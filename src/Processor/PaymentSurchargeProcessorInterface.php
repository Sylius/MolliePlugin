<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Processor;

use Sylius\MolliePlugin\Entity\OrderInterface;

interface PaymentSurchargeProcessorInterface
{
    public function process(OrderInterface $order): void;
}
