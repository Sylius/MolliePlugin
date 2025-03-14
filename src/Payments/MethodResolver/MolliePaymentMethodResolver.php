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

namespace SyliusMolliePlugin\Payments\MethodResolver;

use Doctrine\ORM\EntityManagerInterface;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Repository\PaymentMethodRepositoryInterface;
use SyliusMolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Webmozart\Assert\Assert;

final class MolliePaymentMethodResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedService;

    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    private MollieFactoryNameResolverInterface $factoryNameResolver;

    private MollieMethodFilterInterface $mollieMethodFilter;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PaymentMethodsResolverInterface $decoratedService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        MollieFactoryNameResolverInterface $factoryNameResolver,
        MollieMethodFilterInterface $mollieMethodFilter,
        EntityManagerInterface $entityManager
    ) {
        $this->decoratedService = $decoratedService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->factoryNameResolver = $factoryNameResolver;
        $this->mollieMethodFilter = $mollieMethodFilter;
        $this->entityManager = $entityManager;
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
        $method = $this->paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            $factoryName
        );

        if (null !== $method && MollieSubscriptionGatewayFactory::FACTORY_NAME === $factoryName) {
            return [$method];
        }

        $parentMethods = $this->decoratedService->getSupportedMethods($subject);
        $parentMethods = $this->filterMethodsByChannel($parentMethods, $channel->getId());

        if (false === $order->hasRecurringContents()) {
            $parentMethods = $this->mollieMethodFilter->nonRecurringFilter($parentMethods);
        } else {
            $parentMethods = $this->mollieMethodFilter->recurringFilter($parentMethods);
        }

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

        return $order->hasRecurringContents() || $order->hasNonRecurringContents()
            && null !== $subject->getOrder()->getChannel();
    }

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
     * Sorts payment methods by their position before returning the result
     * @param array $methods
     *
     * @return array
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