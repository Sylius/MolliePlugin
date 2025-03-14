<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Admin\Order;

use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Page\Admin\Order\ShowPage as BaseShowPage;

final class ShowPage extends BaseShowPage implements ShowPageInterface
{
    public function openLastOrderPage(): void
    {
        /** @var NodeElement $showElement */
        $showElement = $this->getElement('last_order')->find('css', '[data-test-button="sylius.ui.show"]');
        Assert::assertNotNull($showElement);

        $showElement->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'last_order' => '[data-test-grid-table-body] [data-test-row]:last-child [data-test-actions]',
        ]);
    }
}
