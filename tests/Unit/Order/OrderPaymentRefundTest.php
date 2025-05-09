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

namespace Tests\Sylius\MolliePlugin\Unit\Order;

use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Refund as RefundAction;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Request\Refund\RefundOrder;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefund;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\Filter\UnitRefundFilterInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Sylius\RefundPlugin\Model\UnitRefundInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderPaymentRefundTest extends TestCase
{
    private MockObject|RepositoryInterface $orderRepository;

    private MockObject|MollieLoggerActionInterface $loggerAction;

    private MockObject|Payum $payum;

    private MockObject|UnitRefundFilterInterface $unitRefundFilter;

    private OrderPaymentRefund $orderPaymentRefund;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(RepositoryInterface::class);
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->payum = $this->createMock(Payum::class);
        $this->unitRefundFilter = $this->createMock(UnitRefundFilterInterface::class);

        $this->orderPaymentRefund = new OrderPaymentRefund(
            $this->orderRepository,
            $this->loggerAction,
            $this->payum,
            $this->unitRefundFilter,
        );
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(OrderPaymentRefund::class, $this->orderPaymentRefund);
    }

    public function testItImplementsOrderPaymentRefundInterface(): void
    {
        $this->assertInstanceOf(OrderPaymentRefundInterface::class, $this->orderPaymentRefund);
    }

    public function testItRefundsOrderPaymentWhenTokenIsNotNullAndOrderMollieIdWasFound(): void
    {
        $firstShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $secondShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $firstUnitRefund = $this->createMock(UnitRefundInterface::class);
        $secondUnitRefund = $this->createMock(UnitRefundInterface::class);

        $units = $this->createMock(UnitsRefunded::class);
        $units->method('orderNumber')->willReturn('#0000001');

        $units->method('units')->willReturn([
            $firstShipmentRefund,
            $secondShipmentRefund,
            $firstUnitRefund,
            $secondUnitRefund,
        ]);

        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->orderRepository->method('findOneBy')->willReturn($order);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('mollie_subscription');

        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [],
                    'shipments' => [],
                ],
                'refund_token' => 'test_token',
            ],
            'order_mollie_id' => 1,
        ];

        $this->unitRefundFilter
            ->expects($this->exactly(2))
            ->method('filterUnitRefunds')
            ->with(
                [$firstUnitRefund, $secondUnitRefund, $firstShipmentRefund, $secondShipmentRefund],
                $this->logicalOr(
                    $this->equalTo(OrderItemUnitRefund::class),
                    $this->equalTo(ShipmentRefund::class),
                ),
            )
            ->willReturnOnConsecutiveCalls([
                $firstUnitRefund,
                $secondUnitRefund,
            ], [
                $firstShipmentRefund,
                $secondShipmentRefund,
            ])
        ;

        $payment->method('getDetails')->willReturn($details);
        $payment->method('setDetails')->willReturnCallback(function ($newDetails) use (&$details) {
            $details = $newDetails;
        });

        $this->payum->method('getTokenStorage')->willReturn($storage);
        $storage->method('find')->willReturn($token);
        $token->method('getGatewayName')->willReturn('mollie');
        $this->payum->method('getGateway')->willReturn($gateway);

        $gateway->expects($this->once())->method('execute')->with($this->isInstanceOf(RefundOrder::class));

        $this->orderPaymentRefund->refund($units);
    }

    public function testItRefundsOrderPaymentWhenTokenIsNotNullAndOrderMollieIdWasNotFound(): void
    {
        $firstShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $secondShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $firstUnitRefund = $this->createMock(UnitRefundInterface::class);
        $secondUnitRefund = $this->createMock(UnitRefundInterface::class);

        $units = $this->createMock(UnitsRefunded::class);
        $units->method('orderNumber')->willReturn('#0000001');

        $units->method('units')->willReturn([
            $firstShipmentRefund,
            $secondShipmentRefund,
            $firstUnitRefund,
            $secondUnitRefund,
        ]);

        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $storage = $this->createMock(StorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->orderRepository->method('findOneBy')->willReturn($order);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('mollie_subscription');

        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [],
                    'shipments' => [],
                ],
                'refund_token' => 'test_token',
            ],
        ];

        $this->unitRefundFilter
            ->expects($this->exactly(2))
            ->method('filterUnitRefunds')
            ->with(
                [$firstUnitRefund, $secondUnitRefund, $firstShipmentRefund, $secondShipmentRefund],
                $this->logicalOr(
                    $this->equalTo(OrderItemUnitRefund::class),
                    $this->equalTo(ShipmentRefund::class),
                ),
            )
            ->willReturnOnConsecutiveCalls([
                $firstUnitRefund,
                $secondUnitRefund,
            ], [
                $firstShipmentRefund,
                $secondShipmentRefund,
            ])
        ;

        $payment->method('getDetails')->willReturn($details);
        $payment->method('setDetails')->willReturnCallback(function ($newDetails) use (&$details) {
            $details = $newDetails;
        });

        $this->payum->method('getTokenStorage')->willReturn($storage);
        $storage->method('find')->willReturn($token);
        $token->method('getGatewayName')->willReturn('test_gateway');
        $this->payum->method('getGateway')->willReturn($gateway);

        $gateway->expects($this->once())->method('execute')->with($this->isInstanceOf(RefundAction::class));

        $this->orderPaymentRefund->refund($units);
    }

    public function testItNotRefundsOrderPaymentWhenTokenIsNull(): void
    {
        $firstShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $secondShipmentRefund = $this->createMock(UnitRefundInterface::class);
        $firstUnitRefund = $this->createMock(UnitRefundInterface::class);
        $secondUnitRefund = $this->createMock(UnitRefundInterface::class);

        $units = $this->createMock(UnitsRefunded::class);
        $units->method('orderNumber')->willReturn('#0000001');

        $units->method('units')->willReturn([
            $firstShipmentRefund,
            $secondShipmentRefund,
            $firstUnitRefund,
            $secondUnitRefund,
        ]);

        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $storage = $this->createMock(StorageInterface::class);

        $this->orderRepository->method('findOneBy')->willReturn($order);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('mollie_subscription');

        $details = [
            'metadata' => [
                'refund' => [
                    'items' => [],
                    'shipments' => [],
                ],
                'refund_token' => 'test_token',
            ],
        ];

        $this->unitRefundFilter
            ->expects($this->exactly(2))
            ->method('filterUnitRefunds')
            ->with(
                [$firstUnitRefund, $secondUnitRefund, $firstShipmentRefund, $secondShipmentRefund],
                $this->logicalOr(
                    $this->equalTo(OrderItemUnitRefund::class),
                    $this->equalTo(ShipmentRefund::class),
                ),
            )
            ->willReturnOnConsecutiveCalls([
                $firstUnitRefund,
                $secondUnitRefund,
            ], [
                $firstShipmentRefund,
                $secondShipmentRefund,
            ])
        ;

        $payment->method('getDetails')->willReturn($details);
        $this->payum->method('getTokenStorage')->willReturn($storage);
        $storage->method('find')->willReturn(null);

        $this->loggerAction
            ->expects($this->once())
            ->method('addNegativeLog')
            ->with('A token with hash `test_token` could not be found.')
        ;

        $this->expectException(BadRequestHttpException::class);

        $this->orderPaymentRefund->refund($units);
    }

    public function testItRefundsOrderPaymentWhenPaymentIsNull(): void
    {
        $units = $this->createMock(UnitsRefunded::class);
        $units->method('orderNumber')->willReturn('#0000001');

        $order = $this->createMock(OrderInterface::class);

        $this->orderRepository->method('findOneBy')->willReturn($order);
        $order->method('getPayments')->willReturn(new ArrayCollection([null]));

        $this->loggerAction->expects($this->once())->method('addNegativeLog')->with('No payment in refund');

        $this->unitRefundFilter->expects($this->never())->method('filterUnitRefunds')->withAnyParameters();

        $this->expectException(NotFoundHttpException::class);

        $this->orderPaymentRefund->refund($units);
    }

    public function testItRefundsOrderPaymentWhenFactoryNameIsNotInArray(): void
    {
        $units = $this->createMock(UnitsRefunded::class);
        $units->method('orderNumber')->willReturn('#0000001');

        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->orderRepository->method('findOneBy')->willReturn($order);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('definitely_not_mollie_subscription_test');

        $this->unitRefundFilter->expects($this->never())->method('filterUnitRefunds')->withAnyParameters();

        $payment->expects($this->never())->method('getDetails');

        $this->orderPaymentRefund->refund($units);
    }
}
