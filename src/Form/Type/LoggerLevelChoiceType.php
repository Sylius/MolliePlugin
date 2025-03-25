<?php

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
            'label' => 'sylius_mollie_plugin.ui.debug_level_log',
            'log_type' => self::TYPE_DEBUG,
            'choices' => $this->getDebugChoices(),
        ]);

        $resolver->setAllowedValues('log_type', [self::TYPE_DEBUG, self::TYPE_LOG]);

        $resolver->addNormalizer('choices', static function ($options, $choices) {
            if ($options['log_type'] === self::TYPE_LOG) {
                return $this->getLogChoices();
            }

            return $choices;
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    private function getLogChoices(): array
    {
        return [
            'sylius_mollie_plugin.ui.info' => LoggerLevel::LOG_ERRORS,
            'sylius_mollie_plugin.ui.errors' => LoggerLevel::LOG_EVERYTHING,
        ];
    }

    private function getDebugChoices(): array
    {
        return [
            'sylius_mollie_plugin.ui.nothing_log' => LoggerLevel::LOG_DISABLED,
            'sylius_mollie_plugin.ui.errors' => LoggerLevel::LOG_ERRORS,
            'sylius_mollie_plugin.ui.everything' => LoggerLevel::LOG_EVERYTHING,
        ];
    }
}
