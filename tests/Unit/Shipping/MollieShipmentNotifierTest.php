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

namespace Tests\Sylius\MolliePlugin\Unit\Shipping;

use Mollie\Api\Endpoints\OrderEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\SectionResolver\AdminApiSection;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Bundle\ShopBundle\SectionResolver\ShopSection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Shipping\MollieShipmentNotifier;

final class MollieShipmentNotifierTest extends TestCase
{
    private MollieApiClient $apiClientMock;

    private SectionProviderInterface $sectionProviderMock;

    private MollieShipmentNotifier $notifier;

    protected function setUp(): void
    {
        $this->apiClientMock = $this->createMock(MollieApiClient::class);
        $this->sectionProviderMock = $this->createMock(SectionProviderInterface::class);
        $this->notifier = new MollieShipmentNotifier($this->apiClientMock, $this->sectionProviderMock);
    }

    public function testShipsAllInAdminApiContext(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $mollieOrderId = 'ord_test123';
        $apiKey = 'test_api_key';
        $shipment = $this->createShipmentWithMolliePayment($mollieOrderId, false, $apiKey);

        $mollieOrder = $this->createMock(Order::class);
        $mollieOrder->expects($this->once())->method('shipAll');

        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->method('get')->with($mollieOrderId)->willReturn($mollieOrder);

        $this->apiClientMock->expects($this->once())->method('setApiKey')->with($apiKey);
        $this->apiClientMock->orders = $orderEndpoint;

        $this->notifier->shipAll($shipment);
    }

    public function testShipsAllInShopApiContext(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new ShopApiSection());

        $mollieOrderId = 'ord_shop456';
        $apiKey = 'test_api_key';
        $shipment = $this->createShipmentWithMolliePayment($mollieOrderId, false, $apiKey);

        $mollieOrder = $this->createMock(Order::class);
        $mollieOrder->expects($this->once())->method('shipAll');

        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->method('get')->with($mollieOrderId)->willReturn($mollieOrder);

        $this->apiClientMock->orders = $orderEndpoint;

        $this->notifier->shipAll($shipment);
    }

    public function testShipsAllWhenSectionIsNull(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(null);

        $mollieOrderId = 'ord_cli789';
        $apiKey = 'test_api_key';
        $shipment = $this->createShipmentWithMolliePayment($mollieOrderId, false, $apiKey);

        $mollieOrder = $this->createMock(Order::class);
        $mollieOrder->expects($this->once())->method('shipAll');

        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->method('get')->with($mollieOrderId)->willReturn($mollieOrder);

        $this->apiClientMock->orders = $orderEndpoint;

        $this->notifier->shipAll($shipment);
    }

    public function testSkipsInNonApiWebContext(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new ShopSection());

        $shipment = $this->createShipmentWithMolliePayment('ord_123', false, 'key');

        $this->apiClientMock->expects($this->never())->method('setApiKey');

        $this->notifier->shipAll($shipment);
    }

    public function testUsesLiveKeyWhenEnvironmentIsLive(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $mollieOrderId = 'ord_live456';
        $liveApiKey = 'live_api_key';
        $shipment = $this->createShipmentWithMolliePayment($mollieOrderId, true, $liveApiKey);

        $mollieOrder = $this->createMock(Order::class);
        $mollieOrder->expects($this->once())->method('shipAll');

        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->method('get')->with($mollieOrderId)->willReturn($mollieOrder);

        $this->apiClientMock->expects($this->once())->method('setApiKey')->with($liveApiKey);
        $this->apiClientMock->orders = $orderEndpoint;

        $this->notifier->shipAll($shipment);
    }

    public function testDoesNothingWhenPaymentIsNull(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn(null);

        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getOrder')->willReturn($order);

        $this->apiClientMock->expects($this->never())->method('setApiKey');

        $this->notifier->shipAll($shipment);
    }

    public function testDoesNothingWhenPaymentIsNotMollie(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn('stripe');

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn(['order_mollie_id' => 'ord_123']);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getOrder')->willReturn($order);

        $this->apiClientMock->expects($this->never())->method('setApiKey');

        $this->notifier->shipAll($shipment);
    }

    public function testDoesNothingWhenMollieOrderIdIsMissing(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn(MollieGatewayFactory::FACTORY_NAME);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn([]);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getOrder')->willReturn($order);

        $this->apiClientMock->expects($this->never())->method('setApiKey');

        $this->notifier->shipAll($shipment);
    }

    public function testThrowsApiExceptionOnFailure(): void
    {
        $this->sectionProviderMock->method('getSection')->willReturn(new AdminApiSection());

        $mollieOrderId = 'ord_fail123';
        $shipment = $this->createShipmentWithMolliePayment($mollieOrderId, false, 'test_key');

        $orderEndpoint = $this->createMock(OrderEndpoint::class);
        $orderEndpoint->method('get')->with($mollieOrderId)->willThrowException(
            new ApiException('Mollie API error'),
        );

        $this->apiClientMock->orders = $orderEndpoint;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Mollie API error');

        $this->notifier->shipAll($shipment);
    }

    private function createShipmentWithMolliePayment(
        string $mollieOrderId,
        bool $liveEnvironment,
        string $apiKey,
    ): ShipmentInterface {
        $config = ['environment' => $liveEnvironment];
        if ($liveEnvironment) {
            $config[MollieGatewayConfigurationType::API_KEY_LIVE] = $apiKey;
        } else {
            $config[MollieGatewayConfigurationType::API_KEY_TEST] = $apiKey;
        }

        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $gatewayConfig->method('getConfig')->willReturn($config);

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn(['order_mollie_id' => $mollieOrderId]);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getLastPayment')->willReturn($payment);

        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getOrder')->willReturn($order);

        return $shipment;
    }
}
