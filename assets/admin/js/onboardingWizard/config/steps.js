import {
    paymentTypeIndicator,
    methodLoadIndicator,
    currentStepValidator,
    updateTourCompletition,
    getStatusInfo,
    saveStep,
    clearStorage,
    checkForExistance,
    expandDisabled
} from '../helpers';

const paymentMethodPaymentApi = 'PAYMENT_API';
const paymentMethodOrderApi = 'ORDER_API';
const enviromentTest = '0';
const enviromentLive = '1';

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
            currentStepValidator('.js-onboardingWizard-environment', '.pushable');
            return checkForExistance(this.attachTo.element);
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
            currentStepValidator('.js-two-fields-test .required.field', '.pushable');
            return paymentTypeIndicator('.js-onboardingWizard-environment', enviromentTest);
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
            currentStepValidator('.js-onboardingWizard-profile-api', '.pushable');
            return paymentTypeIndicator('.js-onboardingWizard-environment', enviromentLive);
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
            currentStepValidator('.js-onboardingWizard-mollieComponents', '.pushable');
            return checkForExistance(this.attachTo.element);
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
            currentStepValidator('.js-onboardingWizard-singleClick', '.pushable');
            return checkForExistance(this.attachTo.element);
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
            element: '.ui.buttons:not(.js-header-btn)',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            saveStep(this.id);
            currentStepValidator('.js-onboardingWizard-load-methods', '.pushable');
            methodLoadIndicator('.js-payment-method-not-loaded', '.pushable');
            return checkForExistance(this.attachTo.element);
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
            expandDisabled('.js-onboardingWizard-paymentName', '[data-payment-method]', '.title');
            currentStepValidator('.js-onboardingWizard-paymentName', '.pushable');
            
            return checkForExistance(this.attachTo.element);
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
            currentStepValidator('.content.active .js-onboardingWizard-customizeMethodImage', '.pushable');
            return checkForExistance(this.attachTo.element);
        },
        id: 'step-image-upload',
        text: 'stepImageUpload.text',
        classes: 'step-14 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-customizeMethodImage input',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.content.active .js-onboardingWizard-countryRestriction', '.pushable');
            return checkForExistance(this.attachTo.element);
        },
        id: 'step-country-restriction',
        text: 'stepCountryRestriction.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-countryRestrictions',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            currentStepValidator('.content.active .js-onboardingWizard-PaymentMethod', '.pushable');
            return checkForExistance(this.attachTo.element);
        },
        id: 'step-payment-method',
        text: 'stepPaymentMethod.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-PaymentMethod',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            return paymentTypeIndicator(
                '.content.active .js-onboardingWizard-PaymentMethod select',
                paymentMethodPaymentApi
            );
        },
        id: 'step-order-number',
        text: 'stepPaymentType.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-order-number',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            return paymentTypeIndicator(
                '.content.active .js-onboardingWizard-PaymentMethod select',
                paymentMethodOrderApi
            );
        },
        id: 'step-order-api',
        text: 'stepOrdersAPI.text',
        classes: 'step-12 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-PaymentMethod',
            on: 'top-start',
        },
    },

    {
        showOn: function () {
            return checkForExistance(this.attachTo.element);
        },
        id: 'step-fees',
        text: 'stepFees.text',
        classes: 'step-13 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.content.active .js-onboardingWizard-paymentFee',
            on: 'top-start',
        },
    },
    {
        showOn: function () {
            updateTourCompletition();
            clearStorage('step');
            return checkForExistance(this.attachTo.element);
        },
        id: 'save',
        text: 'stepSave.text',
        classes: 'step-13 shepherd-element--align-right',
        highlightClass: 'payment-settings',
        btnNextClass: 'shepherd-button-next',
        attachTo: {
            element: '.ui.buttons:not(.js-header-btn)',
            on: 'top-start',
        },
    },
    {
        id: 'step-finish-wizard',
        title: 'stepFinishWizard.title',
        text: 'stepFinishWizard.text',
        highlightClass: 'payment-settings',
        btnBackClass: 'd-none',
        btnNextClass: 'mr-auto',
        btnNextText: 'stepButtons.finishWizard',
        btnCollapseClass: 'd-none',
        btnCloseClass: 'd-none',
    },
];
