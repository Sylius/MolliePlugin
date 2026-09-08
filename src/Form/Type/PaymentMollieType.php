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
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentMollieType extends AbstractType
{
    public const OPTION_MOLLIE_METHODS = 'mollie_methods';

    public const FIELD_PAYMENT_METHODS = 'molliePaymentMethods';

    public const FIELD_CART_TOKEN = 'cartToken';

    public const FIELD_SAVE_CARD_INFO = 'saveCardInfo';

    public const FIELD_USE_SAVED_CARDS = 'useSavedCards';

    public function __construct(private readonly MolliePaymentsMethodResolverInterface $methodResolver)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var array{
         *     data: array<string, string>,
         *     image: array<string, string>,
         *     issuers: array<string, mixed>|null,
         *     paymentFee: array<string, mixed>
         * } $methods
         */
        $methods = $options[self::OPTION_MOLLIE_METHODS] ?? $this->methodResolver->resolve();

        $builder->setAttribute(self::OPTION_MOLLIE_METHODS, $methods);

        $data = $methods['data'];
        $images = $methods['image'];
        $paymentFee = $methods['paymentFee'];

        $builder
            ->add(self::FIELD_PAYMENT_METHODS, ChoiceType::class, [
                'constraints' => [
                    new PaymentMethodCheckout([
                        'groups' => ['sylius'],
                    ]),
                ],
                'label' => false,
                'choices' => array_flip($data),
                'choice_attr' => fn ($value): array => [
                    'image' => $images[$value],
                    'paymentFee' => $paymentFee[$value],
                ],
            ])
            ->add(self::FIELD_CART_TOKEN, HiddenType::class)
            ->add(self::FIELD_SAVE_CARD_INFO, HiddenType::class)
            ->add(self::FIELD_USE_SAVED_CARDS, HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault(self::OPTION_MOLLIE_METHODS, null)
            ->setAllowedTypes(self::OPTION_MOLLIE_METHODS, ['array', 'null'])
        ;
    }
}
