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

namespace Tests\Sylius\MolliePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareInterface;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareTrait;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_admin_user')]
class AdminUser extends BaseAdminUser implements PasswordHasherAwareInterface, OnboardingStatusAwareInterface
{
    use OnboardingStatusAwareTrait;

    public function getPasswordHasherName(): ?string
    {
        return $this->encoderName;
    }
}
