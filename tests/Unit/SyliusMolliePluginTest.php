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

namespace Tests\Sylius\MolliePlugin\Unit;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\SyliusMolliePlugin;

final class SyliusMolliePluginTest extends TestCase
{
    public function testGetVersionReturnsInstalledVersion(): void
    {
        $version = SyliusMolliePlugin::getVersion();

        $this->assertNotSame('unknown', $version);
        $this->assertNotEmpty($version);
    }

    public function testGetVersionReturnsString(): void
    {
        $this->assertIsString(SyliusMolliePlugin::getVersion());
    }
}
