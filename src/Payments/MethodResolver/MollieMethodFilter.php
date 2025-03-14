<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\MethodResolver;

use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Webmozart\Assert\Assert;

final class MollieMethodFilter implements MollieMethodFilterInterface
{
    /** @return PaymentMethodInterface[] */
    public function nonRecurringFilter(array $paymentMethods): array
    {
        $filteredMethods = [];
        /** @var PaymentMethodInterface $method */
        foreach ($paymentMethods as $method) {
            Assert::notNull($method->getGatewayConfig());
            if (MollieSubscriptionGatewayFactory::FACTORY_NAME !== $method->getGatewayConfig()->getFactoryName()) {
                $filteredMethods[] = $method;
            }
        }

        return $filteredMethods;
    }

    /** @return PaymentMethodInterface[] */
    public function recurringFilter(array $paymentMethods): array
    {
        $filteredMethods = [];

        /** @var PaymentMethodInterface $method */
        foreach ($paymentMethods as $method) {
            Assert::notNull($method->getGatewayConfig());
            if (MollieGatewayFactory::FACTORY_NAME !== $method->getGatewayConfig()->getFactoryName()) {
                $filteredMethods[] = $method;
            }
        }

        return $filteredMethods;
    }
}
