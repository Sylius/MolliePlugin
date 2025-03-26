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

namespace Sylius\MolliePlugin\Form\Type;

use Sylius\MolliePlugin\Resolver\MolliePaymentsMethodResolverInterface;
use Sylius\MolliePlugin\Validator\Constraints\PaymentMethodCheckout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMollieType extends AbstractType
{
    public function __construct(private readonly MolliePaymentsMethodResolverInterface $methodResolver)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $methods = $this->methodResolver->resolve();

        $data = $methods['data'];
        $images = $methods['image'];
        $paymentFee = $methods['paymentFee'];

        $builder
            ->add('molliePaymentMethods', ChoiceType::class, [
                'constraints' => [
                    new PaymentMethodCheckout([
                        'groups' => ['sylius'],
                    ]),
                ],
                'label' => false,
                'choices' => $data,
                'choice_attr' => fn ($value): array => [
                    'image' => $images[$value],
                    'paymentFee' => $paymentFee[$value],
                ],
            ])
            ->add('cartToken', HiddenType::class)
            ->add('saveCardInfo', HiddenType::class)
            ->add('useSavedCards', HiddenType::class);
    }
}
