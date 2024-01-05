function loadJs() {
    var script = document.createElement('script');
    script.src = "https://widgets.algebrakit.com/akit-widgets.min.js";
    document.body.appendChild(script);
}

if (typeof(AlgebraKIT) == 'undefined') {
    var AlgebraKIT = {
        config: {  }
    };
    loadJs();
}
else if (!AlgebraKIT._api) {
    loadJs();
}