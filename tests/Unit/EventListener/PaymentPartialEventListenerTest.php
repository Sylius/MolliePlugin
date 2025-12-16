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

namespace Tests\Sylius\MolliePlugin\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\EventListener\PaymentPartialEventListener;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Refund\Handler\OrderPaymentRefundInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Sylius\RefundPlugin\Entity\RefundInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;

final class PaymentPartialEventListenerTest extends TestCase
{
    public function testSkipsWhenOrderNotFound(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn(null);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::never())->method('refund');

        $listener->next($event);
    }

    public function testSkipsWhenMollieAlreadyHasRefund(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $divisorProvider->method('getDivisor')->willReturn(100);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn(['payment_mollie_id' => 'tr_test123']);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $refund = $this->createMock(RefundInterface::class);
        $refund->method('getAmount')->willReturn(1000);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$refund]);

        $molliePayment = $this->createMock(Payment::class);
        $molliePayment->amountRefunded = (object) ['value' => '10.00'];

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $paymentEndpoint->method('get')->with('tr_test123')->willReturn($molliePayment);

        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $mollieApiClient->payments = $paymentEndpoint;

        $mollieApiClientResolver->method('getClientWithKey')->with($order)->willReturn($mollieApiClient);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::never())->method('refund');
        $logger->expects(self::once())->method('addLog');

        $listener->next($event);
    }

    public function testProcessesWhenAdminTriggersNewRefund(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $divisorProvider->method('getDivisor')->willReturn(100);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn(['payment_mollie_id' => 'tr_test123']);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $existingRefund = $this->createMock(RefundInterface::class);
        $existingRefund->method('getAmount')->willReturn(700);
        $currentRefund = $this->createMock(RefundInterface::class);
        $currentRefund->method('getAmount')->willReturn(600);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$existingRefund, $currentRefund]);

        $molliePayment = $this->createMock(Payment::class);
        $molliePayment->amountRefunded = (object) ['value' => '7.00'];

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $paymentEndpoint->method('get')->with('tr_test123')->willReturn($molliePayment);

        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $mollieApiClient->payments = $paymentEndpoint;

        $mollieApiClientResolver->method('getClientWithKey')->with($order)->willReturn($mollieApiClient);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 600, 'USD', '');

        $orderPaymentRefund->expects(self::once())->method('refund')->with($event);

        $listener->next($event);
    }

    public function testProcessesWhenMollieHasNoRefunds(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $divisorProvider->method('getDivisor')->willReturn(100);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn(['payment_mollie_id' => 'tr_test123']);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $refund = $this->createMock(RefundInterface::class);
        $refund->method('getAmount')->willReturn(1000);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$refund]);

        $molliePayment = $this->createMock(Payment::class);
        $molliePayment->amountRefunded = null;

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $paymentEndpoint->method('get')->with('tr_test123')->willReturn($molliePayment);

        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $mollieApiClient->payments = $paymentEndpoint;

        $mollieApiClientResolver->method('getClientWithKey')->with($order)->willReturn($mollieApiClient);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::once())->method('refund')->with($event);

        $listener->next($event);
    }

    public function testSkipsWhenNoPaymentMollieId(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn([]);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 1000, 'USD', '');

        $orderPaymentRefund->expects(self::never())->method('refund');

        $listener->next($event);
    }

    public function testSkipsWebhookForMollieTriggeredRefund(): void
    {
        $orderPaymentRefund = $this->createMock(OrderPaymentRefundInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $mollieApiClientResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $divisorProvider = $this->createMock(DivisorProviderInterface::class);
        $refundRepository = $this->createMock(RepositoryInterface::class);

        $divisorProvider->method('getDivisor')->willReturn(100);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn(['payment_mollie_id' => 'tr_test123']);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPayments')->willReturn(new ArrayCollection([$payment]));
        $order->method('getId')->willReturn(1);
        $orderRepository->method('findOneBy')->with(['number' => '0001'])->willReturn($order);

        $existingRefund = $this->createMock(RefundInterface::class);
        $existingRefund->method('getAmount')->willReturn(700);
        $currentRefund = $this->createMock(RefundInterface::class);
        $currentRefund->method('getAmount')->willReturn(600);
        $refundRepository->method('findBy')->with(['order' => 1])->willReturn([$existingRefund, $currentRefund]);

        $molliePayment = $this->createMock(Payment::class);
        $molliePayment->amountRefunded = (object) ['value' => '13.00'];

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $paymentEndpoint->method('get')->with('tr_test123')->willReturn($molliePayment);

        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $mollieApiClient->payments = $paymentEndpoint;

        $mollieApiClientResolver->method('getClientWithKey')->with($order)->willReturn($mollieApiClient);

        $listener = new PaymentPartialEventListener(
            $orderPaymentRefund,
            $logger,
            $orderRepository,
            $mollieApiClientResolver,
            $divisorProvider,
            $refundRepository,
        );

        $event = new UnitsRefunded('0001', [], 1, 600, 'USD', '');

        $orderPaymentRefund->expects(self::never())->method('refund');
        $logger->expects(self::once())->method('addLog');

        $listener->next($event);
    }
}
