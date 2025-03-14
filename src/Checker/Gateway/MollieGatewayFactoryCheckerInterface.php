<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\Gateway;

use Payum\Core\Model\GatewayConfigInterface;

interface MollieGatewayFactoryCheckerInterface
{
    public function isMollieGateway(GatewayConfigInterface $gateway): bool;
}
