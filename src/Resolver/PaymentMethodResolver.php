<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\MolliePlugin\Calculator\PaymentFee\ChargedSurchargeMatcherInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Exceptions\UnknownPaymentSurchargeType;
use Sylius\MolliePlugin\Filter\MollieMethodFilterInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Repository\Query\MollieBasedPaymentMethodQueryInterface;
use Webmozart\Assert\Assert;

final class PaymentMethodResolver implements PaymentMethodsResolverInterface
{
    public function __construct(
        private readonly PaymentMethodsResolverInterface $decoratedResolver,
        private readonly MollieBasedPaymentMethodQueryInterface $mollieBasedPaymentMethodQuery,
        private readonly MollieFactoryNameResolverInterface $factoryNameResolver,
        private readonly MollieMethodFilterInterface $mollieMethodFilter,
        private readonly EntityManagerInterface $entityManager,
        private readonly ?ChargedSurchargeMatcherInterface $chargedSurchargeMatcher = null,
        private readonly ?MollieGatewayFactoryCheckerInterface $gatewayFactoryChecker = null,
        private readonly ?MollieLoggerActionInterface $loggerAction = null,
    ) {
        if (null === $this->chargedSurchargeMatcher || null === $this->gatewayFactoryChecker) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.4',
                'Not passing ChargedSurchargeMatcherInterface and MollieGatewayFactoryCheckerInterface to %s is deprecated and will be required in 4.0. ' .
                'Without them a placed order is offered only the payment method it already carries, since no other can be shown to keep its total.',
                self::class,
            );
        }

        if (null === $this->loggerAction) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.4',
                'Not passing MollieLoggerActionInterface to %s is deprecated and will be required in 4.0.',
                self::class,
            );
        }
    }

    public function getSupportedMethods(PaymentInterface $subject): array
    {
        /** @var ?OrderInterface $order
         * @phpstan-ignore-next-line Ecs yield about missing variable after doc, when subject is set to core
         */
        $order = $subject->getOrder();

        Assert::notNull($order);
        $channel = $order->getChannel();
        $factoryName = $this->factoryNameResolver->resolve($order);

        Assert::notNull($channel);
        $method = $this->mollieBasedPaymentMethodQuery->getOneByChannelAndFactoryName(
            $channel,
            $factoryName,
        );

        if (null !== $method && MollieSubscriptionGatewayFactory::FACTORY_NAME === $factoryName) {
            return [$method];
        }

        $parentMethods = $this->decoratedResolver->getSupportedMethods($subject);
        $parentMethods = $this->filterMethodsByChannel($parentMethods, $channel->getId());

        if (false === $order->hasRecurringContents()) {
            $parentMethods = $this->mollieMethodFilter->nonRecurringFilter($parentMethods);
        } else {
            $parentMethods = $this->mollieMethodFilter->recurringFilter($parentMethods);
        }

        $parentMethods = $this->filterMethodsKeepingTheTotal($order, $parentMethods, $subject->getMethod());

        return $this->sortMethodsByPosition($parentMethods);
    }

    public function supports(PaymentInterface $subject): bool
    {
        if (false === $subject instanceof CorePaymentInterface) {
            return false;
        }
        $order = $subject->getOrder();
        if (false === $order instanceof OrderInterface) {
            return false;
        }

        Assert::notNull($subject->getOrder());

        return $order->hasRecurringContents() || $order->hasNonRecurringContents() &&
            null !== $subject->getOrder()->getChannel();
    }

    /**
     * Order processors stop running once an order leaves `cart` (`Order::canBeProcessed()`), so the
     * surcharge charged on a placed order is frozen and can no longer follow the method the customer
     * picks. Offering a method that would have produced a different surcharge means collecting a fee
     * it never earned - or none of the fee it did.
     *
     * The method the order already carries produced the surcharge it is charged, so it is the one
     * method known to keep the total as it stands and is offered when nothing else does.
     *
     * @param PaymentMethodInterface[] $methods
     *
     * @return PaymentMethodInterface[]
     */
    private function filterMethodsKeepingTheTotal(
        OrderInterface $order,
        array $methods,
        ?PaymentMethodInterface $currentMethod,
    ): array {
        if (null === $order->getCheckoutCompletedAt()) {
            return $methods;
        }

        /**
         * Without the matcher or the gateway checker no surcharge can be compared, so the only method
         * known to keep the total is the one that produced the surcharge the order is charged.
         */
        if (null === $this->chargedSurchargeMatcher || null === $this->gatewayFactoryChecker) {
            return $this->onlyTheCarriedMethod($methods, $currentMethod) ?? $methods;
        }

        $chargedSurcharge = $this->chargedSurchargeMatcher->chargedSurcharge($order);

        $keptMethods = array_values(array_filter(
            $methods,
            fn (PaymentMethodInterface $method): bool => $this->keepsTheTotal($order, $method, $chargedSurcharge),
        ));

        if ([] === $keptMethods) {
            $keptMethods = $this->onlyTheCarriedMethod($methods, $currentMethod) ?? [];

            $this->loggerAction?->addNegativeLog(sprintf(
                'No payment method reproduces the %d surcharge charged on order %s, so %s was offered.',
                $chargedSurcharge,
                (string) $order->getNumber(),
                [] === $keptMethods ? 'none' : 'only the method it already carries',
            ));
        }

        return $keptMethods;
    }

    /**
     * @param PaymentMethodInterface[] $methods
     *
     * @return PaymentMethodInterface[]|null null when the order carries no method that is still offered
     */
    private function onlyTheCarriedMethod(array $methods, ?PaymentMethodInterface $currentMethod): ?array
    {
        if (null === $currentMethod || !in_array($currentMethod, $methods, true)) {
            return null;
        }

        return [$currentMethod];
    }

    /**
     * A Mollie gateway keeps the total when any of its methods reproduces the charged surcharge.
     * A method with no gateway at all, and one on a gateway other than Mollie, add no surcharge of
     * their own, so they only fit an order that carries none - neither is ever taken for Mollie.
     */
    private function keepsTheTotal(OrderInterface $order, PaymentMethodInterface $method, int $chargedSurcharge): bool
    {
        $gatewayConfig = $method->getGatewayConfig();

        if (null === $gatewayConfig) {
            return 0 === $chargedSurcharge;
        }

        /** Every gateway config of an installed shop implements the plugin's interface, Mollie or not. */
        if (false === $gatewayConfig instanceof GatewayConfigInterface) {
            return 0 === $chargedSurcharge;
        }

        if (true !== $this->gatewayFactoryChecker?->isMollieGateway($gatewayConfig)) {
            return 0 === $chargedSurcharge;
        }

        try {
            return $this->chargedSurchargeMatcher?->gatewayKeepsTheTotal($order, $gatewayConfig) ?? true;
        } catch (\InvalidArgumentException|UnknownPaymentSurchargeType $e) {
            $this->loggerAction?->addNegativeLog(sprintf(
                'Cannot compare the payment surcharges of gateway %s, so it was not offered: %s',
                (string) $gatewayConfig->getGatewayName(),
                $e->getMessage(),
            ));

            return false;
        }
    }

    /**
     * @param PaymentMethodInterface[] $methods
     *
     * @return PaymentMethodInterface[]
     */
    private function filterMethodsByChannel(array $methods, int $channelId): array
    {
        $filteredMethods = [];

        foreach ($methods as $method) {
            $methodId = $method->getId();

            $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder()
                ->select('1')
                ->from('sylius_payment_method_channels')
                ->where('payment_method_id = :methodId')
                ->andWhere('channel_id = :channelId')
                ->setParameter('methodId', $methodId)
                ->setParameter('channelId', $channelId);

            if (method_exists($queryBuilder, 'executeQuery')) {
                $isAssociated = $queryBuilder->executeQuery()->fetchOne();
            } else {
                $isAssociated = $queryBuilder->execute()->fetchOne();
            }

            if ($isAssociated !== false && $isAssociated !== null) {
                $filteredMethods[] = $method;
            }
        }

        return $filteredMethods;
    }

    /**
     * @param PaymentMethodInterface[] $methods
     *
     * @return PaymentMethodInterface[]
     */
    private function sortMethodsByPosition(array $methods): array
    {
        $paymentMethods = [];

        foreach ($methods as $method) {
            $paymentMethods[$method->getPosition()] = $method;
        }
        ksort($paymentMethods);

        return $paymentMethods;
    }
}
