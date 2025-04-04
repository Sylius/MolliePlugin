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

namespace SyliusMolliePlugin\Purifier;

use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Resolver\MollieMethodsResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class MolliePaymentMethodPurifier implements MolliePaymentMethodPurifierInterface
{
    /** @var RepositoryInterface */
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function removeAllNoLongerSupportedMethods(): void
    {
        foreach (MollieMethodsResolverInterface::UNSUPPORTED_METHODS as $methodId) {
            $this->removeMethod($methodId);
        }
    }

    public function removeMethod(string $methodId): void
    {
        $methodConfig = $this->repository->findOneBy(['methodId' => $methodId]);

        if ($methodConfig instanceof MollieGatewayConfigInterface) {
            /** @phpstan-ignore-next-line Ecs yields about doc comment in wrong place */
            $this->repository->remove($methodConfig);
        }
    }
}
