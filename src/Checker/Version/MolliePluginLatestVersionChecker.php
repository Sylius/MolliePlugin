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

namespace Sylius\MolliePlugin\Checker\Version;

use Sylius\MolliePlugin\SyliusMolliePlugin;

final class MolliePluginLatestVersionChecker implements MolliePluginLatestVersionCheckerInterface
{
    public function checkLatestVersion(): ?string
    {
        return SyliusMolliePlugin::VERSION;
    }
}
