Modernizr.load([
    //first test need for polyfill
    {
        test: window.matchMedia,
        nope: [
            WpConfig.plugins + "polyfills/matchMedia.js",
            WpConfig.plugins + "polyfills/matchMedia.addListener.js"
        ]
    },
    //and then load enquire
    WpConfig.plugins + "enquire/enquire.min.js",
    WpConfig.js + "scripts.min.js",
]);