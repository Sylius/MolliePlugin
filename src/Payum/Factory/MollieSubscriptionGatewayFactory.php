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

namespace Sylius\MolliePlugin\Payum\Factory;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Sylius\Bundle\CoreBundle\Application\Kernel;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;

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
            'payum.http_client' => '@sylius_mollie.client.mollie_api',
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
