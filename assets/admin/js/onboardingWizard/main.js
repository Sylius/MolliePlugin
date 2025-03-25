import OnboardingWizard from './OnboardingWizard';
import {handleSubmit} from './helpers';

const handleTourShow = async () => {
    const tour = new OnboardingWizard();
    const url = '/admin/onboarding-wizard/status';
    const status = document.querySelector('#mollie-payment-form').dataset.status;
    const paymentMethodsNotLoaded = document.querySelector('.js-payment-method-not-loaded');
    const currentSavedStep = window.localStorage.getItem('step');

    try {
        const response = await fetch(url);
        const data = await response.json();
        const startWizard = await tour.initTour();

        if (data.completed === true && !currentSavedStep) {
            tour.disableTour();
        } else if (currentSavedStep && status) {
            tour.skipTo(currentSavedStep, true);
            if (!paymentMethodsNotLoaded) {
                tour.next();
            }
        } else if (!status && currentSavedStep) {
            tour.skipTo('step-env', true);
        }
    } catch (error) {
        console.error(error);
    }
};

if (document.querySelector('.js-onboarding-wizard')) {
    handleSubmit();
    handleTourShow();
}
