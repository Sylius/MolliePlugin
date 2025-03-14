<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ProductTypeInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getMollieGatewayConfig(): ?MollieGatewayConfigInterface;

    public function setMollieGatewayConfig(?MollieGatewayConfigInterface $mollieGatewayConfig): void;
}
