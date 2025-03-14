<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Controller\Action\Shop;

use Sylius\MolliePlugin\Order\AdjustmentInterface;

interface PaymentFeeCalculateActionInterface
{
    public const PAYMENTS_FEE_METHOD = [
        AdjustmentInterface::PERCENTAGE_ADJUSTMENT,
        AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT,
        AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT,
    ];
}
