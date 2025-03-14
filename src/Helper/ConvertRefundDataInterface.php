<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Helper;

interface ConvertRefundDataInterface
{
    public function convert(array $data, string $currency): array;
}
