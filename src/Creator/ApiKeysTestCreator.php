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

namespace Sylius\MolliePlugin\Creator;

use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Model\DTO\ApiKeyTest;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ApiKeysTestCreator implements ApiKeysTestCreatorInterface
{
    public function __construct(
        private readonly MollieApiClient $mollieApiClient,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function create(string $keyType, ?string $key = null): ApiKeyTest
    {
        $apiKeyTest = new ApiKeyTest(
            $keyType,
            null !== $key && '' !== $key,
        );

        if (null === $key || '' === (trim($key))) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie.ui.insert_you_key_first'));

            return $apiKeyTest;
        }

        if (MollieGatewayConfigurationType::API_KEY_TEST === $apiKeyTest->getType() && !str_starts_with($key, self::TEST_PREFIX)) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie.ui.api_key_start_with_api_key_test'));

            return $apiKeyTest;
        }

        if (MollieGatewayConfigurationType::API_KEY_LIVE === $apiKeyTest->getType() && !str_starts_with($key, self::LIVE_PREFIX)) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie.ui.api_key_start_with_api_key_live'));

            return $apiKeyTest;
        }

        return $this->testApiKey($apiKeyTest, $key);
    }

    private function testApiKey(ApiKeyTest $apiKeyTest, string $apiKey): ApiKeyTest
    {
        try {
            $client = $this->mollieApiClient->setApiKey($apiKey);

            /** @var MethodCollection $methods */
            $methods = $client->methods->allAvailable(MollieMethodsResolverInterface::PARAMETERS_AVAILABLE);
            $filteredMethods = array_filter($methods->getArrayCopy(), [$this, 'filterActiveMethods']);
            $methods->exchangeArray($filteredMethods);

            $apiKeyTest->setMethods($methods);

            return $apiKeyTest;
        } catch (\Exception $exception) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);

            if (0 === $exception->getCode()) {
                $apiKeyTest->setMessage($this->translator->trans(
                    \sprintf('sylius_mollie.ui.api_key_start_with_%s', $apiKeyTest->getType()),
                ));

                return $apiKeyTest;
            }

            $apiKeyTest->setMessage($this->translator->trans(''));

            return $apiKeyTest;
        }
    }

    private function filterActiveMethods(Method $method): bool
    {
        return $method->status === 'activated';
    }
}
