<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund\Generator;

use Sylius\MolliePlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentNewUnitRefundGeneratorInterface
{
    public function generate(OrderInterface $order, PartialRefundItems $partialRefundItems): PartialRefundItems;
}
