<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Symfony\Component\HttpFoundation\Request;

interface ApiKeysTestResolverInterface
{
    public function fromRequest(Request $request): array;
}
