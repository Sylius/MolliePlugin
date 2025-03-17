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

namespace Sylius\MolliePlugin\Twig\Parser;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ContentParser implements ContentParserInterface
{
    /** @param array<array-key, string> $enabledFunctions */
    public function __construct(
        private readonly Environment $twigEnvironment,
        private readonly array $enabledFunctions,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function parse(string $input, string $argument): string
    {
        preg_match_all('`{{\s*(?P<arguments>.+)\s*}}`', $input, $callMatches);

        foreach ($callMatches[0] as $index => $call) {
            $input = str_replace($call, $argument, $input);
        }

        return $input;
    }
}
