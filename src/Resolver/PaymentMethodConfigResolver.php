<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class PaymentMethodConfigResolver implements PaymentMethodConfigResolverInterface
{
    /** @var RepositoryInterface */
    private $mollieMethodRepository;

    public function __construct(RepositoryInterface $mollieMethodRepository)
    {
        $this->mollieMethodRepository = $mollieMethodRepository;
    }

    public function getConfigFromMethodId(string $methodId): MollieGatewayConfigInterface
    {
        /** @var ?MollieGatewayConfigInterface $paymentMethod */
        $paymentMethod = $this->mollieMethodRepository->findOneBy(['methodId' => $methodId]);

        Assert::notNull($paymentMethod, sprintf('Cannot find payment method config with method id %s', $methodId));

        return $paymentMethod;
    }
}
