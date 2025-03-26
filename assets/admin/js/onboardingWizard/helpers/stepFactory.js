import _get from 'lodash.get';

import {updateTourCompletition} from "./index";

import {steps} from '../config/steps';
import wizardTranslations from '../config/wizardTranslations';

const handleStepButtons = (onboardingWizard, stepIndex, step, translator) => {
    if (step.customButtons) {
        return step.customButtons.map((customButton) => ({
            ...customButton,
            text: translator.trans(_get(wizardTranslations, customButton.text)),
            action: () => customButton.action(onboardingWizard, stepIndex),
        }));
    }

    return [
        {
            text: '<i class="close icon"></i>',
            action: () => {
                onboardingWizard.handleQuitConfirmation();
            },
            classes: `shepherd-button--close ${step.btnCloseClass || ''}`,
        },
        {
            text: '<i class="arrow down icon"></i>',
            action: () => onboardingWizard.modalCollapseHandler(),
            classes: `shepherd-button--collapse js-tour-collapse ${step.btnCollapseClass || ''}`,
        },
        {
            text: step.btnBackText
                ? translator.trans(_get(wizardTranslations, step.btnBackText))
                : translator.trans(_get(wizardTranslations, 'stepButtons.goBack')),
            secondary: true,
            classes: `${step.btnBackClass || ''}`,
            action() {
                const tour = onboardingWizard.tour;

                if (stepIndex === 0) {
                    tour.complete();
                    updateTourCompletition();
                } else {
                    if (step.urlMollie) {
                        window.open(`${step.urlMollie}/signin`, '_blank');
                        tour.next();

                        return;
                    }

                    tour.back();
                }
            },
        },
        {
            text: step.btnNextText
                ? translator.trans(_get(wizardTranslations, step.btnNextText))
                : translator.trans(_get(wizardTranslations, 'stepButtons.next')),
            classes: `${step.btnNextClass || ''}`,
            action() {
                const tour = onboardingWizard.tour;

                if (stepIndex === onboardingWizard.steps.length - 1) {
                    tour.complete();
                } else {
                    if (step.urlMollie) {
                        window.open(`${step.urlMollie}/signup`, '_blank');
                    }
                    tour.next();
                }
            },
        },
    ];
};

export default (steps = []) =>
    steps.map((step) => ({
        ...step,
        title: step.title ? _get(wizardTranslations, step.title) : null,
        text: _get(wizardTranslations, step.text),
        stepButtons: (onboardingWizard, stepIndex, translator) =>
            handleStepButtons(onboardingWizard, stepIndex, step, translator),
    }));
