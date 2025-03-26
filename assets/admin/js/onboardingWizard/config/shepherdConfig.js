export default {
    useModalOverlay: true,
    confirmCancel: false,
    keyboardNavigation: false,
    exitOnEsc: true,
    defaultStepOptions: {
        class: 'onboardingWizard-popup',
        arrow: false,
        cancelIcon: {
            enabled: false,
        },
        scrollTo: {behavior: 'smooth', block: 'center'},
        modalOverlayOpeningRadius: 4,
    },
};
