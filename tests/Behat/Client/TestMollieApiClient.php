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

namespace Tests\Sylius\MolliePlugin\Behat\Client;

use Sylius\MolliePlugin\Client\MollieApiClient;

class TestMollieApiClient extends MollieApiClient
{
    public function __construct($httpClient = null, $httpAdapterPicker = null)
    {
        parent::__construct($httpClient, $httpAdapterPicker);
        $this->apiEndpoint = 'http://localhost:8218';
    }
}
