<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

trait ProductTrait
{
    /** @var ProductTypeInterface */
    protected ?ProductType $productType = null;

    public function getProductType(): ?ProductTypeInterface
    {
        return $this->productType;
    }

    public function setProductType(?ProductTypeInterface $productType): void
    {
        $this->productType = $productType;
    }
}
