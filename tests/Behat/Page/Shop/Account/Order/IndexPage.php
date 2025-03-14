<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Shop\Account\Order\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    /**
     * @inheritdoc
     */
    public function cancelSubscription(): void
    {
        $this->getDocument()->pressButton('Cancel subscription');
    }
}
