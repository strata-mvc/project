Modernizr.load([
    //first test need for polyfill
    {
        test: window.matchMedia,
        nope: [
            WpConfig.plugins + "polyfills/media.match.min.js",
        ]
    },
    //and then load enquire
    WpConfig.plugins + "enquire/enquire.min.js",
    WpConfig.js + "scripts.min.js",
]);