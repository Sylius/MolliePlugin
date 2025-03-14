<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Mollie\Api\Resources\MethodCollection;

interface MollieMethodsCreatorInterface
{
    public function createMethods(MethodCollection $allMollieMethods, GatewayConfigInterface $gateway): void;
}
