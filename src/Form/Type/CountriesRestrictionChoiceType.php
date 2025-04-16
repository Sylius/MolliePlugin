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

use Sylius\MolliePlugin\Model\CountriesRestriction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CountriesRestrictionChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'sylius_mollie.ui.country_level_restriction',
            'choices' => [
                'sylius_mollie.ui.all_countries' => CountriesRestriction::ALL,
                'sylius_mollie.ui.selected_countries' => CountriesRestriction::SELECTED,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
