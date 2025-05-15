import { model } from './cartTableRecurringVariants';

document.addEventListener('DOMContentLoaded', () => {
    const recurringContainer = document.querySelector('#sylius-product-name')?.closest('.ui.text.menu');
    const productPriceContainer = document.getElementById('product-price');

    const getMatchSelector = () => {
        let selector = '';
        document.querySelectorAll('#sylius-product-adding-to-cart select[data-option]').forEach(select => {
            const option = select.options[select.selectedIndex].value;
            selector += `[data-${select.getAttribute('data-option')}="${option}"]`;
        });
        return selector;
    };

    const isChoice = () => {
        return document.querySelectorAll('#sylius-product-adding-to-cart input[type="radio"][name="sylius_add_to_cart[cartItem][variant]"]').length > 0;
    };

    const getChoiceSelector = () => {
        const checkedRadio = document.querySelector('#sylius-product-adding-to-cart input[type="radio"][name="sylius_add_to_cart[cartItem][variant]"]:checked');
        return `[data-variant="${checkedRadio?.value}"]`;
    };

    const resolveSelector = () => {
        return isChoice() ? getChoiceSelector() : getMatchSelector();
    };

    const getTimes = () => {
        return document.querySelector(`#sylius-variants-recurring-times ${resolveSelector()}`)?.getAttribute('data-value');
    };

    const getInterval = () => {
        return document.querySelector(`#sylius-variants-recurring-interval ${resolveSelector()}`)?.getAttribute('data-value');
    };

    const checkRecurringMatch = () => {
        return document.querySelector(`#sylius-variants-recurring-match ${resolveSelector()}`)?.getAttribute('data-value') === '1';
    };

    const checkRecurringChoice = () => {
        return document.querySelector(`#sylius-variants-recurring-choice ${resolveSelector()}`)?.getAttribute('data-value') === '1';
    };

    const removeRecurringDetailsLabels = () => {
        model.clearLabels();
    };

    const addRecurringDetailsLabels = () => {
        if (!recurringContainer || !productPriceContainer) return;

        model.appendRecurringLabel(recurringContainer);
        model.appendTimesLabel(recurringContainer, getTimes());
        model.appendIntervalLabel(productPriceContainer, getInterval());
    };

    const updateProductRecurringLabel = () => {
        removeRecurringDetailsLabels();
        if (checkRecurringMatch() || checkRecurringChoice()) {
            addRecurringDetailsLabels();
        }
    };

    const form = document.querySelector('form[name="sylius_add_to_cart"]');
    if (form) {
        form.addEventListener('change', updateProductRecurringLabel);
    }

    updateProductRecurringLabel();
});
