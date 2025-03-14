<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Apple;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\Request;

interface ApplePayDirectProviderInterface
{
    public function provideOrder(OrderInterface $order, Request $request): void;
}
