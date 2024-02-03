/**
 * Javascript entry point for the editor functionality.
 */

import Selectors from './selectors';

/* eslint-disable no-undef */
const AK = AlgebraKIT;
/* eslint-enable */

export const init = () => {
    new AKEditor();

};


class AKEditor {
    get runButton() {
        return document.querySelector(Selectors.actions.runButton);
    }
    get authoringComponent() {
      return document.querySelector(Selectors.actions.authoringComponent);
    }
    get previewComponent() {
        return document.querySelector(Selectors.actions.previewComponent);
    }
    get previewContainer() {
        return document.querySelector(Selectors.actions.previewContainer);
    }
    get jsonInputElement() {
        return document.querySelector(Selectors.actions.jsonInputElement);
    }

    spec = null;

    constructor() {
        this.runButton.addEventListener('click', this.run.bind(this));

        // get the spec from a hidden input field in the moodle form
        let jsonString = this.jsonInputElement.value;

        if (jsonString) {
            this.spec = JSON.parse(jsonString);
        }

        this.loadEditor();
    }

    async loadEditor() {
        await AK.waitUntilReady(this.authoringComponent);

        if (this.spec) {
          await this.authoringComponent.updateExercise(this.spec);
        } else {
          this.spec = await this.authoringComponent.getExercise();
        }

        this.authoringComponent.addWidgetListener('exerciseChanged', ()=>this.saveSpec());
      }

      async saveSpec() {
        if (this.authoringComponent) {
          // Read the exercise definition from the authoring component
          this.spec = await this.authoringComponent.getExercise();

          // Update the value of the hidden element
          this.jsonInputElement.value = JSON.stringify(this.spec);
        }
    }

    async run() {
        this.previewContainer.innerHTML =
        `<akit-exercise-preview show-run-button="false" exercise-id="${this.spec.id}"></akit-exercise-preview>`;

        await AK.waitUntilReady(this.previewComponent);

        this.previewComponent.run();
    }
}

