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

namespace Tests\Sylius\MolliePlugin\Entity;

use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\MolliePlugin\Entity\RecurringProductVariantTrait;

class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use RecurringProductVariantTrait;
}
