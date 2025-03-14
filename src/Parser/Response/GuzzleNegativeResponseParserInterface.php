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

namespace Sylius\MolliePlugin\Parser\Response;

use Mollie\Api\Exceptions\ApiException;

interface GuzzleNegativeResponseParserInterface
{
    public function parse(ApiException $exception): string;
}
