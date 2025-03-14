<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;

interface StatusActionInterface extends ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
}
