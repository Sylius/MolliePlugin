document.addEventListener('DOMContentLoaded', function () {
    const hasMollieForm = document.getElementById('mollie-payment-form');
    if (!hasMollieForm) {
        return;
    }

    const checkAllElement = document.querySelector('[data-mollie-check-all]');
    if (!checkAllElement) {
        return;
    }

    const checkAllMessageElement = checkAllElement.querySelector('span');

    if (
        document.querySelectorAll('[data-mollie-method-enabled] input:checked').length ===
        document.querySelectorAll('[data-mollie-method-enabled] input').length
    ) {
        checkAllMessageElement.textContent = checkAllElement.dataset.uncheckAllMessage;
    }

    checkAllElement.addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('[data-mollie-method-enabled] input');
        const shouldCheck = checkAllElement.dataset.checkAllMessage === checkAllMessageElement.textContent;
        checkboxes.forEach((checkbox) => {
            checkbox.checked = shouldCheck;
        });
        if (shouldCheck) {
            checkAllMessageElement.textContent = checkAllElement.dataset.uncheckAllMessage;
        } else {
            checkAllMessageElement.textContent = checkAllElement.dataset.checkAllMessage;
        }
    });
});
