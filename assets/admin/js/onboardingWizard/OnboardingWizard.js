import Shepherd from 'shepherd.js';
import _get from 'lodash.get';
import wizardTranslations from './config/wizardTranslations';
import {steps, stepQuitConfirmation} from './config/steps';
import shepherdConfig from './config/shepherdConfig';
import {clearStorage, stepFactory, Translator} from './helpers';

export default class onboardingWizard {
    constructor(tourSteps = steps, tourConfig = shepherdConfig, tourQuitConfirmation = stepQuitConfirmation) {
        this.steps = stepFactory(tourSteps);
        this.stepQuitConfirmation = stepFactory(tourQuitConfirmation)[0];
        this.tourConfig = tourConfig;
        this.navbar = document.querySelector('.js-onboarding-wizard');
        this.navBarItems = [...this.navbar.querySelectorAll('.js-onboarding-wizard-progress')];
        this.previousStepIndex = 0;
    }

    modalCollapseHandler() {
        const currentStep = this.tour.currentStep.el;
        const buttonCollapse = currentStep.querySelector('.js-tour-collapse');
        const isCollapsed = [...currentStep.classList].includes('shepherd-element--collapsed');

        const expandButton = document.createElement('span');
        expandButton.classList.add('shepherd-button__open', 'js-shepherd-expand');
        expandButton.textContent = _get(wizardTranslations, 'common.open');

        const textOpen = buttonCollapse.querySelector('.js-shepherd-expand');

        if (isCollapsed) {
            buttonCollapse.removeChild(textOpen);
        } else {
            buttonCollapse.appendChild(expandButton);
        }

        currentStep.classList.toggle('shepherd-element--collapsed', !isCollapsed);
        currentStep.setAttribute('aria-hidden', !isCollapsed);
    }

    handleQuitConfirmation() {
        const returnStepIndex = this.previousStepIndex;

        this.tour.addStep({
            ...this.stepQuitConfirmation,
            title: this.translations.trans(this.stepQuitConfirmation.title),
            buttons: this.stepQuitConfirmation.stepButtons(this, returnStepIndex, this.translations),
        });

        this.tour.show('step-quit-confirmation', true);
    }

    navbarVisibilityHandler(isActive) {
        this.navbar.classList.toggle('d-none', !isActive);
        this.navbar.setAttribute('aria-hidden', !isActive);
    }

    navbarProgressHandler() {
        const currentStepProgress = this.tour.getCurrentStep().options.highlightClass;

        this.navBarItems.forEach((navBarItem) => {
            if (navBarItem.getAttribute('data-navigation-step') === currentStepProgress) {
                navBarItem.classList.add('onboarding-wizard__step--current');
            } else {
                navBarItem.classList.remove('onboarding-wizard__step--current');
            }
        });
    }

    restartTourListener() {
        const restartTourTrigger = document.querySelector('.js-restart-tour');

        restartTourTrigger.addEventListener('click', () => {
            clearStorage('step');
            this.tour.start();
            this.navbar.classList.remove('d-none');
        });
    }

    async initTour() {
        if (this.navbar) {
            const translations = await Translator();
            this.translations = translations;
            this.tour = new Shepherd.Tour({
                ...this.tourConfig,
            });
            this.steps.forEach((step, stepIndex) => {
                this.tour.addStep({
                    ...step,
                    title: translations.trans(step.title),
                    text: translations.trans(step.text),
                    buttons: step.stepButtons(this, stepIndex, translations),
                    when: {
                        show: () => {
                            this.previousStepIndex = this.tour.getCurrentStep().id;
                            this.navbarProgressHandler();
                        },
                    },
                });
            });

            this.tour.on('complete', () => {
                this.navbarVisibilityHandler(false);
            });
            this.tour.start();

            this.restartTourListener();
        }
    }

    disableTour() {
        this.tour.complete();
    }

    skipTo(element = 'step-start', bool = true) {
        this.tour.show(element, bool);
    }

    next() {
        this.tour.next();
    }
}
