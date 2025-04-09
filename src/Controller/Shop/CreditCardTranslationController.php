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

namespace Sylius\MolliePlugin\Controller\Shop;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CreditCardTranslationController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function fetchTranslations(Request $request): JsonResponse
    {
        $response = [
            'emptyCardHolder' => $this->translator->trans('sylius_mollie.ui.credit_card_validations.empty_card_holder'),
            'emptyCardNumber' => $this->translator->trans('sylius_mollie.ui.credit_card_validations.empty_card_number'),
            'emptyExpiryDate' => $this->translator->trans('sylius_mollie.ui.credit_card_validations.empty_expiry_date'),
            'emptyVerificationCode' => $this->translator->trans('sylius_mollie.ui.credit_card_validations.empty_verification_code'),
            'oneOrMoreInvalidFields' => $this->translator->trans('sylius_mollie.ui.credit_card_validations.one_or_more_invalid_fields'),
        ];

        return new JsonResponse(['status' => Response::HTTP_OK, 'translations' => $response]);
    }
}
