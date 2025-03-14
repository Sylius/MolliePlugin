<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\OrderInterface;

interface MollieApiClientKeyResolverInterface
{
    public function getClientWithKey(OrderInterface $order = null): MollieApiClient;
}
