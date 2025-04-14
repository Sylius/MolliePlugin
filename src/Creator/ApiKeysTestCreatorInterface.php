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

namespace Sylius\MolliePlugin\Creator;

use Sylius\MolliePlugin\Model\DTO\ApiKeyTest;

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

    public function create(string $keyType, ?string $key = null): ApiKeyTest;
}
