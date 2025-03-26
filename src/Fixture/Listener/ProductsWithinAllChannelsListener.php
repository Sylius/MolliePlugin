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

namespace Sylius\MolliePlugin\Fixture\Listener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Listener\AbstractListener;
use Sylius\Bundle\FixturesBundle\Listener\AfterSuiteListenerInterface;
use Sylius\Bundle\FixturesBundle\Listener\SuiteEvent;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class ProductsWithinAllChannelsListener extends AbstractListener implements AfterSuiteListenerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private ProductRepositoryInterface $productRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function afterSuite(SuiteEvent $suiteEvent, array $options): void
    {
        /** @var Collection<ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        /** @var Collection<ProductInterface> $products */
        $products = $this->productRepository->findAll();
        foreach ($products as $product) {
            foreach ($channels as $channel) {
                $product->addChannel($channel);
            }
        }

        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'products_within_all_channels';
    }
}
