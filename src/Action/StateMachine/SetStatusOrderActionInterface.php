<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine;

use Mollie\Api\Resources\Order;

interface SetStatusOrderActionInterface
{
    public function execute(Order $order): void;
}
