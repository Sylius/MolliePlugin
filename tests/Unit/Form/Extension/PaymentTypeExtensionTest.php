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

namespace Tests\Sylius\MolliePlugin\Unit\Form\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\MolliePlugin\Form\EventSubscriber\ScopeMollieDetailsToMollieGatewaySubscriber;
use Sylius\MolliePlugin\Form\Extension\PaymentTypeExtension;
use Sylius\MolliePlugin\Form\Type\PaymentMollieType;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Sylius\MolliePlugin\Unit\Form\Extension\Fixtures\ForeignGatewayDetailsType;
use Tests\Sylius\MolliePlugin\Unit\Form\Extension\Fixtures\ForeignGatewayPaymentTypeExtension;
use Tests\Sylius\MolliePlugin\Unit\Form\Extension\Fixtures\LateForeignDetailsPaymentTypeExtension;

final class PaymentTypeExtensionTest extends TypeTestCase
{
    private const MOLLIE_DETAILS = [
        'molliePaymentMethods' => 'ideal',
        'cartToken' => 'tkn_123',
        'saveCardInfo' => '1',
        'useSavedCards' => '0',
    ];

    /** @var array<PaymentMethodInterface> */
    private array $paymentMethods = [];

    private MockObject&MolliePaymentsMethodResolverInterface $molliePaymentsMethodResolver;

    protected function setUp(): void
    {
        $this->molliePaymentsMethodResolver = $this->createMock(MolliePaymentsMethodResolverInterface::class);
        $this->molliePaymentsMethodResolver->method('resolve')->willReturn([
            'data' => ['ideal' => 'iDEAL'],
            'image' => ['ideal' => ['name' => 'ideal.png']],
            'issuers' => [],
            'paymentFee' => ['ideal' => 0],
        ]);

        parent::setUp();
    }

    public function testItExtendsTheCheckoutPaymentType(): void
    {
        self::assertSame([PaymentType::class], PaymentTypeExtension::getExtendedTypes());
    }

