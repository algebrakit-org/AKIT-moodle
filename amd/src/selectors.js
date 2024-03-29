export default {
    actions: {
        runButton: '[data-action="qtype_algebrakit/editor-run_button"]',
        authoringComponent: 'akit-exercise-editor',
        previewComponent: 'akit-exercise-preview',
        previewContainer: '[data-action="qtype_algebrakit/editor-preview_div"]',
        editorContainer: '[data-action="qtype_algebrakit/editor-container_div"]',
        jsonInputElement: 'input[name="exercise_in_json"]',
        exerciseIdInputElement: 'input[name="exercise_id"]',
        generalStemDiv: 'div#fitem_id_questiontext', //the div containing the stem in the general section of the exercise form
        //used in question.js
        reponseForm: '#responseform'
    }
};