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

namespace Sylius\MolliePlugin\Filter;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Webmozart\Assert\Assert;

final class MollieMethodFilter implements MollieMethodFilterInterface
{
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
