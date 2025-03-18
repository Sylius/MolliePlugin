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

namespace SyliusMolliePlugin\Creator;

use Symfony\Component\HttpFoundation\Request;

interface ChangePositionPaymentMethodCreatorInterface
{
    public function createFromRequest(Request $request): void;
}
