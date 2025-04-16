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

namespace Sylius\MolliePlugin\PartialShip;

use Mollie\Api\Exceptions\ApiException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\PartialShip\Resolver\FromSyliusToMollieLinesResolverInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Webmozart\Assert\Assert;

final class OrderMolliePartialShip implements OrderMolliePartialShipInterface
{
    public function __construct(
        private readonly MollieApiClient $apiClient,
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly FromSyliusToMollieLinesResolverInterface $mollieUnitsResolver,
    ) {
    }

    public function partialShip(OrderInterface $order): void
    {
        $shipments = $order->getShipments();
        $units = $shipments->last()->getUnits();

        if ($units->isEmpty()) {
            return;
        }

        $payment = $order->getLastPayment();
        Assert::notNull($payment);

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            return;
        }

        $factoryName = $gatewayConfig->getFactoryName();
        if (!isset($payment->getDetails()['order_mollie_id']) || MollieGatewayFactory::FACTORY_NAME !== $factoryName) {
            return;
        }

        $modusKey = $this->getModus($gatewayConfig->getConfig());

        try {
            $this->apiClient->setApiKey($modusKey);
            $mollieOrder = $this->apiClient->orders->get($payment->getDetails()['order_mollie_id']);

            $lines = $this->mollieUnitsResolver->resolve($units, $mollieOrder);

            $mollieOrder->createShipment(['lines' => $lines->getArrayFromObject()]);

            $this->loggerAction->addLog(sprintf('Partial ship with order id %s: ', $mollieOrder->id));
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error partial ship with message %s: ', $e->getMessage()));
        }
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
