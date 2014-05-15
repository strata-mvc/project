/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can 
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * Google CDN, Latest jQuery
 * To use the default WordPress version of jQuery, go to lib/config.php and
 * remove or comment out: add_theme_support('jquery-cdn');
 * ======================================================================== */

(function($, TweenMax, enquire) {

// Use this variable to set up the common and page specific functions. If you 
// rename this variable, you will also need to rename the namespace below.
var Roots = {

  // All pages
  common: {
    init: function() {
      // JavaScript to be fired on all pages

      // initialize Snap.js
      var snapper = new Snap({
        element: document.getElementById("snap-content")
      });

      $(".snap-toggle").click(function(e){
        if( snapper.state().state === "left" ){
            snapper.close();
        } else {
            snapper.open('left');
        }
      });

      snapper.on("animated", function(){
        var $h = $(".hamburger");
        if($h.length){
          if (snapper.state().state === "left" ){
            if (!$h.hasClass("active")){$h.addClass("active");}
          } else {
            $h.removeClass("active");
          }
        }
      });

      $(".google-maps-builder-wrap").data("snap-ignore", true);
      console.log($(".google-maps-builder-wrap").data("snap-ignore"));

      enquire.register("screen and (max-width: 992px)", {
          setup: function(){
            console.log("Enquire max-992 setup is complete");
            snapper.disable();
          },
          match: function(){
            console.log("Enquire: Viewport 992px and smaller");
            snapper.enable();
          },
          unmatch: function(){
            console.log("Enquire: Viewport larger than 992px");
            snapper.disable();
          }
      });

    }
  },
  // Home page
  home: {
    init: function() {
      // JavaScript to be fired on the home page
      
      var tween = TweenMax.to($(".fa-heart"), 0.3, {scale: 1.5, ease: Bounce.easeOut});
      setInterval(function(){tween.restart();}, 1000);

      $(".royalSlider").royalSlider({
          // options go here
          // as an example, enable keyboard arrows nav
          keyboardNavEnabled: true,
          imageScaleMode: 'fit-if-smaller',
          arrowsNav: true,
          arrowsNavAutoHide: false,
          controlNavigation: 'bullets',
          imageScalePadding: 0,
          controlsInside: true,
          autoScaleSlider: true,
          autoScaleSliderWidth: 1140,
          autoScaleSliderHeight: 400,
          slidesSpacing: 0,
          addActiveClass: true,
          loop: true
      });

    }
  },
  // About us page, note the change from about-us to about_us.
  about_us: {
    init: function() {
      // JavaScript to be fired on the about us page
    }
  }
};

// The routing fires all common scripts, followed by the page specific scripts.
// Add additional events for more control over timing e.g. a finalize event
var UTIL = {
  fire: function(func, funcname, args) {
    var namespace = Roots;
    funcname = (funcname === undefined) ? 'init' : funcname;
    if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
      namespace[func][funcname](args);
    }
  },
  loadEvents: function() {
    UTIL.fire('common');

    $.each(document.body.className.replace(/-/g, '_').split(/\s+/),function(i,classnm) {
      UTIL.fire(classnm);
    });
  }
};

$(document).ready(UTIL.loadEvents);

})(jQuery, TweenMax, enquire); // Fully reference jQuery after this point.
