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

namespace Tests\Sylius\MolliePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;

final class ProductContext extends RawMinkContext implements Context
{
    private SharedStorageInterface $sharedStorage;

    private ProductRepository $productRepository;

    private ProductVariantRepository $productVariantRepository;

    private RepositoryInterface $channelPricingRepository;

    private DivisorProviderInterface $divisorProvider;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        ProductRepository $productRepository,
        ProductVariantRepository $productVariantRepository,
        RepositoryInterface $channelPricingRepository,
        DivisorProviderInterface $divisorProvider,
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->divisorProvider = $divisorProvider;
    }

    /**
     * @Given the :productName variant has recurring payment enabled
     */
    public function theVariantHasRecurringPaymentEnabled(string $productName): void
    {
        /** @var ProductInterface $product */
        $product = $this->productRepository->findOneBy([
            'code' => StringInflector::nameToUppercaseCode($productName),
        ]);
        $productVariants = $product->getVariants();

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $productVariants->first();
        $productVariant->setRecurring(true);
        $productVariant->setTimes(2);
        $productVariant->setInterval('10 days');

        $this->saveProductVariant($productVariant);
    }

    /**
     * @Then I change :productVariantCode product variant price to :price
     */
    public function iChangeProductVariantPriceTo(string $productVariantCode, string $price): void
    {
        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy([
            'code' => $productVariantCode,
        ]);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $this->channelPricingRepository->findOneBy([
            'productVariant' => $productVariant->getId(),
        ]);

        $channelPricing->setPrice((int) $price * $this->divisorProvider->getDivisor());

        $this->channelPricingRepository->add($channelPricing);
    }

    private function saveProductVariant(ProductVariantInterface $product): void
    {
        $this->productVariantRepository->add($product);
        $this->sharedStorage->set('product_variant', $product);
    }
}
