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

namespace Sylius\MolliePlugin\Twig\Extension;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\MolliePlugin\Entity\MollieCustomerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CustomerCreditCards extends AbstractExtension
{
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly CustomerContextInterface $customerContext,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isCardSaved', [$this, 'isCardSaved']),
            new TwigFunction('getCustomerFromContext', [$this, 'getCustomerFromContext']),
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
