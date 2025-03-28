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

namespace Sylius\MolliePlugin\Refund\Validator;

use Sylius\MolliePlugin\Refund\Checker\DuplicateRefundTheSameAmountCheckerInterface;
use Sylius\RefundPlugin\Checker\OrderRefundingAvailabilityCheckerInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Exception\InvalidRefundAmount;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use Sylius\RefundPlugin\Validator\RefundAmountValidatorInterface;
use Sylius\RefundPlugin\Validator\RefundUnitsCommandValidatorInterface;

final class RefundUnitsCommandValidator implements RefundUnitsCommandValidatorInterface
{
    public function __construct(
        private readonly OrderRefundingAvailabilityCheckerInterface $orderRefundingAvailabilityChecker,
        private readonly RefundAmountValidatorInterface $refundAmountValidator,
        private readonly DuplicateRefundTheSameAmountCheckerInterface $duplicateRefundTheSameAmountChecker,
    ) {
    }

    public function validate(RefundUnits $command): void
    {
        if (!$this->orderRefundingAvailabilityChecker->__invoke($command->orderNumber())) {
            throw OrderNotAvailableForRefunding::withOrderNumber($command->orderNumber());
        }

        if (0 === count($command->units())) {
            throw new OrderNotAvailableForRefunding(sprintf('There are no units to refund in order %s', $command->orderNumber()));
        }

        $this->refundAmountValidator->validateUnits($command->units());

        if (true === $this->duplicateRefundTheSameAmountChecker->check($command)) {
            throw new InvalidRefundAmount('A duplicate refund has been detected');
        }
    }
}
