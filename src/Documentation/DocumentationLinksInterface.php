<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Documentation;

interface DocumentationLinksInterface
{
    public function getSingleClickDoc(): string;

    public function getMollieComponentsDoc(): string;

    public function getPaymentMethodDoc(): string;

    public function getApiKeyDoc(): string;
}
