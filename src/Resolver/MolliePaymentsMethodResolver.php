<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Checker\Voucher\ProductVoucherTypeCheckerInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface as MollieOrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Repository\MollieGatewayConfigRepository;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Sylius\MolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\MolliePlugin\Resolver\Order\PaymentCheckoutOrderResolverInterface;
use Mollie\Api\Exceptions\ApiException;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class MolliePaymentsMethodResolver implements MolliePaymentsMethodResolverInterface
{
    private const MINIMUM_FIELD = 'minimumAmount';
    private const MAXIMUM_FIELD = 'maximumAmount';
    private const FIELD_VALUE = 'value';

    public function __construct(
        private MollieGatewayConfigRepository $mollieGatewayRepository,
        private MollieCountriesRestrictionResolverInterface $countriesRestrictionResolver,
        private ProductVoucherTypeCheckerInterface $productVoucherTypeChecker,
        private PaymentCheckoutOrderResolverInterface $paymentCheckoutOrderResolver,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private MollieAllowedMethodsResolverInterface $allowedMethodsResolver,
        private MollieLoggerActionInterface $loggerAction,
        private MollieFactoryNameResolverInterface $mollieFactoryNameResolver,
        private DivisorProviderInterface $divisorProvider
    ) {
    }

    public function resolve(): array
    {
        /** @var OrderInterface $order */
        $order = $this->paymentCheckoutOrderResolver->resolve();

        $address = $order->getBillingAddress();

        if (null === $address) {
            $address = $order->getShippingAddress();
        }

        if (null === $address) {
            return $this->getDefaultOptions();
        }

        if (false === $order instanceof MollieOrderInterface) {
            return $this->getDefaultOptions();
        }
        Assert::notNull($address->getCountryCode());

        return $this->getMolliePaymentOptions($order, $address->getCountryCode());
    }

    private function getMolliePaymentOptions(MollieOrderInterface $order, string $countryCode): array
    {
        $methods = $this->getDefaultOptions();
        $factoryName = $this->mollieFactoryNameResolver->resolve($order);

        Assert::notNull($order->getChannel());
        $paymentMethod = $this->paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $order->getChannel(),
            $factoryName
        );

        if (null === $paymentMethod) {
            return $this->getDefaultOptions();
        }

        /** @var ?GatewayConfigInterface $gateway */
        $gateway = $paymentMethod->getGatewayConfig();

        if (null === $gateway) {
            return $this->getDefaultOptions();
        }

        $paymentConfigs = $this->mollieGatewayRepository->findAllEnabledByGateway($gateway);

        if (0 === count($paymentConfigs)) {
            return $this->getDefaultOptions();
        }

        try {
            $allowedMethodsIds = $this->allowedMethodsResolver->resolve($order);
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());

            return $this->getDefaultOptions();
        }

        $allowedMethods = $this->filterPaymentMethods($paymentConfigs, $allowedMethodsIds, (float)$order->getTotal()/$this->divisorProvider->getDivisor());

        if (0 === count($allowedMethods)) {
            return $this->getDefaultOptions();
        }

        /** @var MollieGatewayConfigInterface $allowedMethod */
        foreach ($allowedMethods as $allowedMethod) {
            Assert::notNull($methods);
            $methods = $this->countriesRestrictionResolver->resolve($allowedMethod, $methods, $countryCode);
        }
        if (null === $methods) {
            return $this->getDefaultOptions();
        }

        return $this->productVoucherTypeChecker->checkTheProductTypeOnCart($order, $methods);
    }

    private function filterPaymentMethods(array $paymentConfigs, array $allowedMethodsIds, float $orderTotal) : array
    {
        $allowedMethods = [];

        /** @var MollieGatewayConfig $allowedMethod */
        foreach ($paymentConfigs as $allowedMethod) {
            if (isset($allowedMethod[0]) && !empty($allowedMethod[0]) && in_array($allowedMethod[0]->getMethodId(), $allowedMethodsIds, true)) {

                $minAmountLimit = $allowedMethod[self::MINIMUM_FIELD];
                if ($minAmountLimit === null && $allowedMethod[0]->getMinimumAmount()) {
                    $minAmountLimit = $allowedMethod[0]->getMinimumAmount()[self::FIELD_VALUE];
                }

                if ($minAmountLimit !== null && $minAmountLimit > $orderTotal) {
                    continue;
                }

                $maxAmountLimit = $allowedMethod[self::MAXIMUM_FIELD];
                if ($maxAmountLimit === null && $allowedMethod[0]->getMaximumAmount()) {
                    $maxAmountLimit = $allowedMethod[0]->getMaximumAmount()[self::FIELD_VALUE];
                }

                if ($maxAmountLimit !== null && $maxAmountLimit < $orderTotal) {
                    continue;
                }

                $allowedMethods[] = $allowedMethod[0];
            }
        }

        return $allowedMethods;
    }

    private function getDefaultOptions(): array
    {
        return [
            'data' => [],
            'image' => [],
            'issuers' => [],
            'paymentFee' => [],
        ];
    }
}
