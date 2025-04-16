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

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface MollieLoggerInterface extends ResourceInterface
{
    public function getId(): int;

    public function getLevel(): int;

    public function setLevel(int $level): void;

    public function getErrorCode(): int;

    public function setErrorCode(int $errorCode): void;

    public function getMessage(): string;

    public function setMessage(string $message): void;

    public function getDateTime(): \DateTime;

    public function setDateTime(\DateTime $dateTime): void;
}
