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

namespace SyliusMolliePlugin\Provider\Apple;

use SyliusMolliePlugin\Entity\OrderInterface;
use Symfony\Component\HttpFoundation\Request;

interface ApplePayDirectProviderInterface
{
    public function provideOrder(OrderInterface $order, Request $request): void;
}
