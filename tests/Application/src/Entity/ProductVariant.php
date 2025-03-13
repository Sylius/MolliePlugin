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

namespace Tests\SyliusMolliePlugin\Entity;

use SyliusMolliePlugin\Entity\ProductVariantInterface;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Tests\SyliusMolliePlugin\Application\src\Entity\RecurringProductVariantTrait;

class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use RecurringProductVariantTrait;
}
