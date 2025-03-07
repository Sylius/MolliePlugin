<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Creator;

use Mollie\Api\Resources\Method;
use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\DTO\ApiKeyTest;
use SyliusMolliePlugin\Form\Type\MollieGatewayConfigurationType;
use SyliusMolliePlugin\Resolver\MollieMethodsResolverInterface;
use Mollie\Api\Resources\MethodCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ApiKeysTestCreator implements ApiKeysTestCreatorInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        MollieApiClient $mollieApiClient,
        TranslatorInterface $translator
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->translator = $translator;
    }

    public function create(string $keyType, string $key = null): ApiKeyTest
    {
        $apiKeyTest = new ApiKeyTest(
            $keyType,
            null !== $key && '' !== $key
        );

        if (null === $key || '' === (trim($key))) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie_plugin.ui.insert_you_key_first'));

            return $apiKeyTest;
        }

        if (MollieGatewayConfigurationType::API_KEY_TEST === $apiKeyTest->getType() && !str_starts_with($key, self::TEST_PREFIX)) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie_plugin.ui.api_key_start_with_api_key_test'));

            return $apiKeyTest;
        }

        if (MollieGatewayConfigurationType::API_KEY_LIVE === $apiKeyTest->getType() && !str_starts_with($key, self::LIVE_PREFIX)) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('sylius_mollie_plugin.ui.api_key_start_with_api_key_live'));

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
            $filteredMethods = array_filter($methods->getArrayCopy(), array($this, 'filterActiveMethods'));
            $methods->exchangeArray($filteredMethods);

            $apiKeyTest->setMethods($methods);

            return $apiKeyTest;
        } catch (\Exception $exception) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);

            if (0 === $exception->getCode()) {
                $apiKeyTest->setMessage($this->translator->trans(
                    \sprintf('sylius_mollie_plugin.ui.api_key_start_with_%s', $apiKeyTest->getType())
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
