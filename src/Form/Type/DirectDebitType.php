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
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consumerName', TextType::class, [
                'label' => 'sylius_mollie.ui.consumer_name',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie.consumer_name.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
                'data' => $this->requestStack->getSession()->get('mollie_direct_debit_data')['consumerName'] ?? null,
            ])
            ->add('iban', TextType::class, [
                'label' => 'sylius_mollie.ui.iban',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie.iban.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new Iban([
                        'message' => 'sylius_mollie.iban.incorrect',
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
