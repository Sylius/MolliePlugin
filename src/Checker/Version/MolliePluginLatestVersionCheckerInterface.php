<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Checker\Version;

interface MolliePluginLatestVersionCheckerInterface
{
    public function checkLatestVersion(): ?string;
}
