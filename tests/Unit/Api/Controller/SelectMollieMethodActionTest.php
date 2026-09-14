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
use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SelectMollieMethodActionTest extends TestCase
{
    public function testItTracksOnlyTheNewMollieSessionAfterAMethodChange(): void
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
        $molliePaymentsMethodResolver = $this->createMock(MolliePaymentsMethodResolverInterface::class);
        $molliePaymentsMethodResolver->method('resolve')->willReturn($this->offeredMethods(['ideal']));

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
            $molliePaymentsMethodResolver,
        );

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getDetails')->willReturn([
            'payment_mollie_id' => 'tr_old',
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
            'metadata' => [
                'order_id' => 42,
                'customer_id' => 7,
                'molliePaymentMethods' => 'ideal',
            ],
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

        self::assertSame('tr_new', $capturedDetails['payment_mollie_id']);
        self::assertSame(42, $capturedDetails['metadata']['order_id']);
    }

    public function testItRejectsAMethodThatIsNotOfferedForTheOrder(): void
    {
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $apiClientKeyResolver = $this->createMock(MollieApiClientKeyResolverInterface::class);
        $mollieGatewayFactoryChecker = $this->createMock(MollieGatewayFactoryCheckerInterface::class);
        $paymentDataCreator = $this->createMock(PaymentDataCreatorInterface::class);
        $molliePaymentsMethodResolver = $this->createMock(MolliePaymentsMethodResolverInterface::class);

        $action = new SelectMollieMethodAction(
            $orderRepository,
            $this->createMock(EntityManagerInterface::class),
            $apiClientKeyResolver,
            $mollieGatewayFactoryChecker,
            $this->createMock(RepositoryInterface::class),
            $this->createMock(MollieSubscriptionFactoryInterface::class),
            $this->createMock(MollieSubscriptionRepositoryInterface::class),
            $paymentDataCreator,
            $this->createMock(MollieLoggerActionInterface::class),
            $molliePaymentsMethodResolver,
        );

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getPaymentState')->willReturn('awaiting_payment');
        $order->method('getLastPayment')->willReturn($payment);

        $orderRepository->method('findOneByTokenValue')->with('order_token')->willReturn($order);
        $mollieGatewayFactoryChecker->method('isMollieGateway')->willReturn(true);
        $molliePaymentsMethodResolver->method('resolve')->willReturn($this->offeredMethods(['ideal', 'bancontact']));

        $apiClientKeyResolver->expects(self::never())->method('getClientWithKey');
        $paymentDataCreator->expects(self::never())->method('create');

        $request = new Request([], [], [], [], [], [], json_encode([
            'methodId' => 'paypal',
            'backUrl' => 'https://example.com/back',
        ]));

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('The payment method is not available for order "order_token"');

        $action('order_token', $request);
    }

    /**
     * @param string[] $methodIds
     *
     * @return array{
     *     data: array<string, string>,
     *     image: array<string, string>,
     *     issuers: array<string, mixed>|null,
     *     paymentFee: array<string, mixed>
     * }
     */
    private function offeredMethods(array $methodIds): array
    {
        return [
            'data' => array_combine($methodIds, $methodIds),
            'image' => [],
            'issuers' => [],
            'paymentFee' => [],
        ];
    }
}
