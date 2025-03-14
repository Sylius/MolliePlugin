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

namespace SyliusMolliePlugin\Resolver;

use SyliusMolliePlugin\Creator\ApiKeysTestCreatorInterface;
use SyliusMolliePlugin\Form\Type\MollieGatewayConfigurationType;
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
