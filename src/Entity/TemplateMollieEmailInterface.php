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
use Sylius\Component\Resource\Model\TranslatableInterface;

interface TemplateMollieEmailInterface extends ResourceInterface, TranslatableInterface
{
    public const PAYMENT_LINK = 'payment_link';

    public const PAYMENT_LINK_ABANDONED = 'payment_link_abandoned';

    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getSubject(): ?string;

    public function setSubject(?string $subject): void;

    public function getContent(): ?string;

    public function setContent(?string $content): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getStyleCss(): ?string;

    public function setStyleCss(?string $styleCss): void;
}
