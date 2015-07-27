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


			$('img.svg').each(function(){
	            var $img = $(this);
	            var imgID = $img.attr('id');
	            var imgClass = $img.attr('class');
	            var imgURL = $img.attr('src');

	            jQuery.get(imgURL, function(data) {
	                // Get the SVG tag, ignore the rest
	                var $svg = $(data).find('svg');

	                // Add replaced image's ID to the new SVG
	                if(typeof imgID !== 'undefined') {
	                    $svg = $svg.attr('id', imgID);
	                }
	                // Add replaced image's classes to the new SVG
	                if(typeof imgClass !== 'undefined') {
	                    $svg = $svg.attr('class', imgClass+' replaced-svg');
	                }

	                // Remove any invalid XML tags as per http://validator.w3.org
	                $svg = $svg.removeAttr('xmlns:a');
	                // Replace image with new SVG
	                $img.replaceWith($svg);

	            }, 'xml');

	        });

			function resizeRegions() {
				var contentPadding   = parseInt($(".container").offset().left) + 15;
		        var contentHeight    = parseInt($(".region-locales .col-white").height()+100);
		  		$(".region-locales .col-grey").css("paddingLeft", contentPadding);
		  		$(".region-locales .col-grey").css("height", contentHeight);
			}

			function repositionMenu() {
				var contentPadding   = parseInt($(".container").offset().left);
				var count = 1;
				$(".main-nav .main-navigation > li").each(function(){
					var leftPos = $(this).position().left;
					console.log(count+" = "+leftPos);
					$(this).find(".dropdown").css("left", (-leftPos) + contentPadding - 20);
					count = count+1;
				});
			}

			repositionMenu();

	        $(window).resize(function(){
		        resizeRegions();
		        repositionMenu();
	  		});

	  		$(".main-nav .main-navigation li").hover(
			  function() {
			    $( this ).addClass("open");
			    $( this ).find(".dropdown").show();
			    $( this ).find(".dropdown").animate({
			    	opacity: 1
			    }, 200, "linear");

			  }, function() {
			    $( this ).find(".dropdown").animate({opacity: 0});
			    $( this ).find(".dropdown").animate({
			    	opacity: 0
			    }, "fast");
			    $( this ).find(".dropdown").hide();
			    $( this ).removeClass("open");
			  }
			);

			$(".localization-menu").click(function(e){
				e.preventDefault;
				$(".region-locales").slideToggle("slow");
				resizeRegions();
			});

		}
	},
	// Home page
	home: {
		init: function() {
			// JavaScript to be fired on the home page

			$('.success-stories').waypoint(function(){
				window.Odometer.init();

				$.each($('.odometer'), function(i, el){

				    setTimeout(function(){
				       var stat = $(el).attr("data-stat");
					   $(el).html(stat);
				    },500 + ( i * 500 ));

				});

			  }, {
			  	offset: '80%'
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
            $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
                UTIL.fire(classnm);
            });
        }
    };

    $(document).ready(UTIL.loadEvents);

})(jQuery, TweenMax, enquire); // Fully reference jQuery after this point.
