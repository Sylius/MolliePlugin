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

use Mollie\Api\Types\PaymentMethod;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\CompleteType;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\MolliePlugin\Form\Type\DirectDebitType;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class CompleteTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var OrderInterface $order */
        $order = $builder->getData();

        /** @var ?PaymentInterface $payment */
        $payment = $order->getLastPayment();
        /** @var PaymentMethodInterface|null $method */
        $method = null !== $payment ? $payment->getMethod() : null;
        $details = null !== $payment ? $payment->getDetails() : [];

        if (
            null !== $method &&
            null !== $method->getGatewayConfig() &&
            MollieSubscriptionGatewayFactory::FACTORY_NAME === $method->getGatewayConfig()->getFactoryName() &&
            true === array_key_exists('molliePaymentMethods', $details) &&
            PaymentMethod::DIRECTDEBIT == $details['molliePaymentMethods']
        ) {
            $builder
                ->add('directDebit', DirectDebitType::class, [
                    'mapped' => false,
                    'validation_groups' => ['sylius'],
                    'constraints' => [
                        new Valid(),
                    ],
                ])
            ;
        }
    }

    public static function getExtendedTypes(): array
    {
        return [CompleteType::class];
    }
}
