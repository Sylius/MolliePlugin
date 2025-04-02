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

namespace Sylius\MolliePlugin\Resolver;

use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;

final class MollieMethodsResolver implements MollieMethodsResolverInterface
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly MollieApiClient $mollieApiClient,
        private readonly RepositoryInterface $gatewayConfigRepository,
        private readonly MollieMethodsCreatorInterface $mollieMethodsCreator,
    ) {
    }

    public function create(): void
    {
        $gateways = $this->gatewayConfigRepository->findBy([
            'factoryName' => [
                MollieGatewayFactory::FACTORY_NAME,
            ],
        ]);
        /** @var GatewayConfigInterface $gateway */
        foreach ($gateways as $gateway) {
            $this->createForGateway($gateway);
        }
    }

    public function createForGateway(GatewayConfigInterface $gateway): void
    {
        $config = $gateway->getConfig();
        $environment = true === $config['environment'] ?
            MollieGatewayConfigurationType::API_KEY_LIVE :
            MollieGatewayConfigurationType::API_KEY_TEST;
        $recurring = array_key_exists('times', $config) && array_key_exists('interval', $config);

        /** @var MollieApiClient $client */
        $client = $this->mollieApiClient->setApiKey($config[$environment]);
        $client->setIsRecurringSubscription($recurring);

        if (MollieSubscriptionGatewayFactory::FACTORY_NAME === $gateway->getFactoryName()) {
            /** @var MethodCollection $baseCollection */
            $baseCollection = $client->methods->allActive(self::PARAMETERS);
            $recurringCollection = $client->methods->allActive(self::PARAMETERS_RECURRING);
            foreach ($recurringCollection as $recurringEntry) {
                $baseCollection->append($recurringEntry);
            }

            $this->mollieMethodsCreator->createMethods($baseCollection, $gateway);
        } elseif (MollieGatewayFactory::FACTORY_NAME === $gateway->getFactoryName()) {
            /** @var MethodCollection $allMollieMethods */
            $allMollieMethods = $client->methods->allAvailable(self::PARAMETERS_AVAILABLE);

            $filteredMethods = array_filter($allMollieMethods->getArrayCopy(), [$this, 'filterActiveMethods']);
            $allMollieMethods->exchangeArray($filteredMethods);

            $this->mollieMethodsCreator->createMethods($allMollieMethods, $gateway);
        } else {
            $this->loggerAction->addLog(sprintf('Unable to download methods for "%s"', $gateway->getGatewayName()));

            return;
        }

        $this->loggerAction->addLog(sprintf('Downloaded all methods from mollie API'));
    }

    private function filterActiveMethods(Method $method): bool
    {
        return $method->status === 'activated';
    }
}
