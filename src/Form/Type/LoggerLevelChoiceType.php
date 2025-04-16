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

use Sylius\MolliePlugin\Logger\LoggerLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LoggerLevelChoiceType extends AbstractType
{
    public const TYPE_DEBUG = 'debug';

    public const TYPE_LOG = 'log';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'sylius_mollie.ui.debug_level_log',
            'log_type' => self::TYPE_DEBUG,
            'choices' => self::getDebugChoices(),
        ]);

        $resolver->setAllowedValues('log_type', [self::TYPE_DEBUG, self::TYPE_LOG]);

        $resolver->addNormalizer('choices', static function ($options, $choices) {
            if ($options['log_type'] === self::TYPE_LOG) {
                return self::getLogChoices();
            }

            return $choices;
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /** @return array<string, int> */
    private static function getLogChoices(): array
    {
        return [
            'sylius_mollie.ui.info' => LoggerLevel::LOG_ERRORS,
            'sylius_mollie.ui.errors' => LoggerLevel::LOG_EVERYTHING,
        ];
    }

    /** @return array<string, int> */
    private static function getDebugChoices(): array
    {
        return [
            'sylius_mollie.ui.nothing_log' => LoggerLevel::LOG_DISABLED,
            'sylius_mollie.ui.errors' => LoggerLevel::LOG_ERRORS,
            'sylius_mollie.ui.everything' => LoggerLevel::LOG_EVERYTHING,
        ];
    }
}
