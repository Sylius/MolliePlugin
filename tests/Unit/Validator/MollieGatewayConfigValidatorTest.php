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

namespace Tests\Sylius\MolliePlugin\Unit\Validator;

use Doctrine\ORM\PersistentCollection;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieMinMaxInterface;
use Sylius\MolliePlugin\Validator\Constraints\MollieGatewayConfigValidator;
use Sylius\MolliePlugin\Validator\Constraints\MollieGatewayConfigValidatorType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class MollieGatewayConfigValidatorTest extends ConstraintValidatorTestCase
{
    /** @var MollieGatewayConfigValidatorType */
    protected Constraint $constraint;

    private MollieGatewayConfigInterface $mollieGatewayConfig;

    private MollieMinMaxInterface $configAmountLimits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new MollieGatewayConfigValidatorType();
        $this->context->setConstraint($this->constraint);
        $this->validator->initialize($this->context);

        $this->mollieGatewayConfig = $this->createMock(MollieGatewayConfigInterface::class);
        $this->configAmountLimits = $this->createMock(MollieMinMaxInterface::class);
    }

    public function testItIsAConstraintValidator(): void
    {
        $this->assertInstanceOf(
            ConstraintValidatorInterface::class,
            $this->validator,
        );
    }

    public function testIgnoresNonPersistentCollection(): void
    {
        $this->validator->validate('foo', $this->constraint);
        $this->assertNoViolation();
    }

    public function testAddsNoViolationIfPersistentCollectionIsEmpty(): void
    {
        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    public function testAddsNoViolationIfGatewayConfigHasNoAmountLimitsSet(): void
    {
        $this->mollieGatewayConfig
            ->method('getAmountLimits')
            ->willReturn(null)
        ;

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    public function testConfigMinimumLessThanConfigMaximum(): void
    {
        $this->setupPaymentMethod('someMethod');
        $this->setupApiAmountLimits();
        $this->setupConfigAmountLimits(minimum: 10.01, maximum: 10.00);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->buildViolation($this->constraint->minGreaterThanMaxMessage)
            ->atPath('property.path[0].amountLimits.maximumAmount')
            ->assertRaised()
        ;
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMinimumLessThanApiMinimum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        if ($apiMinimumAmount === null) {
            $this->assertNoViolation();

            return;
        }

        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(minimum: $apiMinimumAmount - 0.01);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->buildViolation($this->constraint->minLessThanMollieMinMessage)
            ->setParameter('%amount%', (string) $apiMinimumAmount)
            ->atPath('property.path[0].amountLimits.minimumAmount')
            ->assertRaised()
        ;
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMinimumEqualApiMinimum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(minimum: $apiMinimumAmount);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMinimumGreaterThanApiMinimum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(minimum: $apiMinimumAmount + 0.01);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMaximumLessThanApiMaximum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(maximum: $apiMaximumAmount - 0.01);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMaximumEqualApiMaximum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(maximum: $apiMaximumAmount);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);
        $this->assertNoViolation();
    }

    /** @dataProvider providePaymentMethodConfigurations */
    public function testConfigMaximumGreaterThanApiMaximum(
        string $paymentMethod,
        ?float $apiMinimumAmount = null,
        ?float $apiMaximumAmount = null,
    ): void {
        if ($apiMaximumAmount === null) {
            $this->assertNoViolation();

            return;
        }

        $this->setupPaymentMethod($paymentMethod);
        $this->setupApiAmountLimits($apiMinimumAmount, $apiMaximumAmount);
        $this->setupConfigAmountLimits(maximum: $apiMaximumAmount + 0.01);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);

        if ($paymentMethod === 'creditcard') {
            $this->assertNoViolation();

            return;
        }

        $this->buildViolation($this->constraint->maxGreaterThanMollieMaxMessage)
            ->setParameter('%amount%', (string) $apiMaximumAmount)
            ->atPath('property.path[0].amountLimits.maximumAmount')
            ->assertRaised()
        ;
    }

    public function testConfigMaximumGreaterThanApiMaximumWhenConfigMinimumIsProvidedButItsNotSetInApi(): void
    {
        $this->setupPaymentMethod('someMethod');
        $this->setupApiAmountLimits(maximum: 10.00);
        $this->setupConfigAmountLimits(0.01, 10.01);

        $this->validator->validate($this->createPersistentCollection(), $this->constraint);

        $this->buildViolation($this->constraint->maxGreaterThanMollieMaxMessage)
            ->setParameter('%amount%', '10')
            ->atPath('property.path[0].amountLimits.maximumAmount')
            ->assertRaised()
        ;
    }

    protected function createContext(): ExecutionContextInterface
    {
        $context = parent::createContext();
        $context->setConstraint($this->constraint);

        return $context;
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new MollieGatewayConfigValidator();
    }

    private function createPersistentCollection(): PersistentCollection
    {
        $ref = new \ReflectionClass(PersistentCollection::class);
        $collection = $ref->newInstanceWithoutConstructor();

        $ref
            ->getProperty('snapshot')
            ->setValue($collection, [$this->mollieGatewayConfig])
        ;

        return $collection;
    }

    /**
     * Each entry is a 3-element tuple:
     *   [0] string $paymentMethod Mollie payment method identifier
     *   [1] float $apiMinimumAmount Minimum amount defined by Mollie API
     *   [2] float|null $apiMaximumAmount Maximum amount defined by Mollie API (null = no upper bound)
     *
     * @return array<int, array{string, float, float|null}>
     */
    private static function providePaymentMethodConfigurations(): array
    {
        return [
            ['applepay', 0.02, 10000.00],
            ['ideal', 0.02, 50000.00],
            ['creditcard', 0.02, 10000.00],
            ['klarna', 0.10, 14000.00],
            ['klarnapaylater', 0.02, 2500.00],
            ['klarnapaynow', 0.10, 10000.00],
            ['klarnasliceit', 35.00, 4000.00],
            ['riverty', 5.00, 2500.00],
            ['banktransfer', 0.02, 1000000.00],
            ['paypal', 0.02, null],
            ['trustly', 0.02, 50000.00],
            ['bancontact', 0.03, 50000.00],
            ['przelewy24', 0.02, 12815.00],
            ['kbc', 0.02, 50000.00],
            ['billie', 0.02, 25000.00],
            ['twint', 0.02, 5000.00],
            ['blik', 0.02, 50000.00],
            ['bancomatpay', 0.02, 1000000.00],
            ['alma', 50.00, 2000.00],
            ['payconiq', 0.02, 2500.00],
            ['satispay', 0.02, 99999.99],
        ];
    }

    private function setupPaymentMethod(string $paymentMethod): void
    {
        $this->mollieGatewayConfig
            ->method('getMethodId')
            ->willReturn($paymentMethod)
        ;
    }

    private function setupApiAmountLimits(?float $minimum = null, ?float $maximum = null): void
    {
        $this->mollieGatewayConfig
            ->method('getAmountLimits')
            ->willReturn($this->configAmountLimits)
        ;

        if ($minimum !== null) {
            $this->mollieGatewayConfig
                ->method('getMinimumAmount')
                ->willReturn(['value' => (string) $minimum, 'currency' => 'EUR'])
            ;
        }

        if ($maximum !== null) {
            $this->mollieGatewayConfig
                ->method('getMaximumAmount')
                ->willReturn(['value' => (string) $maximum, 'currency' => 'EUR'])
            ;
        }
    }

    private function setupConfigAmountLimits(?float $minimum = null, ?float $maximum = null): void
    {
        if ($minimum !== null) {
            $this->configAmountLimits
                ->method('getMinimumAmount')
                ->willReturn($minimum)
            ;
        }

        if ($maximum !== null) {
            $this->configAmountLimits
                ->method('getMaximumAmount')
                ->willReturn($maximum)
            ;
        }
    }
}
