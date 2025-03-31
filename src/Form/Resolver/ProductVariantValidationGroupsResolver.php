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

namespace Sylius\MolliePlugin\Form\Resolver;

use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Symfony\Component\Form\FormInterface;

final class ProductVariantValidationGroupsResolver implements ValidationGroupsResolverInterface
{
    public function resolve(FormInterface $form): array
    {
        $groups = ['sylius'];
        $data = $form->getData();

        if (false === $data instanceof ProductVariantInterface) {
            return $groups;
        }

        if (false === $data->isRecurring()) {
            $groups[] = 'non_recurring_product_variant';

            return $groups;
        }

        $groups[] = 'recurring_product_variant';

        return $groups;
    }
}
