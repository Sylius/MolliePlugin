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

namespace Tests\SyliusMolliePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use SyliusMolliePlugin\Entity\AbandonedEmailOrderTrait;
use SyliusMolliePlugin\Entity\MolliePaymentIdOrderTrait;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Entity\QRCodeOrderTrait;
use SyliusMolliePlugin\Entity\RecurringOrderTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderInterface
{
    use AbandonedEmailOrderTrait;
    use RecurringOrderTrait;
    use QRCodeOrderTrait;
    use MolliePaymentIdOrderTrait;
}
