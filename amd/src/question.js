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
    if (!AK._api) {
        let script = document.createElement('script');
        script.src = cdnUrl;
        document.body.appendChild(script);
        new AKQuestion();
    }
};

class AKQuestion {
    submitClicked = false;

    get form() {
        return document.querySelector(Selectors.actions.reponseForm);
    }
    get submitButton() {
        if(!this.form) {
            return;
        }
        return this.form.querySelector('input[name="next"]');
    }
    get exercise() {
        if(!this.form) {
            return;
        }
        return this.form.querySelector('akit-exercise');
    }

    constructor() {
        this.submitButton?.addEventListener('click', (evt) => this.submitQuestion(evt));
    }

    async submitQuestion(evt) {
        if(this.submitClicked) {
            this.submitButton = false;
        } else {
            evt.preventDefault();
            await this.exercise.submit();
            this.submitClicked = true;
            this.submitButton.click();
        }
    }
}