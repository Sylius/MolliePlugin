<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\Component\Core\Model\AdjustmentInterface as BaseAdjustmentInterface;

interface AdjustmentInterface extends BaseAdjustmentInterface
{
    public const PERCENTAGE_ADJUSTMENT = Options::PERCENTAGE;

    public const FIXED_AMOUNT_ADJUSTMENT = Options::FIXED_FEE;

    public const PERCENTAGE_AND_AMOUNT_ADJUSTMENT = Options::FIXED_FEE_AND_PERCENTAGE;

    public const VOUCHER_ADJUSTMENT = 'voucher';
}
