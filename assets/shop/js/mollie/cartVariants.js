import { model } from './cartTableRecurringVariants';

document.addEventListener('DOMContentLoaded', () => {
    const cartVariantDetails = document.getElementById('cart-variant-details');

    if (!cartVariantDetails) {
        return;
    }

    const cartItemsTableRows = document.querySelectorAll('#sylius-cart-items tr');

    Array.from(cartVariantDetails.querySelectorAll('div[data-recurring]')).forEach((variantDetailsElement) => {
        const index = parseInt(variantDetailsElement.getAttribute('data-index'), 10);
        const recurring = parseInt(variantDetailsElement.getAttribute('data-recurring'), 10);
        const interval = variantDetailsElement.getAttribute('data-interval');
        const times = variantDetailsElement.getAttribute('data-times');

        if (recurring === 1) {
            const row = cartItemsTableRows[index + 1];
            const cells = row.querySelectorAll('td');
            const item = cells[0];
            const total = cells[4];

            model.addRecurringDetailsLabels(item, total, interval, times);
        }
    });
});
