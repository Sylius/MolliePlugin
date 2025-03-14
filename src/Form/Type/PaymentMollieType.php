<?php


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
    /** @var MolliePaymentsMethodResolverInterface */
    private $methodResolver;

    public function __construct(
        MolliePaymentsMethodResolverInterface $methodResolver
    )
    {
        $this->methodResolver = $methodResolver;
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
                'choice_attr' => function ($value) use ($images, $paymentFee): array {
                    return [
                        'image' => $images[$value],
                        'paymentFee' => $paymentFee[$value],
                    ];
                },
            ])
            ->add('cartToken', HiddenType::class)
            ->add('saveCardInfo', HiddenType::class)
            ->add('useSavedCards', HiddenType::class);
    }
}
