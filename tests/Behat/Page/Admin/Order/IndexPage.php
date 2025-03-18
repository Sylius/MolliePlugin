<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Admin\Order;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Order\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function allOrdersHaveSameTotal(string $total): bool
    {
        $correctCells = 0;
        $ordersCount = $this->countOrders();
        $tableCells = $this->getDocument()->findAll('css', 'td');

        /** @var NodeElement $tableCell */
        foreach ($tableCells as $tableCell) {
            if ($total === $tableCell->getText()) {
                ++$correctCells;
            }
        }

        if ($ordersCount === $correctCells) {
            return true;
        }

        return false;
    }

    private function countOrders(): int
    {
        $table = $this->getElement('customer_orders');

        return count($table->findAll('css', 'tbody > tr'));
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'customer_orders' => '[data-test-grid-table]',
        ]);
    }
}
