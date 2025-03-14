<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Twig\Parser;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ContentParser implements ContentParserInterface
{
    /** @var Environment */
    private $twigEnvironment;

    /** @var array */
    private $enabledFunctions;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        Environment $twigEnvironment,
        array $enabledFunctions,
        TranslatorInterface $translator
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->enabledFunctions = $enabledFunctions;
        $this->translator = $translator;
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
