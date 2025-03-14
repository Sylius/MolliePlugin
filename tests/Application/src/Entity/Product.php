<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Entity;

use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Entity\ProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

class Product extends BaseProduct implements ProductInterface
{
    use ProductTrait;
}
