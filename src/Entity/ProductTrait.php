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

namespace Sylius\MolliePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @mixin ProductInterface */
trait ProductTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Sylius\MolliePlugin\Entity\ProductType")
     *
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id", onDelete="SET NULL")
     */
    #[ORM\ManyToOne(targetEntity: ProductType::class)]
    #[ORM\JoinColumn(name: 'product_type_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
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
