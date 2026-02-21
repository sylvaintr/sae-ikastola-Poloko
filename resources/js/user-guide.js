/**
 * User Guide Alpine.js Component
 * Gère la navigation et la progression du guide d'utilisation
 */

const STORAGE_KEY = 'ikastola_user_guide';

/**
 * Récupère la progression sauvegardée depuis LocalStorage
 * @returns {Object} État sauvegardé ou valeurs par défaut
 */
function getStoredProgress() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch (e) {
        console.warn('Erreur lors de la lecture du localStorage:', e);
    }
    return {
        currentStep: 0,
        completed: false,
        lastVisit: null
    };
}

/**
 * Sauvegarde la progression dans LocalStorage
 * @param {Object} progress État à sauvegarder
 */
function saveProgress(progress) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            ...progress,
            lastVisit: new Date().toISOString()
        }));
    } catch (e) {
        console.warn('Erreur lors de la sauvegarde du localStorage:', e);
    }
}

/**
 * Composant Alpine.js pour le guide d'utilisation
 */
function userGuide() {
    return {
        currentStep: 0,
        totalSteps: 0,
        completed: false,

        /**
         * Initialise le composant
         */
        init() {
            // Récupérer le nombre total d'étapes depuis le DOM
            const navItems = this.$el.querySelectorAll('.user-guide-nav-item');
            this.totalSteps = navItems.length;

            // Charger la progression sauvegardée
            const stored = getStoredProgress();
            this.currentStep = stored.currentStep;
            this.completed = stored.completed;

            // Réinitialiser à 0 si l'étape sauvegardée dépasse le nombre d'étapes
            if (this.currentStep >= this.totalSteps) {
                this.currentStep = 0;
            }

            // Écouter l'ouverture du modal pour mettre à jour l'état
            const modal = document.getElementById('userGuideModal');
            if (modal) {
                modal.addEventListener('shown.bs.modal', () => {
                    // Mettre à jour la vue si nécessaire
                    this.$nextTick(() => {
                        this.updateProgress();
                    });
                });
            }
        },

        /**
         * Passe à l'étape suivante
         */
        nextStep() {
            if (this.currentStep < this.totalSteps - 1) {
                this.currentStep++;
                this.updateProgress();
            }
        },

        /**
         * Revient à l'étape précédente
         */
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.updateProgress();
            }
        },

        /**
         * Va à une étape spécifique
         * @param {number} step Index de l'étape
         */
        goToStep(step) {
            if (step >= 0 && step < this.totalSteps) {
                this.currentStep = step;
                this.updateProgress();
            }
        },

        /**
         * Termine le guide
         */
        finish() {
            this.completed = true;
            saveProgress({
                currentStep: 0,
                completed: true
            });
        },

        /**
         * Met à jour et sauvegarde la progression
         */
        updateProgress() {
            saveProgress({
                currentStep: this.currentStep,
                completed: this.completed
            });
        },

        /**
         * Réinitialise le guide
         */
        reset() {
            this.currentStep = 0;
            this.completed = false;
            this.updateProgress();
        }
    };
}

// Enregistrer le composant globalement pour Alpine.js
if (typeof window !== 'undefined') {
    window.userGuide = userGuide;
}

export { userGuide };
