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

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Request\Api\CreateCustomer;

final class ApiCustomerFactory implements ApiCustomerFactoryInterface
{
    public function createNew(array $details): CreateCustomer
    {
        return new CreateCustomer($details);
    }
}
