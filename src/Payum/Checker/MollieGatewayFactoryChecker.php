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

namespace Sylius\MolliePlugin\Payum\Checker;

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class MollieGatewayFactoryChecker implements MollieGatewayFactoryCheckerInterface
{
    private const MOLLIE_GATEWAYS = [
      MollieGatewayFactory::FACTORY_NAME,
      MollieSubscriptionGatewayFactory::FACTORY_NAME,
    ];

    public function isMollieGateway(GatewayConfigInterface $gateway): bool
    {
        if (in_array($gateway->getFactoryName(), self::MOLLIE_GATEWAYS, true)) {
            return true;
        }

        return false;
    }
}
