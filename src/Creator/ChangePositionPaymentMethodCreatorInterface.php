<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

use Symfony\Component\HttpFoundation\Request;

interface ChangePositionPaymentMethodCreatorInterface
{
    public function createFromRequest(Request $request): void;
}
