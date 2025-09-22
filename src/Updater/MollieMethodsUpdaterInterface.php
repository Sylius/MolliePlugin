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

namespace Sylius\MolliePlugin\Updater;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;

interface MollieMethodsUpdaterInterface
{
    /** @throws MollieMethodsException */
    public function update(GatewayConfigInterface $gateway, bool $force = false): void;
}
