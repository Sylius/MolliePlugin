<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver\Address;

use Sylius\Component\Core\Model\OrderInterface;

interface ApplePayAddressResolverInterface
{
    public function resolve(OrderInterface $order, array $applePayData): void;
}
