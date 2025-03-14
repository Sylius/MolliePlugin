<?php

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Entity;

use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Tests\Sylius\MolliePlugin\Application\src\Entity\RecurringProductVariantTrait;

class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use RecurringProductVariantTrait;
}
