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

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Form\Type\PaymentMollieType;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryChecker;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Valid;

final class PaymentTypeExtension extends AbstractTypeExtension
{
    private readonly MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker;

    public function __construct(
        ?MollieGatewayFactoryCheckerInterface $mollieGatewayFactoryChecker = null,
    ) {
        if (null === $mollieGatewayFactoryChecker) {
            trigger_deprecation(
                'sylius/mollie-plugin',
                '3.3.4',
                'Not passing a "%s" to "%s" is deprecated and will be required in 4.0.',
                MollieGatewayFactoryCheckerInterface::class,
                self::class,
            );
        }

        $this->mollieGatewayFactoryChecker = $mollieGatewayFactoryChecker ?? new MollieGatewayFactoryChecker();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('details', PaymentMollieType::class, [
                'validation_groups' => ['sylius'],
                'constraints' => [
                    new Valid(),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $payment = $event->getData();

            if (!$payment instanceof PaymentInterface || $this->isMolliePayment($payment)) {
                return;
            }

            $details = array_diff_key($payment->getDetails(), array_flip(PaymentMollieType::FIELDS));

            $payment->setDetails($details);
        });
    }

    public static function getExtendedTypes(): array
    {
        return [PaymentType::class];
    }

    private function isMolliePayment(PaymentInterface $payment): bool
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $payment->getMethod()?->getGatewayConfig();

        return null !== $gatewayConfig && $this->mollieGatewayFactoryChecker->isMollieGateway($gatewayConfig);
    }
}
