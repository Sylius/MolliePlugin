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

namespace Sylius\MolliePlugin\Controller\Shop;

use Mollie\Api\Exceptions\ApiException;
use Sylius\MolliePlugin\ApplePay\Checker\ApplePayEnabledCheckerInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieApiClientKeyResolverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApplePayValidationAction
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly MollieApiClientKeyResolverInterface $apiClientKeyResolver,
        private readonly ApplePayEnabledCheckerInterface $applePayEnabledChecker,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (false === $this->applePayEnabledChecker->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $validateUrl = $request->get('validationUrl');

        if (null === $validateUrl) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $url = $request->getHost();
        /** @var string[] $domain */
        $domain = parse_url($url);

        try {
            $mollieClient = $this->apiClientKeyResolver->getClientWithKey();

            $json = $mollieClient->wallets->requestApplePayPaymentSession($domain['path'], $validateUrl);
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(\sprintf('Error with validate apple pay with: %s', $e->getMessage()));

            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $response = [
            'success' => true,
            'data' => $json,
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
