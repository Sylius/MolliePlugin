<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Form\Extension;

use Sylius\MolliePlugin\Form\Extension\CompleteTypeExtension;
use Sylius\MolliePlugin\Form\Type\DirectDebitType;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class CompleteTypeExtensionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(CompleteTypeExtension::class);
        $this->shouldHaveType(AbstractTypeExtension::class);
    }

    function it_builds_form(
        FormBuilderInterface $builder,
        OrderInterface $order,
        PaymentInterface $payment,
        PaymentMethodInterface $method,
        GatewayConfigInterface $config
    ): void {
        $builder->getData()->willReturn($order);

        $order->getLastPayment()->willReturn($payment);

        $payment->getMethod()->willReturn($method);
        $payment->getDetails()->willReturn([
            'molliePaymentMethods' => 'directdebit'
        ]);

        $method->getGatewayConfig()->willReturn($config);
        $config->getFactoryName()->willReturn('mollie_subscription');

        $this->buildForm($builder, []);

        $builder
            ->add('directDebit', DirectDebitType::class, [
                'mapped' => false,
                'validation_groups' => ['sylius'],
                'constraints' => [
                    new Valid(),
                ],
            ])->shouldBeCalledOnce();
    }
}
