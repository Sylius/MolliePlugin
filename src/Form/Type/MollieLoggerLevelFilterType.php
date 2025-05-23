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
use Symfony\Component\Form\FormBuilderInterface;

final class MollieLoggerLevelFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('loggerLevel', LoggerLevelChoiceType::class, [
                'label' => false,
                'log_type' => LoggerLevelChoiceType::TYPE_LOG,
                'placeholder' => 'sylius.ui.all',
            ])
        ;
    }
}
