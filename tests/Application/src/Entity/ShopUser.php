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

use Sylius\Component\Core\Model\ShopUser as BaseShopUser;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;

/**
 * @method string getUserIdentifier()
 */
class ShopUser extends BaseShopUser implements PasswordHasherAwareInterface
{
    public function getPasswordHasherName(): ?string
    {
        return $this->encoderName;
    }

}
