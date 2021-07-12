if (typeof varExists !== "function") {
    function varExists(varName) {
        return new Promise(function (resolve, reject) {
            (function waitForJQuery() {
                if (typeof window[varName] !== 'undefined') return resolve();
                setTimeout(waitForJQuery, 30);
            })();
        });
    }
}

varExists('jQuery').then(function () {

    (function (wooptpm, $, undefined) {



    }(window.wooptpm = window.wooptpm || {}, jQuery));

}).catch(function () {
    console.log('object couldn\'t be loaded');
})