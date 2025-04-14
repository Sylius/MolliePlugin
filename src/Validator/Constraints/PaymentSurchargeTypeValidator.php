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

namespace Sylius\MolliePlugin\Validator\Constraints;

use Sylius\MolliePlugin\Entity\PaymentSurchargeFeeInterface;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class PaymentSurchargeTypeValidator extends ConstraintValidator
{
    public const PERCENTAGE_FIELD = 'percentage';

    public const FIXED_AMOUNT_FIELD = 'fixedAmount';

    public const SURCHARGE_LIMIT_FIELD = 'surchargeLimit';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value instanceof PaymentSurchargeFeeInterface) {
            $this->validatePaymentSurcharge($value, $constraint);
        }

        Assert::isInstanceOf($constraint, PaymentSurchargeType::class);
    }

    private function validatePaymentSurcharge(PaymentSurchargeFeeInterface $paymentSurchargeFee, Constraint $constraint): void
    {
        if (PaymentSurchargeFeeType::PERCENTAGE === $paymentSurchargeFee->getType() && null === $paymentSurchargeFee->getPercentage()) {
            $this->createNegativeResponse($constraint, self::PERCENTAGE_FIELD);
        }
        if (PaymentSurchargeFeeType::PERCENTAGE === $paymentSurchargeFee->getType() && null === $paymentSurchargeFee->getSurchargeLimit()) {
            $this->createNegativeResponse($constraint, self::SURCHARGE_LIMIT_FIELD);
        }
        if (PaymentSurchargeFeeType::FIXED === $paymentSurchargeFee->getType() && null === $paymentSurchargeFee->getFixedAmount()) {
            $this->createNegativeResponse($constraint, self::FIXED_AMOUNT_FIELD);
        }
        if (PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE === $paymentSurchargeFee->getType() && null === $paymentSurchargeFee->getPercentage()) {
            $this->createNegativeResponse($constraint, self::PERCENTAGE_FIELD);
        }
        if (PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE === $paymentSurchargeFee->getType() && null === $paymentSurchargeFee->getFixedAmount()) {
            $this->createNegativeResponse($constraint, self::FIXED_AMOUNT_FIELD);
        }
    }

    private function createNegativeResponse(Constraint $constraint, string $filedName): void
    {
        if (!property_exists($constraint, 'message')) {
            throw new \InvalidArgumentException();
        }
        $this->context->buildViolation($constraint->message)->atPath($filedName)->addViolation();
    }
}
