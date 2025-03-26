import {model} from './cartTableRecurringVariants';

$(function () {
    $(document).ready(() => {
        const $cartVariantDetails = $('#cart-variant-details');

        if (1 !== $cartVariantDetails.length) {
            return;
        }

        const $cartItemsTableRows = $('#sylius-order tr');
        $cartVariantDetails.children('div[data-recurring]').each(function () {
            const $variantDetailsElement = $(this);
            const $index = $variantDetailsElement.data('index');
            const $recurring = $variantDetailsElement.data('recurring');
            const $interval = $variantDetailsElement.data('interval');
            const $times = $variantDetailsElement.data('times');

            if (1 === $recurring) {
                let [item, unitPrice, quantity, total] = $($cartItemsTableRows[$index + 1]).children('td');

                model.addRecurringDetailsLabels($(item), $(total), $interval, $times);
            }
        });
    });
});
