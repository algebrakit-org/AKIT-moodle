/**
 * Javascript entry point for the editor functionality.
 */

import Selectors from './selectors';

export const init = (cdnUrl, proxyUrl, useEditor, audienceSpec, blacklist) => {
    new AKEditor(cdnUrl, proxyUrl, useEditor, audienceSpec, blacklist);

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
    get editorContainer() {
      return document.querySelector(Selectors.actions.editorContainer);
    }
    get previewContainer() {
        return document.querySelector(Selectors.actions.previewContainer);
    }
    get jsonInputElement() {
        return document.querySelector(Selectors.actions.jsonInputElement);
    }
    get exerciseIdInputElement() {
      return document.querySelector(Selectors.actions.exerciseIdInputElement);
    }
    get generalStemDiv() {
      return document.querySelector(Selectors.actions.generalStemDiv);
    }

    spec = null;
    cdnUrl = null;
    proxyUrl = null;
    useEditor = false;
    audienceSpec = null;
    blacklist = null;
    AK = null; // AlgebraKIT instance

    constructor(cdnUrl, proxyUrl, useEditor, audienceSpec, blacklist) {
        this.cdnUrl = cdnUrl;
        this.proxyUrl = proxyUrl;
        this.useEditor = !!useEditor;
        this.audienceSpec = audienceSpec;
        this.blacklist = blacklist;

        // get the spec from a hidden input field in the moodle form
        let jsonString = this.jsonInputElement.value;
        let exerciseId = this.exerciseIdInputElement.value;

        let showEditor = this.useEditor;

        // override setting for old questions that were created with a diferent setting
        if(exerciseId && !jsonString) {
          showEditor = false;
        } else if(!exerciseId && jsonString) {
          showEditor = true;
        }

        // Let requireJS know where to find the jsxgraphcore library
        let existingConfig = require.s.contexts._.config;
        existingConfig.paths['jsxgraphcore'] =  cdnUrl.replace('akit-widgets', 'jsxgraphcore').replace('.js', '');

        if(showEditor) {
          if (jsonString) {
            this.spec = JSON.parse(jsonString);
          }
          this.loadEditor();
        }
    }

    async loadEditor() {
      this.exerciseIdInputElement.parentElement.parentElement.style.display = 'none';
      this.generalStemDiv.style.display = 'none';

      if(!window['AlgebraKIT']) {
        window['AlgebraKIT'] = {
          config: {
              secureProxy: {
                  url: this.proxyUrl
              }
          }
        };
        await this.addScript(this.cdnUrl);
      }

      this.AK = window['AlgebraKIT'];

      this.editorContainer.innerHTML = `
      <akit-exercise-editor audiences='${this.audienceSpec}'
         allow-assets="false" enable-preview="false" enable-basic-info="false"
         interaction-blacklist='${this.blacklist}' enable-id-field="false">
      </akit-exercise-editor>
      <div class="qtype_algebrakit-editor-button-wrapper">
        <button class="algebrakit-button" data-action="qtype_algebrakit/editor-run_button" type="button">Preview</button>
      </div>
      <div class="qtype_algebrakit-editor-akit-preview" data-action="qtype_algebrakit/editor-preview_div">
      </div>
      `;

      await this.waitForEditorAvailable(0);

      if (this.spec) {
        await this.authoringComponent.updateExercise(this.spec);
      } else {
        this.spec = await this.authoringComponent.getExercise();
      }

      this.authoringComponent.addWidgetListener('exerciseChanged', ()=>this.saveSpec());
      this.runButton.addEventListener('click', this.run.bind(this));
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

        await this.AK.waitUntilReady(this.previewComponent);

        this.previewComponent.run();
    }

    async addScript(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async waitForEditorAvailable(iter) {
      if(iter>100) {
        throw new Error("AlgebraKIT editor not available");
      }
      if(!this.authoringComponent.updateExercise) {
        await new Promise(resolve => setTimeout(resolve, 100));
        return this.waitForEditorAvailable(iter+1);
      }
    }
}

