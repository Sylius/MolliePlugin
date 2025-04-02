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

namespace Sylius\MolliePlugin\Model\DTO;

use Mollie\Api\Resources\MethodCollection;

final class ApiKeyTest
{
    public function __construct(private string $type, private bool $key = false, private ?\Mollie\Api\Resources\MethodCollection $methods = null, private string $status = 'OK', private string $message = '')
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getKey(): bool
    {
        return $this->key;
    }

    public function getMethods(): ?MethodCollection
    {
        return $this->methods;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setKey(bool $key): void
    {
        $this->key = $key;
    }

    public function setMethods(?MethodCollection $methods): void
    {
        $this->methods = $methods;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
