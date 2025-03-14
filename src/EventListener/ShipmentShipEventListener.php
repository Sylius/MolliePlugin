<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\EventListener;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Webmozart\Assert\Assert;

final class ShipmentShipEventListener
{
    /** @var RequestStack */
    private $requestStack;

    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApiClient $apiClient, RequestStack $requestStack)
    {
        $this->apiClient = $apiClient;
        $this->requestStack = $requestStack;
    }

    public function shipAll(GenericEvent $event): void
    {
        /** @var ?ShipmentInterface $shipment */
        $shipment = $event->getSubject();
        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $payment = $order->getLastPayment();

        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        $factoryName = $paymentMethod->getGatewayConfig()->getFactoryName() ?? null;

        if (!isset($payment->getDetails()['order_mollie_id']) || MollieGatewayFactory::FACTORY_NAME !== $factoryName) {
            return;
        }

        $modusKey = $this->getModus($paymentMethod->getGatewayConfig()->getConfig());

        try {
            $this->apiClient->setApiKey($modusKey);
            /** @var Order $order */
            $order = $this->apiClient->orders->get($payment->getDetails()['order_mollie_id']);
            $order->shipAll();
        } catch (ApiException $e) {
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', $e->getMessage());
        }
    }

    private function getModus(array $config): string
    {
        if ($config['environment']) {
            return $config[MollieGatewayConfigurationType::API_KEY_LIVE];
        }

        return $config[MollieGatewayConfigurationType::API_KEY_TEST];
    }
}
