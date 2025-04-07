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

namespace Sylius\MolliePlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\MolliePlugin\Client\MollieApiClient;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    protected MollieApiClient $mollieApiClient;

    public function setApi($api): void
    {
        if (false === $api instanceof MollieApiClient) {
            throw new UnsupportedApiException('Not supported.Expected an instance of ' . MollieApiClient::class);
        }

        $this->mollieApiClient = $api;
    }
}
