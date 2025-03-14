<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Repository;

use Sylius\MolliePlugin\Entity\TemplateMollieEmailTranslationInterface;

interface TemplateMollieEmailTranslationRepositoryInterface
{
    public function findOneByLocaleCodeAdnType(string $localeCode, string $type): ?TemplateMollieEmailTranslationInterface;
}
