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

namespace Sylius\MolliePlugin\Provider\Methods;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;

final class MollieMethodsProvider implements MollieMethodsProviderInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly MollieLoggerActionInterface $loggerAction,
    ) {
    }

    public function getMethods(GatewayConfigInterface $gateway): array
    {
        $factoryName = $gateway->getFactoryName();
        if (!in_array($factoryName, [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME], true)) {
            $this->loggerAction->addLog(sprintf('Unable to download methods for "%s"', $factoryName));

            return [];
        }

        try {
            $client = $this->getClient($gateway);

            if (MollieGatewayFactory::FACTORY_NAME === $factoryName) {
                return $this->fetchRegular($client);
            }

            return $this->fetchSubscription($client);
        } catch (ApiException $exception) {
            throw MollieMethodsException::retrieval($exception);
        }
    }

    /**
     * @return Method[]
     *
     * @throws ApiException
     */
    private function fetchSubscription(MollieApiClient $client): array
    {
        /** @var MethodCollection $baseCollection */
        $baseCollection = $client->methods->allActive(MollieMethodsResolverInterface::PARAMETERS);
        $recurringCollection = $client->methods->allActive(MollieMethodsResolverInterface::PARAMETERS_RECURRING);
        foreach ($recurringCollection as $recurringEntry) {
            $baseCollection->append($recurringEntry);
        }

        return $baseCollection->getArrayCopy();
    }

    /**
     * @return Method[]
     *
     * @throws ApiException
     */
    private function fetchRegular(MollieApiClient $client): array
    {
        /** @var MethodCollection $allMollieMethods */
        $allMollieMethods = $client->methods->allAvailable(MollieMethodsResolverInterface::PARAMETERS_AVAILABLE);

        return array_filter(
            $allMollieMethods->getArrayCopy(),
            fn (object $method) => ($method->status ?? null) === 'activated',
        );
    }

    /** @throws ApiException */
    private function getClient(GatewayConfigInterface $gateway): MollieApiClient
    {
        $config = $gateway->getConfig();
        $environment = true === $config['environment']
            ? MollieGatewayConfigurationType::API_KEY_LIVE
            : MollieGatewayConfigurationType::API_KEY_TEST
        ;

        $this->mollieApiClient->setApiKey($config[$environment]);

        $recurring = array_key_exists('times', $config) && array_key_exists('interval', $config);
        $this->mollieApiClient->setIsRecurringSubscription($recurring);

        return $this->mollieApiClient;
    }
}
