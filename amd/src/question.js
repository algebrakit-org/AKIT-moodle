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
    get form() {
        return document.querySelector(Selectors.actions.reponseForm);
    }
    get submitButton() {
        return this.form.querySelector('input[name="next"]');
    }
    get exercise() {
        return this.form.querySelector('akit-exercise');
    }

    constructor() {
        this.submitButton.addEventListener('click', () => this.submitQuestion());
    }

    submitQuestion() {
        return this.exercise.submit();
    }
}