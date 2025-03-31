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

use Symfony\Component\Form\FormInterface;

interface ValidationGroupsResolverInterface
{
    /** @return string[] */
    public function resolve(FormInterface $form): array;
}
