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

namespace SyliusMolliePlugin\Documentation;

interface DocumentationLinksInterface
{
    public function getSingleClickDoc(): string;

    public function getMollieComponentsDoc(): string;

    public function getPaymentMethodDoc(): string;

    public function getApiKeyDoc(): string;
}
