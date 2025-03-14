<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface TemplateMollieEmailTranslationInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getSubject(): ?string;

    public function setSubject(?string $subject): void;

    public function getContent(): ?string;

    public function setContent(?string $content): void;
}
