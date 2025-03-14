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

return [
    BabDev\PagerfantaBundle\BabDevPagerfantaBundle::class => ['all' => true],
    SyliusLabs\Polyfill\Symfony\Security\Bundle\SyliusLabsPolyfillSymfonySecurityBundle::class => ['all' => true],
    Sylius\Calendar\SyliusCalendarBundle::class => ['all' => true],
];
