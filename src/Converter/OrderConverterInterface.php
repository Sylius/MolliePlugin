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

namespace Sylius\MolliePlugin\Converter;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Model\AdjustmentInterface;

interface OrderConverterInterface
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

    /**
     * @param array<string, mixed> $details
     *
     * @return array{
     *     amount: array<array-key, mixed>,
     *     orderNumber: string,
     *     shippingAddress: array<string, string>,
     *     billingAddress: array<string, string>,
     *     lines: array<array-key, array<string, mixed>>,
     *     applePayDirectToken?: string,
     *     backurl?: string
     * }
     */
    public function convert(
        OrderInterface $order,
        array $details,
        int $divisor,
        MollieGatewayConfigInterface $method,
    ): array;
}
