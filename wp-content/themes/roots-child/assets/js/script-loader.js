yepnope([
    //first test need for polyfill
    {
        test: window.matchMedia,
        nope: [
            WpConfig.parentPlugins + "polyfills/media.match.min.js",
        ]
    },
    //and then load enquire
    WpConfig.bower + "enquire/dist/enquire.min.js",
    WpConfig.childJs + "scripts.min.js",
]);