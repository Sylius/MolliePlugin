export const paymentTypeIndicator = (item, expectedValue) => {
    const indicatedItem = document.querySelector(item);
    const indicatedItemValue = indicatedItem?.value;

    return indicatedItemValue === expectedValue;
};

export const methodLoadIndicator = (item, messageContainer) => {
    const indicatedItem = document.querySelector(item);
    const messageWindow = document.querySelector(messageContainer);

    if (indicatedItem) {
        messageWindow.classList.add('step-next-disabled');
    } else {
        messageWindow.classList.remove('step-next-disabled');
    }
};

export const validateFields = (elements, messageContainer) => {
    const errors = [];

    elements.forEach((item) => !item.value && errors.push(item));
    if (errors.every((el) => el === null)) {
        messageContainer.classList.remove('step-next-disabled');
    } else {
        messageContainer.classList.add('step-next-disabled');
    }
};

export const currentStepValidator = (element, popup) => {
    const validationContainer = document.querySelector(element);
    const validationElements = validationContainer.parentNode
        .querySelectorAll(`input:not([type="file"]):not([type="submit"]):not(disabled):not([style*="display: none"]),
		select:not(disabled):not([style*="display: none;"])`);
    const messageWindow = document.querySelector(popup);

    if (validationElements && validationElements.length != 0) {
        validateFields(validationElements, messageWindow);
        validationElements.forEach((el) => {
            el.addEventListener('input', () => {
                validateFields(validationElements, messageWindow);
            });
        });
    }
};

export const updateTourCompletition = async () => {
    const url = '/admin/onboarding-wizard/completed';

    try {
        const response = await fetch(url, {
            method: 'POST',
        });
        const complete = await response.json();
    } catch (error) {
        console.error(error);
    }
};

export const getStatusInfo = () => {
    const status = document.querySelector('#mollie-payment-form').dataset.status;
    if (status === '') {
        return true;
    }
};

export const saveStep = (stepId) => {
    window.localStorage.setItem('step', `${stepId}`);
};

export const clearStorage = (key) => {
    window.localStorage.removeItem(`${key}`);
};

export const handleSubmit = () => {
    const nameInput = document.querySelector('.content.active [id*="sylius_payment_method_translations_"]');
    const keyInput = document.querySelector('#sylius_payment_method_code');
    const submitBtn = document.querySelector('button[type="submit"]');
    const randomId = Math.floor(Math.random() * 10000);

    submitBtn.addEventListener('click', (event) => {
        if (nameInput && nameInput.value === '') {
            nameInput.value = 'mollie';
        }
        if (keyInput && keyInput.value === '') {
            keyInput.value = 'mollie' + randomId;
        }
    });
};

export const checkForExistance = (highlightClass) => document.querySelector(highlightClass) != undefined;

export const expandDisabled = (child, parent, target) => {
    const element = document.querySelector(child);
    const container = element.closest(parent);
    const eventTarget = container.querySelector(target);

    if (!eventTarget.classList.contains('active')) {
        eventTarget.click();
    }
};
