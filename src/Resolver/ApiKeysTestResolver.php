<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Creator\ApiKeysTestCreatorInterface;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Symfony\Component\HttpFoundation\Request;

final class ApiKeysTestResolver implements ApiKeysTestResolverInterface
{
    /** @var ApiKeysTestCreatorInterface */
    private $apiKeysTestCreator;

    public function __construct(
        ApiKeysTestCreatorInterface $apiKeysTestCreator
    ) {
        $this->apiKeysTestCreator = $apiKeysTestCreator;
    }

    public function fromRequest(Request $request): array
    {
        $testApiKey = $request->get(MollieGatewayConfigurationType::API_KEY_TEST);
        $liveApiKey = $request->get(MollieGatewayConfigurationType::API_KEY_LIVE);

        return [
            $this->apiKeysTestCreator->create(MollieGatewayConfigurationType::API_KEY_TEST, $testApiKey),
            $this->apiKeysTestCreator->create(MollieGatewayConfigurationType::API_KEY_LIVE, $liveApiKey),
        ];
    }
}
