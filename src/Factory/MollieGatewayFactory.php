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

namespace SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Sylius\Bundle\CoreBundle\Application\Kernel;

final class MollieGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'mollie';

    protected function populateConfig(ArrayObject $config): void
    {
        $environment = true === $config['environment'] ?
            MollieGatewayConfigurationType::API_KEY_LIVE :
            MollieGatewayConfigurationType::API_KEY_TEST;

        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'Mollie',
            'payum.http_client' => '@sylius_mollie_plugin.mollie_api_client',
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'api_key' => null,
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
                $mollieApiClient->addVersionString(\sprintf('Sylius/%s', Kernel::VERSION));
                $mollieApiClient->addVersionString(\sprintf('SyliusMolliePlugin/%s', $mollieApiClient->getVersion()));
                $mollieApiClient->addVersionString(\sprintf('uap/%s', $mollieApiClient->getUserAgentToken()));

                return $mollieApiClient;
            };
        }
    }
}
