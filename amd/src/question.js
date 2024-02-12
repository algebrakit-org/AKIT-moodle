/**
 * Javascript entry point for showing an Algebrakit question.
 * - To guarantee the student input is stored, the submit function is
 *   called when the student clicks the "Next" button.
 */
import Selectors from './selectors';

/* eslint-disable no-undef */
const AK = (typeof(AlgebraKIT) == 'undefined')
    ?{
        config: {}
    } :{
        AlgebraKIT
    };
/* eslint-enable */

export const init = (cdnUrl) => {
    // Let requireJS know where to find the jsxgraphcore library
    let existingConfig = require.s.contexts._.config;
    existingConfig.paths['jsxgraphcore'] =  cdnUrl.replace('akit-widgets', 'jsxgraphcore').replace('.js', '');

    // Load the Algebrakit library
    if (!AK._api) {
        let script = document.createElement('script');
        script.src = cdnUrl;
        document.body.appendChild(script);
    }

    new AKQuestion();
};

class AKQuestion {
    submitClicked = false;

    get form() {
        return document.querySelector(Selectors.actions.reponseForm);
    }
    get nextButton() {
        if(!this.form) {
            return;
        }
        return this.form.querySelector('input[name="next"]');
    }
    get prevButton() {
        if(!this.form) {
            return;
        }
        return this.form.querySelector('input[name="previous"]');
    }
    get exercise() {
        if(!this.form) {
            return;
        }
        return this.form.querySelector('akit-exercise');
    }

    constructor() {
        this.nextButton?.addEventListener('click', (evt) => this.submitQuestion(evt, true));
        this.prevButton?.addEventListener('click', (evt) => this.submitQuestion(evt, false));
    }

    async submitQuestion(evt, next) {
        if(this.submitClicked) {
            // prevent loop
        } else {
            evt.preventDefault();
            this.submitClicked = true;
            await this.exercise.submit();
            if(next) {
                this.nextButton.click();
            } else {
                this.prevButton.click();
            }
        }
    }
}