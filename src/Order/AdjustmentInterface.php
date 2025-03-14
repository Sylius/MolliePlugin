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

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;

interface AdjustmentInterface extends BaseAdjustmentInterface
{
    public const PERCENTAGE_ADJUSTMENT = Options::PERCENTAGE;

    public const FIXED_AMOUNT_ADJUSTMENT = Options::FIXED_FEE;

    public const PERCENTAGE_AND_AMOUNT_ADJUSTMENT = Options::FIXED_FEE_AND_PERCENTAGE;

    public const VOUCHER_ADJUSTMENT = 'voucher';
}
