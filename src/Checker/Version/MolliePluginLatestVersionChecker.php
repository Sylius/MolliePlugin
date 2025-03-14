<?php

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
