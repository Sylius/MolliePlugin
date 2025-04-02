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

namespace Sylius\MolliePlugin\Refund\Checker;

use Sylius\RefundPlugin\Command\RefundUnits;

interface DuplicateRefundTheSameAmountCheckerInterface
{
    /** @var string */
    public const ONE_HOUR_INTERVAL = 'PT1H';

    public function check(RefundUnits $command): bool;
}
