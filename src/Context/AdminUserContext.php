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

namespace Sylius\MolliePlugin\Context;

use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AdminUserContext implements AdminUserContextInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getAdminUser(): ?AdminUserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if ($token->getUser() instanceof AdminUserInterface) {
            return $token->getUser();
        }

        return null;
    }
}
