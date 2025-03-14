<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\AbstractTranslation;

class MollieGatewayConfigTranslation extends AbstractTranslation implements MollieGatewayConfigTranslationInterface
{
    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
