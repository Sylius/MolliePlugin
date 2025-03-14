<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Parser\Response;

use Mollie\Api\Exceptions\ApiException;
use Webmozart\Assert\Assert;

final class GuzzleNegativeResponseParser implements GuzzleNegativeResponseParserInterface
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
