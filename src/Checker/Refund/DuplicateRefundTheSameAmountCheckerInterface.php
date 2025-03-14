<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\Refund;

use Sylius\RefundPlugin\Command\RefundUnits;

interface DuplicateRefundTheSameAmountCheckerInterface
{
    /** @var string */
    public const ONE_HOUR_INTERVAL = 'PT1H';

    public function check(RefundUnits $command): bool;
}
