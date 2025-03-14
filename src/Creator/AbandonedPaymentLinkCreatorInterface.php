<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

interface AbandonedPaymentLinkCreatorInterface
{
    public function create(): void;
}
