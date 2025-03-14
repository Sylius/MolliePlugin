<?php


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
