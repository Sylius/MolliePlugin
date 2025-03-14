<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Context\Admin;

use Sylius\Component\Core\Model\AdminUserInterface;

interface AdminUserContextInterface
{
    public function getAdminUser(): ?AdminUserInterface;
}
