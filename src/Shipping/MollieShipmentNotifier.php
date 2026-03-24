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

namespace Sylius\MolliePlugin\Shipping;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order;
use Sylius\Bundle\ApiBundle\SectionResolver\AdminApiSection;
use Sylius\Bundle\ApiBundle\SectionResolver\ShopApiSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Webmozart\Assert\Assert;

final class MollieShipmentNotifier implements MollieShipmentNotifierInterface
{
    public function __construct(
        private readonly MollieApiClient $apiClient,
        private readonly SectionProviderInterface $sectionProvider,
    ) {
    }

    /**
     * @throws ApiException
     */
    public function shipAll(ShipmentInterface $shipment): void
    {
        $section = $this->sectionProvider->getSection();

        if (null !== $section && !$section instanceof AdminApiSection && !$section instanceof ShopApiSection) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $payment = $order->getLastPayment();

        if (null === $payment) {
            return;
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $factoryName = $gatewayConfig->getFactoryName();

        if (!isset($payment->getDetails()['order_mollie_id']) || MollieGatewayFactory::FACTORY_NAME !== $factoryName) {
            return;
        }

        $modusKey = $this->getModus($gatewayConfig->getConfig());

        $this->apiClient->setApiKey($modusKey);
        /** @var Order $mollieOrder */
        $mollieOrder = $this->apiClient->orders->get($payment->getDetails()['order_mollie_id']);
        $mollieOrder->shipAll();
    }

    /**
     * @param array{
     *    environment?: bool,
     *    api_key_live?: mixed,
     *    api_key_test?: mixed
     * } $config
     */
    private function getModus(array $config): string
    {
        if ($config['environment']) {
            return $config[MollieGatewayConfigurationType::API_KEY_LIVE];
        }

        return $config[MollieGatewayConfigurationType::API_KEY_TEST];
    }
}
