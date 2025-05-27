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

namespace Sylius\MolliePlugin\Form\Transformer;

use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Symfony\Component\Form\DataTransformerInterface;

final class MollieIntervalTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        if (false === is_string($value)) {
            return [
                'amount' => null,
                'step' => null,
            ];
        }

        preg_match(
            sprintf(
                '/^(?<amount>\d{1,})\s(?<step>%s)$/',
                implode('|', MollieSubscriptionConfigurationInterface::SUPPORTED_INTERVAL_STEPS),
            ),
            $value,
            $matches,
        );

        return $matches;
    }

    public function reverseTransform($value): mixed
    {
        if (false === is_array($value)) {
            return null;
        }

        if (false === array_key_exists('amount', $value) || false === array_key_exists('step', $value)) {
            return null;
        }

        return sprintf('%d %s', $value['amount'], $value['step']);
    }
}
