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

namespace Sylius\MolliePlugin\Client\Parser;

use Mollie\Api\Exceptions\ApiException;
use Webmozart\Assert\Assert;

final class ApiExceptionParser implements ApiExceptionParserInterface
{
    public function parse(ApiException $exception): string
    {
        if ($exception->hasResponse()) {
            $response = $exception->getResponse();

            Assert::notNull($response);
            $responseBodyAsString = json_decode((string) $response->getBody(), true);
            if (isset($responseBodyAsString['extra']['failureReason'])) {
                return $responseBodyAsString['extra']['failureReason'];
            }
        }

        return '';
    }
}
