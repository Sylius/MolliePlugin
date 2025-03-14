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
