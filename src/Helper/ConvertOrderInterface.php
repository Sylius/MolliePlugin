<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Helper;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Order\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface ConvertOrderInterface
{
    public const PAYMENT_FEE = 'PAYMENT_FEE';

    public const PAYMENT_FEE_TYPE = 'surcharge';

    public const SHIPPING_TYPE = 'shipping_fee';

    public const SHIPPING_FEE = 'SHIPPING_FEE';

    public const PHYSICAL_TYPE = 'physical';

    public const ITEM_DISCOUNT_ADJUSTMENTS_TYPES = [
        AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT,
        AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT,
        AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
    ];

    public const TAX_RATE_CRITERIA_ZONE = 'zone';

    public function convert(
        OrderInterface $order,
        array $details,
        int $divisor,
        MollieGatewayConfigInterface $method
    ): array;
}
