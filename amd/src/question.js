/**
 * Javascript entry point for showing an Algebrakit question.
 */

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
    }
};
