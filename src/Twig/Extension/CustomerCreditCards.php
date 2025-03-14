<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\MolliePlugin\Twig\Extension;

use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieCustomerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CustomerCreditCards extends AbstractExtension
{
    public function __construct(
        private MollieApiClient $apiClient,
        private EntityRepository $customerRepository,
        private CustomerContextInterface $customerContext
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isCardSaved', [$this, 'isCardSaved']),
            new TwigFunction('getCustomerFromContext', [$this, 'getCustomerFromContext'])
        ];
    }

    public function isCardSaved(string $customerEmail): bool
    {
        /** @var MollieCustomerInterface|null $existingCustomer */
        $existingCustomer = $this->customerRepository->findOneBy(['email' => $customerEmail]);

        return $existingCustomer !== null && $existingCustomer->isCreditCardSaved() === '1';
    }

    public function getCustomerFromContext(): ?CustomerInterface
    {
        return $this->customerContext->getCustomer();
    }
}
