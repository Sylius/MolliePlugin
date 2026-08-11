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

namespace Tests\Sylius\MolliePlugin\Unit\Api\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment as MolliePaymentResource;
use Mollie\Api\Types\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Api\Controller\SelectMollieMethodAction;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\PaymentDataCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class SelectMollieMethodActionTest extends TestCase
{
    public function testItAppendsPreviousMollieIdToHistoryOnRetryWithDifferentMethod(): void
    {
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $apiClientKeyResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $mollieGatewayFactoryChecker = $this->createMock(MollieGatewayFactoryCheckerInterface::class);
        $mollieCustomerRepository = $this->createMock(RepositoryInterface::class);
        $subscriptionFactory = $this->createMock(MollieSubscriptionFactoryInterface::class);
        $subscriptionRepository = $this->createMock(MollieSubscriptionRepositoryInterface::class);
        $paymentDataCreator = $this->createMock(PaymentDataCreatorInterface::class);
        $logger = $this->createMock(MollieLoggerActionInterface::class);

        $action = new SelectMollieMethodAction(
            $orderRepository,
            $entityManager,
            $apiClientKeyResolver,
            $mollieGatewayFactoryChecker,
            $mollieCustomerRepository,
            $subscriptionFactory,
            $subscriptionRepository,
            $paymentDataCreator,
            $logger,
        );

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn([
            'payment_mollie_id' => 'tr_old',
            'mollie_payment_ids_history' => ['tr_older'],
        ]);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPaymentState')->willReturn('awaiting_payment');
        $order->method('getLastPayment')->willReturn($payment);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('mollie');

        $orderRepository->method('findOneByTokenValue')->with('order_token')->willReturn($order);
        $mollieGatewayFactoryChecker->method('isMollieGateway')->willReturn(true);

        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $apiClientKeyResolver->method('getClientWithKey')->willReturn($mollieApiClient);

        $paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $mollieApiClient->payments = $paymentEndpoint;

        $existingMolliePayment = new MolliePaymentResource($mollieApiClient);
        $existingMolliePayment->id = 'tr_old';
        $existingMolliePayment->status = PaymentStatus::STATUS_PAID;
        $paymentEndpoint->method('get')->with('tr_old')->willReturn($existingMolliePayment);

        $paymentDataCreator->method('create')->willReturn([
            'webhookUrl' => 'https://example.com/webhook',
            'redirectUrl' => 'https://example.com/redirect',
        ]);

        $newMolliePayment = (object) [
            'id' => 'tr_new',
            '_links' => (object) ['checkout' => (object) ['href' => 'https://example.com/checkout']],
        ];
        $paymentEndpoint->method('create')->willReturn($newMolliePayment);

        $capturedDetails = null;
        $payment->expects(self::once())
            ->method('setDetails')
            ->with(self::callback(function (array $details) use (&$capturedDetails): bool {
                $capturedDetails = $details;

                return true;
            }))
        ;

        $request = new Request([], [], [], [], [], [], json_encode([
            'methodId' => 'ideal',
            'backUrl' => 'https://example.com/back',
        ]));

        $action('order_token', $request);

        self::assertSame(['tr_older', 'tr_old'], $capturedDetails['mollie_payment_ids_history']);
    }
}
