<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Provider\Order;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface OrderPaymentApplePayDirectProviderInterface
{
    public function provideOrderPayment(OrderInterface $order, string $targetState): ?PaymentInterface;
}
