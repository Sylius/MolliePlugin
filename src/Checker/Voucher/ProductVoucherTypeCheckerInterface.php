<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\Voucher;

use Sylius\Component\Core\Model\OrderInterface;

interface ProductVoucherTypeCheckerInterface
{
    public function checkTheProductTypeOnCart(OrderInterface $order, array $methods): array;
}