    public function testItDoesNotAddTheMollieDetailsWhenNoMollieMethodIsAvailable(): void
    {
        $this->paymentMethods = [$this->createPaymentMethod('offline', 'offline')];

        $payment = $this->createPayment(['foreignGatewayKey' => 'value']);
        $form = $this->factory->create(PaymentType::class, $payment);

        self::assertFalse($form->has('details'));

        $form->submit(['method' => 'offline', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame(['foreignGatewayKey' => 'value'], $payment->getDetails());
        self::assertSame([], $form->getExtraData());
        self::assertArrayNotHasKey('details', $form->createView()->children);
    }

    public function testItDoesNotTouchTheDetailsOfAPaymentUsingAnUnrelatedGateway(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $details = ['cartToken' => 'foreign-gateway-token', 'foreignGatewayKey' => 'value'];
        $payment = $this->createPayment($details);
        $form = $this->factory->create(PaymentType::class, $payment);

        self::assertTrue($form->has('details'));

        $form->submit(['method' => 'offline', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame($details, $payment->getDetails());

        // the Mollie hook templates read parent_form.details.*, so the child must still be renderable
        $detailsView = $form->createView()->children['details'];

        self::assertArrayHasKey('molliePaymentMethods', $detailsView->children);
        self::assertArrayHasKey('cartToken', $detailsView->children);
        self::assertArrayHasKey('saveCardInfo', $detailsView->children);
        self::assertArrayHasKey('useSavedCards', $detailsView->children);
        self::assertCount(1, $detailsView->children['molliePaymentMethods']->vars['choices']);
    }

    public function testItMapsTheMollieDetailsWhenAMollieMethodIsSelected(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $payment = $this->createPayment();
        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'mollie', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame(self::MOLLIE_DETAILS, $payment->getDetails());
    }

    public function testItMapsTheMollieDetailsWhenAMollieSubscriptionMethodIsSelected(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie_subscription', MollieSubscriptionGatewayFactory::FACTORY_NAME),
        ];

        $payment = $this->createPayment();
        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'mollie_subscription', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame(self::MOLLIE_DETAILS, $payment->getDetails());
    }

    public function testItDoesNotTouchTheDetailsWhenNoMethodIsSubmitted(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $payment = $this->createPayment(['foreignGatewayKey' => 'value']);
        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['details' => self::MOLLIE_DETAILS]);

        self::assertSame(['foreignGatewayKey' => 'value'], $payment->getDetails());
    }

    public function testItDoesNotTouchTheDetailsWhenTheSelectedMethodHasNoGatewayConfig(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('no_gateway', null),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $payment = $this->createPayment(['foreignGatewayKey' => 'value']);
        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'no_gateway', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame(['foreignGatewayKey' => 'value'], $payment->getDetails());
    }

    public function testItFallsBackToTheDefaultCheckerWhenNoneIsProvided(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $factory = $this->createFormFactory(null);

        $payment = $this->createPayment(['foreignGatewayKey' => 'value']);
        $form = $factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'offline', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame(['foreignGatewayKey' => 'value'], $payment->getDetails());
    }

    public function testItLeavesForeignGatewayDetailsUntouchedEvenWhenTheyUseMollieKeyNames(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $details = [
            'molliePaymentMethods' => 'foreign-method',
            'cartToken' => 'foreign-cart-token',
            'saveCardInfo' => 'foreign-save-card-info',
            'useSavedCards' => 'foreign-use-saved-cards',
            'foreignGatewayKey' => 'value',
        ];

        $payment = $this->createPayment($details);
        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'offline', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame($details, $payment->getDetails());
    }

    public function testItDoesNotHijackADetailsFormOwnedByAnotherPaymentIntegration(): void
    {
        $this->paymentMethods = [
            $this->createPaymentMethod('offline', 'offline'),
            $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME),
        ];

        $factory = $this->createFormFactory(
            new MollieGatewayFactoryChecker(),
            [new ForeignGatewayPaymentTypeExtension()],
        );

        $payment = $this->createPayment();
        $form = $factory->create(PaymentType::class, $payment);

        self::assertInstanceOf(
            ForeignGatewayDetailsType::class,
            $form->get('details')->getConfig()->getType()->getInnerType(),
        );

        $form->submit([
            'method' => 'offline',
            'details' => ['cartToken' => 'foreign-cart-token', 'foreignToken' => 'foreign-token'],
        ]);

        self::assertSame(
            ['cartToken' => 'foreign-cart-token', 'foreignToken' => 'foreign-token'],
            $payment->getDetails(),
        );
    }

    public function testItClearsItsOwnLeftoversWhenSwitchingFromAMollieMethodToAnotherGateway(): void
    {
        $mollie = $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME);
        $this->paymentMethods = [$this->createPaymentMethod('offline', 'offline'), $mollie];

        $payment = $this->createPayment(self::MOLLIE_DETAILS + ['foreignGatewayKey' => 'value']);
        $payment->setMethod($mollie);

        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'offline', 'details' => [
            'molliePaymentMethods' => '',
            'cartToken' => '',
            'saveCardInfo' => '',
            'useSavedCards' => '',
        ]]);

        self::assertSame(['foreignGatewayKey' => 'value'], $payment->getDetails());
    }

    public function testItKeepsTheKeysWhenThePreviouslyStoredMethodWasNotMollie(): void
    {
        $offline = $this->createPaymentMethod('offline', 'offline');
        $this->paymentMethods = [$offline, $this->createPaymentMethod('mollie', MollieGatewayFactory::FACTORY_NAME)];

        $details = ['cartToken' => 'foreign-gateway-token', 'foreignGatewayKey' => 'value'];
        $payment = $this->createPayment($details);
        $payment->setMethod($offline);

        $form = $this->factory->create(PaymentType::class, $payment);

        $form->submit(['method' => 'offline', 'details' => self::MOLLIE_DETAILS]);

        self::assertSame($details, $payment->getDetails());
    }

    public function testItKeepsTheDetailsWhenTheOfferedMethodsCannotBeEnumerated(): void
    {
        // dropping `details` here would break the Mollie hooks, which still dereference it
        $subscriber = new ScopeMollieDetailsToMollieGatewaySubscriber(new MollieGatewayFactoryChecker());

        $resolvedType = $this->createMock(ResolvedFormTypeInterface::class);
        $resolvedType->method('getInnerType')->willReturn(new PaymentMollieType($this->molliePaymentsMethodResolver));

        $detailsConfig = $this->createMock(FormConfigInterface::class);
        $detailsConfig->method('getType')->willReturn($resolvedType);

        $details = $this->createMock(FormInterface::class);
        $details->method('getConfig')->willReturn($detailsConfig);

        $form = $this->createMock(FormInterface::class);
        $form->method('has')->willReturnCallback(static fn (string $name): bool => 'details' === $name);
        $form->method('get')->willReturn($details);
        $form->expects($this->never())->method('remove');

        $subscriber->removeDetailsWhenNoMollieMethodIsAvailable(new FormEvent($form, $this->createPayment()));
    }

    public function testItLeavesADetailsFieldAnotherIntegrationAddsOnSubmitAlone(): void
    {
        $this->paymentMethods = [$this->createPaymentMethod('offline', 'offline')];

        $factory = $this->createFormFactory(
            new MollieGatewayFactoryChecker(),
            [new LateForeignDetailsPaymentTypeExtension()],
        );

        $payment = $this->createPayment();
        $form = $factory->create(PaymentType::class, $payment);

        $form->submit([
            'method' => 'offline',
            'details' => ['cartToken' => 'foreign-cart-token', 'foreignToken' => 'foreign-token'],
        ]);

        self::assertSame(
            ['cartToken' => 'foreign-cart-token', 'foreignToken' => 'foreign-token'],
            $payment->getDetails(),
        );
    }

    protected function getExtensions(): array
    {
        return $this->buildExtensions(new MollieGatewayFactoryChecker());
    }

    /**
     * @param array<FormTypeExtensionInterface> $extraPaymentTypeExtensions
     *
     * @return array<FormExtensionInterface>
     */
    private function buildExtensions(
        ?MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
        array $extraPaymentTypeExtensions = [],
    ): array {
        $paymentMethodsResolver = $this->createMock(PaymentMethodsResolverInterface::class);
        $paymentMethodsResolver
            ->method('getSupportedMethods')
            ->willReturnCallback(fn (): array => $this->paymentMethods)
        ;

        return [
            new ValidatorExtension($this->createValidator()),
            new PreloadedExtension(
                [
                    new PaymentType(Payment::class),
                    new PaymentMethodChoiceType($paymentMethodsResolver, $this->createMock(RepositoryInterface::class)),
                    new PaymentMollieType($this->molliePaymentsMethodResolver),
                    new ForeignGatewayDetailsType(),
                ],
                [PaymentType::class => [
                    new PaymentTypeExtension($mollieGatewayFactoryChecker),
                    ...$extraPaymentTypeExtensions,
                ]],
            ),
        ];
    }

    /** @param array<FormTypeExtensionInterface> $extraPaymentTypeExtensions */
    private function createFormFactory(
        ?MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker,
        array $extraPaymentTypeExtensions = [],
    ): FormFactoryInterface {
        $builder = Forms::createFormFactoryBuilder();

        foreach ($this->buildExtensions($mollieGatewayFactoryChecker, $extraPaymentTypeExtensions) as $extension) {
            $builder->addExtension($extension);
        }

        return $builder->getFormFactory();
    }

    private function createValidator(): ValidatorInterface
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('getMetadataFor')->willReturn(new ClassMetadata(Form::class));
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        return $validator;
    }

    /** @param array<string, mixed> $details */
    private function createPayment(array $details = []): Payment
    {
        $payment = new Payment();
        $payment->setDetails($details);

        return $payment;
    }

    private function createPaymentMethod(string $code, ?string $factoryName): PaymentMethodInterface
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setCurrentLocale('en_US');
        $paymentMethod->setFallbackLocale('en_US');
        $paymentMethod->setCode($code);
        $paymentMethod->setName(ucfirst($code));

        if (null !== $factoryName) {
            $gatewayConfig = new GatewayConfig();
            $gatewayConfig->setGatewayName($code);
            $gatewayConfig->setFactoryName($factoryName);

            $paymentMethod->setGatewayConfig($gatewayConfig);
        }

        return $paymentMethod;
    }
}
