<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Validator\Refund;

use SyliusMolliePlugin\Checker\Refund\DuplicateRefundTheSameAmountCheckerInterface;
use Sylius\RefundPlugin\Checker\OrderRefundingAvailabilityCheckerInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Exception\InvalidRefundAmount;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use Sylius\RefundPlugin\Model\RefundType;
use Sylius\RefundPlugin\Validator\RefundAmountValidatorInterface;
use Sylius\RefundPlugin\Validator\RefundUnitsCommandValidatorInterface;

final class RefundUnitsCommandValidator implements RefundUnitsCommandValidatorInterface
{
    /** @var OrderRefundingAvailabilityCheckerInterface */
    private $orderRefundingAvailabilityChecker;

    /** @var RefundAmountValidatorInterface */
    private $refundAmountValidator;

    /** @var DuplicateRefundTheSameAmountCheckerInterface */
    private $duplicateRefundTheSameAmountChecker;

    public function __construct(
        OrderRefundingAvailabilityCheckerInterface $orderRefundingAvailabilityChecker,
        RefundAmountValidatorInterface $refundAmountValidator,
        DuplicateRefundTheSameAmountCheckerInterface $duplicateRefundTheSameAmountChecker
    ) {
        $this->orderRefundingAvailabilityChecker = $orderRefundingAvailabilityChecker;
        $this->refundAmountValidator = $refundAmountValidator;
        $this->duplicateRefundTheSameAmountChecker = $duplicateRefundTheSameAmountChecker;
    }

    public function validate(RefundUnits $command): void
    {
        if (!$this->orderRefundingAvailabilityChecker->__invoke($command->orderNumber())) {
            throw OrderNotAvailableForRefunding::withOrderNumber($command->orderNumber());
        }

        if (0 === count($command->units()) && 0 === count($command->shipments())) {
            throw new OrderNotAvailableForRefunding(sprintf('There are no units to refund in order %s', $command->orderNumber()));
        }

        $reflection = new \ReflectionMethod($this->refundAmountValidator, 'validateUnits');
        $paramCount = $reflection->getNumberOfParameters();

        if ($paramCount === 2) {
            $this->refundAmountValidator->validateUnits($command->units(), RefundType::orderItemUnit());
            $this->refundAmountValidator->validateUnits($command->shipments(), RefundType::shipment());
        } else {
            $this->refundAmountValidator->validateUnits($command->units());
            $this->refundAmountValidator->validateUnits($command->shipments());
        }
        
        if (true === $this->duplicateRefundTheSameAmountChecker->check($command)) {
            throw new InvalidRefundAmount('A duplicate refund has been detected');
        }
    }
}
