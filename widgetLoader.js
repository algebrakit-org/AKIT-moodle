function loadJs() {
    var script = document.createElement('script');
    // script.src = "https://widgets.staging.algebrakit.com/akit-widgets.min.js";
    
    script.src = "AK_WIDGETS_URL/akit-widgets.js";
    document.body.appendChild(script);
}

if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {
            minified: false,
            theme: 'akit',  //algebrakit theme is default
            widgets: [{
                name: 'akit-formula-editor',
                handwriting: false
            }],
            loggingLevel: 4
        }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}