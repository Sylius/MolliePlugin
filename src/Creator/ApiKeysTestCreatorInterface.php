<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

use Sylius\MolliePlugin\DTO\ApiKeyTest;

interface ApiKeysTestCreatorInterface
{
    /** @var string */
    public const ERROR_STATUS = 'ERROR';

    /** @var string */
    public const OK_STATUS = 'OK';

    /** @var string */
    public const TEST_PREFIX = 'test_';

    /** @var string */
    public const LIVE_PREFIX = 'live_';

    public function create(string $keyType, string $key = null): ApiKeyTest;
}
