<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Core\Model\ProductInterface as BaseProductInterface;

interface ProductInterface extends BaseProductInterface
{
    public function getProductType(): ?ProductTypeInterface;

    public function setProductType(?ProductTypeInterface $productType): void;
}
