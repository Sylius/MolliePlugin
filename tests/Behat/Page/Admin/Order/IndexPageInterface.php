<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Admin\Order\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function allOrdersHaveSameTotal(string $total): bool;
}
