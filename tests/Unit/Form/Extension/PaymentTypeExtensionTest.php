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
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Form\Extension\PaymentTypeExtension;
use Sylius\MolliePlugin\Form\Type\PaymentMollieType;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormInterface;

final class PaymentTypeExtensionTest extends TestCase
{
    /** @var array<string, null> */
    private array $mollieDetails;

    private PaymentTypeExtension $extension;

    protected function setUp(): void
    {
        $this->mollieDetails = array_fill_keys(PaymentMollieType::FIELDS, null);
        $this->extension = new PaymentTypeExtension(new MollieGatewayFactoryChecker());
    }

    public function testItExtendsTheCheckoutPaymentType(): void
    {
        $this->assertInstanceOf(FormExtensionInterface::class, $this->extension);
        $this->assertSame([PaymentType::class], PaymentTypeExtension::getExtendedTypes());
    }

    public function testItStripsMollieDetailsFromPaymentsUsingUnrelatedGateways(): void
    {
        $payment = $this->createPayment(
            factoryName: 'offline',
            details: $this->mollieDetails + ['unrelatedGatewayKey' => 'value'],
        );

        $payment->expects($this->once())
            ->method('setDetails')
            ->with(['unrelatedGatewayKey' => 'value']);

        $this->dispatchPostSubmit($payment);
    }

    public function testItKeepsMollieDetailsForMolliePayments(): void
    {
        $payment = $this->createPayment(
            factoryName: MollieGatewayFactory::FACTORY_NAME,
            details: $this->mollieDetails,
        );

        $payment->expects($this->never())->method('setDetails');

        $this->dispatchPostSubmit($payment);
    }

    public function testItKeepsMollieDetailsForMollieSubscriptionPayments(): void
    {
        $payment = $this->createPayment(
            factoryName: MollieSubscriptionGatewayFactory::FACTORY_NAME,
            details: $this->mollieDetails,
        );

        $payment->expects($this->never())->method('setDetails');

        $this->dispatchPostSubmit($payment);
    }

    public function testItStripsMollieDetailsWhenPaymentHasNoMethod(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn(null);
        $payment->method('getDetails')->willReturn($this->mollieDetails);

        $payment->expects($this->once())->method('setDetails')->with([]);

        $this->dispatchPostSubmit($payment);
    }

    public function testItStripsMollieDetailsWhenPaymentMethodHasNoGatewayConfig(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn(null);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn($this->mollieDetails);

        $payment->expects($this->once())->method('setDetails')->with([]);

        $this->dispatchPostSubmit($payment);
    }

    public function testItDoesNothingWhenTheDataIsNotAPayment(): void
    {
        $this->expectNotToPerformAssertions();

        $this->dispatchPostSubmit(new \stdClass());
    }

    /**
     * @return PaymentInterface&MockObject
     */
    private function createPayment(string $factoryName, array $details): PaymentInterface
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn($factoryName);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn($details);

        return $payment;
    }

    private function dispatchPostSubmit(mixed $data): void
    {
        $listener = $this->capturePostSubmitListener();

        $listener(new FormEvent($this->createMock(FormInterface::class), $data));
    }

    private function capturePostSubmitListener(): callable
    {
        $listener = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('add')->willReturnSelf();
        $builder->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $callback) use (&$listener, $builder): FormBuilderInterface {
                if (FormEvents::POST_SUBMIT === $eventName) {
                    $listener = $callback;
                }

                return $builder;
            });

        $this->extension->buildForm($builder, []);

        $this->assertNotNull($listener, 'Expected a POST_SUBMIT listener to be registered.');

        return $listener;
    }
}
