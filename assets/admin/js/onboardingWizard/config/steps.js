import {
    paymentTypeIndicator,
    methodLoadIndicator,
    currentStepValidator,
    updateTourCompletion,
    getStatusInfo,
    saveStep,
    clearStorage,
    checkForExistence,
    expandDisabled
} from '../helpers';

const paymentMethodPaymentApi = 'PAYMENT_API';
const paymentMethodOrderApi = 'ORDER_API';
const environmentTest = '0';
const environmentLive = '1';
const windowElementSelector = '.shepherd-content';

export const stepQuitConfirmation = [
    {
        id: 'step-quit-confirmation',
        title: 'stepQuitConfirmation.title',
        highlightClass: 'intro',
        customButtons: [
            {
                text: 'stepButtons.quitConfirm',
                action: (onboardingWizard) => {
                    onboardingWizard.tour.removeStep('step-quit-confirmation');
                    onboardingWizard.tour.complete();
                    clearStorage('step');
                },
                secondary: true,
            },
            {
                text: 'stepButtons.quitCancel',
                action: (onboardingWizard, stepIndex) => {
                    onboardingWizard.tour.show(stepIndex, true);
                    onboardingWizard.tour.removeStep('step-quit-confirmation');
                },
            },
        ],
    },
];

export const steps = [
    {
        id: 'step-start',
        title: 'stepStart.title',
        text: 'stepStart.text',
        classes: 'shepherd-element--first',
        highlightClass: 'intro',
        btnBackText: 'stepButtons.skipWizard',
        btnNextText: 'stepButtons.startWizard',
        btnCollapseClass: 'd-none',
        btnCloseClass: 'd-none',
    },
    {
        id: 'step-mollie-connect',
        title: 'stepMollieConnect.title',
        text: 'stepMollieConnect.text',
        highlightClass: 'intro',
        btnBackText: 'stepButtons.loginMollieAccount',
        btnNextText: 'stepButtons.createMollieAccount',
        btnCollapseClass: 'd-none',
        urlMollie: 'https://www.mollie.com/dashboard',
    },
    {
        showOn: function () {
            clearStorage('step');
            currentStepValidator('.js-onboardingWizard-environment', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-env',
        text: 'stepEnv.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'api-settings',
        attachTo: {
            element: '.js-onboardingWizard-environment',
            on: 'top-start',
        },
        btnNextClass: 'shepherd-button-next',
    },
    {
        showOn: function () {
            clearStorage('step');
            currentStepValidator('.js-two-fields-test .field', windowElementSelector);
            return paymentTypeIndicator('.js-onboardingWizard-environment', environmentTest);
        },
        id: 'step-api-key-test',
        text: 'stepApiKey.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'api-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.js-onboardingWizard-profile-api',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            clearStorage('step');
            currentStepValidator('.js-onboardingWizard-profile-api', windowElementSelector);
            return paymentTypeIndicator('.js-onboardingWizard-environment', environmentLive);
        },
        id: 'step-api-key-live',
        text: 'stepApiKey.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'api-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.js-onboardingWizard-profile-api',
            on: 'top-start',
        },
    },
    {
        id: 'step-checkout-config',
        text: 'stepCheckoutConfig.text',
        classes: 'step-6 shepherd-element--align-right',
        highlightClass: 'store-settings',
        btnNextText: 'stepButtons.next',
    },
    {
        showOn: function () {
            currentStepValidator('.js-onboardingWizard-mollieComponents', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-mollie-components',
        text: 'stepMollieComponents.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'store-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.js-onboardingWizard-mollieComponents',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.js-onboardingWizard-singleClick', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-mollie-payments',
        text: 'stepMolliePayments.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'store-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.js-onboardingWizard-singleClick',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            saveStep(this.id);
            return getStatusInfo();
        },
        id: 'create',
        text: 'stepCreate.text',
        classes: 'step-13 shepherd-element--align-right',
        highlightClass: 'store-settings',
        btnNextClass: 'd-none',
        attachTo: {
            element: '.page-header .btn-list',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            saveStep(this.id);
            currentStepValidator('.js-onboardingWizard-load-methods', windowElementSelector);
            methodLoadIndicator('.js-payment-method-not-loaded', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-payments-api',
        text: 'stepMethodConfig.text',
        classes: 'shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextText: 'stepButtons.next',
        attachTo: {
            element: '.js-onboardingWizard-load-methods',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            expandDisabled('.js-onboardingWizard-paymentName', '[data-payment-method]', '.accordion-button');
            currentStepValidator('.js-onboardingWizard-paymentName', windowElementSelector);

            return checkForExistence(this.attachTo.element);
        },
        id: 'step-payment-title',
        text: 'stepPaymentTitle.text',
        classes: 'step-9 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.js-onboardingWizard-paymentName',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.accordion-collapse.show .js-onboardingWizard-customizeMethodImage', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-image-upload',
        text: 'stepImageUpload.text',
        classes: 'step-14 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-customizeMethodImage input',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.accordion-collapse.show .js-onboardingWizard-countryRestriction', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-country-restriction',
        text: 'stepCountryRestriction.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-countryRestrictions',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.accordion-collapse.show .js-onboardingWizard-PaymentMethod', windowElementSelector);
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-payment-method',
        text: 'stepPaymentMethod.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-PaymentMethod',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            return paymentTypeIndicator(
                '.accordion-collapse.show .js-onboardingWizard-PaymentMethod',
                paymentMethodPaymentApi
            );
        },
        id: 'step-order-number',
        text: 'stepPaymentType.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-order-number',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            return paymentTypeIndicator(
                '.accordion-collapse.show .js-onboardingWizard-PaymentMethod',
                paymentMethodOrderApi
            );
        },
        id: 'step-order-api',
        text: 'stepOrdersAPI.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-PaymentMethod',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            return checkForExistence(this.attachTo.element);
        },
        id: 'step-fees',
        text: 'stepFees.text',
        classes: 'step-13 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.accordion-collapse.show .js-onboardingWizard-paymentFee',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            updateTourCompletion();
            clearStorage('step');
            return checkForExistence(this.attachTo.element);
        },
        id: 'save',
        text: 'stepSave.text',
        classes: 'step-13 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.page-header .btn-list',
            on: 'top-start',
        },
    },
    {
        id: 'step-finish-wizard',
        title: 'stepFinishWizard.title',
        text: 'stepFinishWizard.text',
        highlightClass: 'payment-settings',
        btnBackClass: 'd-none',
        btnNextClass: 'text-center',
        btnNextText: 'stepButtons.finishWizard',
        btnCollapseClass: 'd-none',
        btnCloseClass: 'd-none',
    },
];
