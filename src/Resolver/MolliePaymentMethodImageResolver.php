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

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

final class MolliePaymentMethodImageResolver implements MolliePaymentMethodImageResolverInterface
{
    /** @var string */
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function resolve(MollieGatewayConfigInterface $paymentMethod): string
    {
        if (null !== $paymentMethod->getCustomizeMethodImage() &&
            null !== $paymentMethod->getCustomizeMethodImage()->getPath()) {
            return sprintf('%s%s', $this->rootDir, $paymentMethod->getCustomizeMethodImage()->getPath());
        }

        return $paymentMethod->getImage()['svg'];
    }
}
