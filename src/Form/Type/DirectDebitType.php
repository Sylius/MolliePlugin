<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

final class DirectDebitType extends AbstractType
{
    /** @var RequestStack */
    private $requestStack;

    /** @param RequestStack $requestStack */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consumerName', TextType::class, [
                'label' => 'sylius_mollie_plugin.ui.consumer_name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie_plugin.consumer_name.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
                'data' => $this->requestStack->getSession()->get('mollie_direct_debit_data')['consumerName'] ?? null,
            ])
            ->add('iban', TextType::class, [
                'label' => 'sylius_mollie_plugin.ui.iban',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie_plugin.iban.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new Iban([
                        'message' => 'sylius_mollie_plugin.iban.incorrect',
                        'groups' => ['sylius'],
                    ]),
                ],
                'data' => $this->requestStack->getSession()->get('mollie_direct_debit_data')['iban'] ?? null,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();

                $this->requestStack->getSession()->set('mollie_direct_debit_data', $data);
            })
        ;
    }
}
