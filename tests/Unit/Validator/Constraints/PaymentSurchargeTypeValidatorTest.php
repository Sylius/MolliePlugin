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

namespace Tests\Sylius\MolliePlugin\Unit\Validator\Constraints;

use Sylius\MolliePlugin\Entity\PaymentSurchargeFee;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Validator\Constraints\PaymentSurchargeType;
use Sylius\MolliePlugin\Validator\Constraints\PaymentSurchargeTypeValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class PaymentSurchargeTypeValidatorTest extends ConstraintValidatorTestCase
{
    public function testItAcceptsACompleteFixedAmountAndPercentageFee(): void
    {
        $this->validator->validate($this->fee(PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE, 1.0, 1.0, 5.0), new PaymentSurchargeType());

        $this->assertNoViolation();
    }

    public function testItRejectsAFixedAmountAndPercentageFeeWithoutASurchargeLimit(): void
    {
        $this->validator->validate($this->fee(PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE, 1.0, 1.0, null), new PaymentSurchargeType());

        $this->buildViolation('sylius_mollie.form.error.payment_surcharge_not_empty')
            ->atPath('property.path.surchargeLimit')
            ->assertRaised()
        ;
    }

    public function testItAcceptsAFixedFeeWithoutASurchargeLimit(): void
    {
        $this->validator->validate($this->fee(PaymentSurchargeFeeType::FIXED, 1.0, null, null), new PaymentSurchargeType());

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PaymentSurchargeTypeValidator();
    }

    private function fee(string $type, ?float $fixedAmount, ?float $percentage, ?float $surchargeLimit): PaymentSurchargeFee
    {
        $fee = new PaymentSurchargeFee();
        $fee->setType($type);
        $fee->setFixedAmount($fixedAmount);
        $fee->setPercentage($percentage);
        $fee->setSurchargeLimit($surchargeLimit);

        return $fee;
    }
}
