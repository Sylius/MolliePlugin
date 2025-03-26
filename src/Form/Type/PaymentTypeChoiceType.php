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

use Sylius\MolliePlugin\Documentation\DocumentationLinksInterface;
use Sylius\MolliePlugin\Payments\PaymentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly DocumentationLinksInterface $documentationLinks,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'sylius_mollie_plugin.ui.payment_type',
            'choices' => PaymentType::getAllAvailable(),
            'help' => $this->documentationLinks->getPaymentMethodDoc(),
            'help_html' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
