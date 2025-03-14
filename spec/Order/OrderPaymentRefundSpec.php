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

namespace spec\SyliusMolliePlugin\Order;

use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Order\OrderPaymentRefund;
use SyliusMolliePlugin\Order\OrderPaymentRefundInterface;
use SyliusMolliePlugin\Request\Api\RefundOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Refund as RefundAction;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderPaymentRefundSpec extends ObjectBehavior
{
    public function let(
        RepositoryInterface $orderRepository,
        MollieLoggerActionInterface $loggerAction,
        Payum $payum,
        UnitsRefunded $units,
        OrderInterface $order,
        PaymentInterface $payment,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $config
    ): void {
        $this->beConstructedWith(
            $orderRepository,
            $loggerAction,
            $payum
        );
        $units->orderNumber()->willReturn('#0000001');
        $units->units()->willReturn([
            'order_item_1',
            'order_item_2',
        ]);
        $units->shipments()->willReturn([
            'dhl',
            'fedex',
        ]);
        $orderRepository->findOneBy(['number' => '#0000001'])->willReturn($order);

        $order->getPayments()->willReturn(new ArrayCollection([
            $payment->getWrappedObject(),
        ]));
        $payment->getMethod()->willReturn($paymentMethod);

        $paymentMethod->getGatewayConfig()->willReturn($config);
        $config->getFactoryName()->willReturn('mollie_subscription_test');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(OrderPaymentRefund::class);
    }

    public function it_should_implement_order_payment_refund_interface(): void
    {
        $this->shouldImplement(OrderPaymentRefundInterface::class);
    }

    public function it_refunds_order_payment_when_token_is_not_null_and_order_mollie_id_was_found(
        UnitsRefunded $units,
        PaymentInterface $payment,
        Payum $payum,
        StorageInterface $storage,
        TokenInterface $token,
        GatewayInterface $gateway,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig
    ): void {
        $mollieFactoryName = 'mollie';

        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [
                        'order_item_1',
                        'order_item_2',
                    ],
                    'shipments' => [
                        'dhl',
                        'fedex',
                    ],
                ],
                'refund_token' => 'test_token',
            ],
            'order_mollie_id' => 1,
        ];

        $payment->getMethod()
            ->willReturn($paymentMethod);

        $paymentMethod->getGatewayConfig()
            ->willReturn($gatewayConfig);

        $gatewayConfig->getFactoryName()
            ->willReturn($mollieFactoryName);

        $payment->getDetails()->willReturn($details);
        $hash = $details['metadata']['refund_token'];
        $payment->setDetails($details);
        $payum->getTokenStorage()->willReturn($storage);

        $storage->find($hash)->willReturn($token);

        $token->getGatewayName()->willReturn('mollie');
        $payum->getGateway('mollie')->willReturn($gateway);

        $gateway->execute(new RefundOrder($token->getWrappedObject()))->shouldBeCalledOnce();

        $this->refund($units);
    }

    public function it_refunds_order_payment_when_token_is_not_null_and_order_mollie_id_was_not_found(
        UnitsRefunded $units,
        PaymentInterface $payment,
        Payum $payum,
        StorageInterface $storage,
        TokenInterface $token,
        GatewayInterface $gateway,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig
    ): void {
        $mollieFactoryName = 'mollie';
        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [
                        'order_item_1',
                        'order_item_2',
                    ],
                    'shipments' => [
                        'dhl',
                        'fedex',
                    ],
                ],
                'refund_token' => 'test_token',
            ],
        ];

        $payment->getMethod()
            ->willReturn($paymentMethod);

        $paymentMethod->getGatewayConfig()
            ->willReturn($gatewayConfig);

        $gatewayConfig->getFactoryName()
            ->willReturn($mollieFactoryName);

        $payment->getDetails()->willReturn($details);
        $hash = $details['metadata']['refund_token'];
        $payment->setDetails($details);
        $payum->getTokenStorage()->willReturn($storage);

        $storage->find($hash)->willReturn($token);

        $token->getGatewayName()->willReturn('test_gateway');
        $payum->getGateway('test_gateway')->willReturn($gateway);

        $gateway->execute(new RefundAction($token->getWrappedObject()))->shouldBeCalledOnce();

        $this->refund($units);
    }

    public function it_not_refunds_order_payment_when_token_is_null(
        UnitsRefunded $units,
        PaymentInterface $payment,
        Payum $payum,
        StorageInterface $storage,
        TokenInterface $token,
        GatewayInterface $gateway,
        MollieLoggerActionInterface $loggerAction,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig
    ): void {
        $mollieFactoryName = 'mollie';

        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [
                        'order_item_1',
                        'order_item_2',
                    ],
                    'shipments' => [
                        'dhl',
                        'fedex',
                    ],
                ],
                'refund_token' => 'test_token',
            ],
        ];

        $payment->getMethod()
            ->willReturn($paymentMethod);

        $paymentMethod->getGatewayConfig()
            ->willReturn($gatewayConfig);

        $gatewayConfig->getFactoryName()
            ->willReturn($mollieFactoryName);

        $payment->getDetails()->willReturn($details);
        $hash = $details['metadata']['refund_token'];
        $payment->setDetails($details);
        $payum->getTokenStorage()->willReturn($storage);

        $storage->find($hash)->willReturn(null);

        $token->getGatewayName()->willReturn('test_gateway');
        $payum->getGateway('test_gateway')->willReturn($gateway);

        $loggerAction->addNegativeLog(sprintf('A token with hash `%s` could not be found.', $hash))->shouldBeCalledOnce();

        $this->shouldThrow(BadRequestHttpException::class)->during('refund', [$units]);
    }

    public function it_refunds_order_payment_when_payment_is_null(
        RepositoryInterface $orderRepository,
        OrderInterface $order,
        MollieLoggerActionInterface $loggerAction,
        UnitsRefunded $units
    ): void {
        $orderRepository->findOneBy(['number' => '#0000001'])->willReturn($order);

        $order->getPayments()->willReturn(new ArrayCollection([
            null,
        ]));

        $loggerAction->addNegativeLog(sprintf('Not fount payment in refund'))->shouldBeCalled();

        $this->shouldThrow(NotFoundHttpException::class)->during('refund', [$units]);
    }

    public function it_refunds_order_payment_when_factory_name_is_not_in_array(
        GatewayConfigInterface $config,
        PaymentInterface $payment
    ): void {
        $config->getFactoryName()->willReturn('definitely_not_mollie_subscription_test');
        $payment->getDetails()->shouldNotBeCalled();
    }
}
