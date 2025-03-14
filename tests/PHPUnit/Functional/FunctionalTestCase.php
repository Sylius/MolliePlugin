<?php

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\PHPUnit\Functional;

use ApiTestCase\JsonApiTestCase;

class FunctionalTestCase extends JsonApiTestCase
{
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->dataFixturesPath = __DIR__ . '/DataFixtures';
    }
}
