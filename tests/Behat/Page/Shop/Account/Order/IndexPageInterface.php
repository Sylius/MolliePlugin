<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Shop\Account\Order\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function cancelSubscription(): void;
}
