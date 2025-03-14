<?php


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
