<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Request\Api\CreateCustomer;

interface ApiCustomerFactoryInterface
{
    public function createNew(array $details): CreateCustomer;
}
