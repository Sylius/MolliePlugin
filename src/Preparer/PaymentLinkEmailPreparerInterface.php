<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Preparer;

use Sylius\Component\Core\Model\OrderInterface;

interface PaymentLinkEmailPreparerInterface
{
    public function prepare(OrderInterface $order, string $templateName): void;
}
