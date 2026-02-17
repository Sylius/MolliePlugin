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

namespace Sylius\MolliePlugin\Api\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response as ResponseModel;
use ApiPlatform\OpenApi\OpenApi;
use Sylius\Bundle\ApiBundle\OpenApi\Documentation\DocumentationModifierInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class MollieDocumentationModifier implements DocumentationModifierInterface
{
    public function __construct(
        private string $shopApiRoute,
    ) {
    }

    public function modify(OpenApi $docs): OpenApi
    {
        $paths = $docs->getPaths();
        $schemas = $docs->getComponents()->getSchemas();

        $schemas = $this->addSchemas($schemas);
        $this->addPaths($paths);

        return $docs
            ->withPaths($paths)
            ->withComponents($docs->getComponents()->withSchemas($schemas))
        ;
    }

    private function addPaths(Paths $paths): void
    {
        $tokenParameter = new Parameter(
            name: 'tokenValue',
            in: 'path',
            description: 'The token of the order',
            required: true,
            schema: ['type' => 'string'],
        );

        $methodsPath = sprintf('%s/orders/{tokenValue}/mollie-methods', $this->shopApiRoute);
        $paths->addPath($methodsPath, new PathItem(
            ref: 'MollieMethods',
            get: new Operation(
                operationId: 'sylius_mollie_api_shop_order_mollie_methods',
                tags: ['Mollie'],
                responses: [
                    Response::HTTP_OK => new ResponseModel(
                        description: 'List of available Mollie payment methods',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => ['$ref' => '#/components/schemas/MollieMethod'],
                                ],
                            ],
                        ]),
                    ),
                ],
                summary: 'Get available Mollie payment methods for an order',
                parameters: [$tokenParameter],
            ),
            post: new Operation(
                operationId: 'sylius_mollie_api_shop_order_mollie_select_method',
                tags: ['Mollie'],
                responses: [
                    Response::HTTP_OK => new ResponseModel(
                        description: 'Mollie payment created successfully',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/MollieSelectMethodResponse'],
                            ],
                        ]),
                    ),
                ],
                summary: 'Select a Mollie payment method and create a payment',
                parameters: [$tokenParameter],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/MollieSelectMethodRequest'],
                        ],
                    ]),
                    required: true,
                ),
            ),
        ));

        $statusPath = sprintf('%s/orders/{tokenValue}/mollie-status', $this->shopApiRoute);
        $paths->addPath($statusPath, new PathItem(
            ref: 'MolliePaymentStatus',
            patch: new Operation(
                operationId: 'sylius_mollie_api_shop_order_mollie_payment_status',
                tags: ['Mollie'],
                responses: [
                    Response::HTTP_OK => new ResponseModel(
                        description: 'Current payment status',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/MolliePaymentStatus'],
                            ],
                        ]),
                    ),
                ],
                summary: 'Get Mollie payment status and update Sylius payment state',
                parameters: [$tokenParameter],
            ),
        ));
    }

    /**
     * @param array<string, mixed>|\ArrayObject<string, mixed> $schemas
     *
     * @return array<string, mixed>|\ArrayObject<string, mixed>
     */
    private function addSchemas(array|\ArrayObject $schemas): array|\ArrayObject
    {
        $schemas['MollieMethod'] = [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'string', 'example' => 'klarna'],
                'label' => ['type' => 'string', 'example' => 'Klarna'],
                'image' => ['type' => 'string', 'nullable' => true, 'example' => 'https://www.mollie.com/external/icons/payment-methods/klarna.svg'],
                'paymentFee' => [
                    'type' => 'object',
                    'nullable' => true,
                    'properties' => [
                        'type' => ['type' => 'string', 'nullable' => true],
                        'fixedAmount' => ['type' => 'number', 'nullable' => true],
                        'percentage' => ['type' => 'number', 'nullable' => true],
                        'surchargeLimit' => ['type' => 'number', 'nullable' => true],
                    ],
                ],
            ],
        ];

        $schemas['MollieSelectMethodRequest'] = [
            'type' => 'object',
            'required' => ['methodId', 'backUrl'],
            'properties' => [
                'methodId' => ['type' => 'string', 'example' => 'ideal'],
                'backUrl' => ['type' => 'string', 'example' => 'https://example.com/return-here-after-payment'],
            ],
        ];

        $schemas['MollieSelectMethodResponse'] = [
            'type' => 'object',
            'properties' => [
                'methodId' => ['type' => 'string'],
                'checkoutUrl' => ['type' => 'string'],
            ],
        ];

        $schemas['MolliePaymentStatus'] = [
            'type' => 'object',
            'properties' => [
                'paymentState' => ['type' => 'string'],
            ],
        ];

        return $schemas;
    }
}
