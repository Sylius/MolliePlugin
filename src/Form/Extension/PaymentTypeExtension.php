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

namespace Sylius\MolliePlugin\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Sylius\MolliePlugin\Form\EventSubscriber\ScopeMollieDetailsToMollieGatewaySubscriber;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentTypeExtension extends AbstractTypeExtension
{
    private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker;

    public function __construct(
        ?MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker = null,
    ) {
        $this->mollieGatewayFactoryChecker = $mollieGatewayFactoryChecker ?? new MollieGatewayFactoryChecker();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subscriber = new ScopeMollieDetailsToMollieGatewaySubscriber($this->mollieGatewayFactoryChecker);

        $subscriber->addDetailsField($builder);

        $builder->addEventSubscriber($subscriber);
    }

    public static function getExtendedTypes(): array
    {
        return [PaymentType::class];
    }
}
