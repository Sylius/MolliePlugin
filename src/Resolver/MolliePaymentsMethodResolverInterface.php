<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

interface MolliePaymentsMethodResolverInterface
{
    public function resolve(): array;
}
