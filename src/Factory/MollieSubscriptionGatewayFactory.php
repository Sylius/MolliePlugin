<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Sylius\Bundle\CoreBundle\Application\Kernel;

final class MollieSubscriptionGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'mollie_subscription';

    protected function populateConfig(ArrayObject $config): void
    {
        $environment = true === $config['environment'] ?
            MollieGatewayConfigurationType::API_KEY_LIVE :
            MollieGatewayConfigurationType::API_KEY_TEST;

        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'Mollie subscription',
            'payum.http_client' => '@sylius_mollie_plugin.mollie_api_client',
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'api_key' => null,
                'method' => null,
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                $environment,
            ];

            $config['payum.api'] = function (ArrayObject $config) use ($environment): MollieApiClient {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var MollieApiClient $mollieApiClient */
                $mollieApiClient = $config['payum.http_client'];
                $mollieApiClient->setApiKey($config[$environment]);
                $mollieApiClient->setConfig($config->toUnsafeArray());
                $mollieApiClient->setIsRecurringSubscription(true);
                $mollieApiClient->addVersionString(\sprintf('Sylius/%s', Kernel::VERSION));
                $mollieApiClient->addVersionString(\sprintf('SubscriptionSyliusMolliePlugin/%s', $mollieApiClient->getVersion()));
                $mollieApiClient->addVersionString(\sprintf('uap/%s', $mollieApiClient->getUserAgentToken()));

                return $mollieApiClient;
            };
        }
    }
}
