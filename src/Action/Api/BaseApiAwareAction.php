<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\Api;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /** @var MollieApiClient */
    protected $mollieApiClient;

    public function setApi($mollieApiClient): void
    {
        if (false === $mollieApiClient instanceof MollieApiClient) {
            throw new UnsupportedApiException('Not supported.Expected an instance of ' . MollieApiClient::class);
        }

        $this->mollieApiClient = $mollieApiClient;
    }

    /**
     * Checks if payment should be refunded. As long as there are order items to be refunded, payment will be refunded.
     *
     * @param \ArrayObject $details
     *
     * @return bool
     */
    public function shouldBeRefunded(\ArrayObject $details): bool
    {
        if (isset($details['metadata']['refund']) && array_key_exists('items', $details['metadata']['refund'])) {
            $items = $details['metadata']['refund']['items'];

            return count($items) > 0 && !empty($items[0]);
        }

        return false;
    }
}
