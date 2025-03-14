<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Twig\Parser;

interface ContentParserInterface
{
    public function parse(string $input, string $argument): string;
}
