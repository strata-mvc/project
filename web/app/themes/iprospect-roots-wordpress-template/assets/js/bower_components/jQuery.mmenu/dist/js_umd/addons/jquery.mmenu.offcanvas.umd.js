(function ( factory ) {
    if ( typeof define === 'function' && define.amd )
    {
        // AMD. Register as an anonymous module.
        define( [ 'jquery' ], factory );
    }
    else if ( typeof exports === 'object' )
    {
        // Node/CommonJS
        factory( require( 'jquery' ) );
    }
    else
    {
        // Browser globals
        factory( jQuery );
    }
}( function ( jQuery ) {


/*	
 * jQuery mmenu offCanvas addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */
!function(t){var e="mmenu",o="offCanvas";t[e].addons[o]={setup:function(){if(this.opts[o]){var n=this.opts[o],i=this.conf[o];a=t[e].glbl,this._api=t.merge(this._api,["open","close","setPage"]),("top"==n.position||"bottom"==n.position)&&(n.zposition="front"),"string"!=typeof i.pageSelector&&(i.pageSelector="> "+i.pageNodetype),a.$allMenus=(a.$allMenus||t()).add(this.$menu),this.vars.opened=!1;var r=[s.offcanvas];"left"!=n.position&&r.push(s.mm(n.position)),"back"!=n.zposition&&r.push(s.mm(n.zposition)),this.$menu.addClass(r.join(" ")).parent().removeClass(s.wrapper),this.setPage(a.$page),this._initBlocker(),this["_initWindow_"+o](),this.$menu[i.menuInjectMethod+"To"](i.menuWrapperSelector)}},add:function(){s=t[e]._c,n=t[e]._d,i=t[e]._e,s.add("offcanvas slideout modal background opening blocker page"),n.add("style"),i.add("resize")},clickAnchor:function(t){if(!this.opts[o])return!1;var e=this.$menu.attr("id");if(e&&e.length&&(this.conf.clone&&(e=s.umm(e)),t.is('[href="#'+e+'"]')))return this.open(),!0;if(a.$page){var e=a.$page.first().attr("id");return e&&e.length&&t.is('[href="#'+e+'"]')?(this.close(),!0):!1}}},t[e].defaults[o]={position:"left",zposition:"back",modal:!1,moveBackground:!0},t[e].configuration[o]={pageNodetype:"div",pageSelector:null,wrapPageIfNeeded:!0,menuWrapperSelector:"body",menuInjectMethod:"prepend"},t[e].prototype.open=function(){if(!this.vars.opened){var t=this;this._openSetup(),setTimeout(function(){t._openFinish()},this.conf.openingInterval),this.trigger("open")}},t[e].prototype._openSetup=function(){var e=this;this.closeAllOthers(),a.$page.each(function(){t(this).data(n.style,t(this).attr("style")||"")}),a.$wndw.trigger(i.resize+"-offcanvas",[!0]);var r=[s.opened];this.opts[o].modal&&r.push(s.modal),this.opts[o].moveBackground&&r.push(s.background),"left"!=this.opts[o].position&&r.push(s.mm(this.opts[o].position)),"back"!=this.opts[o].zposition&&r.push(s.mm(this.opts[o].zposition)),this.opts.extensions&&r.push(this.opts.extensions),a.$html.addClass(r.join(" ")),setTimeout(function(){e.vars.opened=!0},this.conf.openingInterval),this.$menu.addClass(s.current+" "+s.opened)},t[e].prototype._openFinish=function(){var t=this;this.__transitionend(a.$page.first(),function(){t.trigger("opened")},this.conf.transitionDuration),a.$html.addClass(s.opening),this.trigger("opening")},t[e].prototype.close=function(){if(this.vars.opened){var e=this;this.__transitionend(a.$page.first(),function(){e.$menu.removeClass(s.current).removeClass(s.opened),a.$html.removeClass(s.opened).removeClass(s.modal).removeClass(s.background).removeClass(s.mm(e.opts[o].position)).removeClass(s.mm(e.opts[o].zposition)),e.opts.extensions&&a.$html.removeClass(e.opts.extensions),a.$page.each(function(){t(this).attr("style",t(this).data(n.style))}),e.vars.opened=!1,e.trigger("closed")},this.conf.transitionDuration),a.$html.removeClass(s.opening),this.trigger("close"),this.trigger("closing")}},t[e].prototype.closeAllOthers=function(){a.$allMenus.not(this.$menu).each(function(){var o=t(this).data(e);o&&o.close&&o.close()})},t[e].prototype.setPage=function(e){var n=this,i=this.conf[o];e&&e.length||(e=a.$body.find(i.pageSelector),e.length>1&&i.wrapPageIfNeeded&&(e=e.wrapAll("<"+this.conf[o].pageNodetype+" />").parent())),e.each(function(){t(this).attr("id",t(this).attr("id")||n.__getUniqueId())}),e.addClass(s.page+" "+s.slideout),a.$page=e,this.trigger("setPage",e)},t[e].prototype["_initWindow_"+o]=function(){a.$wndw.off(i.keydown+"-offcanvas").on(i.keydown+"-offcanvas",function(t){return a.$html.hasClass(s.opened)&&9==t.keyCode?(t.preventDefault(),!1):void 0});var t=0;a.$wndw.off(i.resize+"-offcanvas").on(i.resize+"-offcanvas",function(e,o){if(1==a.$page.length&&(o||a.$html.hasClass(s.opened))){var n=a.$wndw.height();(o||n!=t)&&(t=n,a.$page.css("minHeight",n))}})},t[e].prototype._initBlocker=function(){var e=this;a.$blck||(a.$blck=t('<div id="'+s.blocker+'" class="'+s.slideout+'" />')),a.$blck.appendTo(a.$body).off(i.touchstart+"-offcanvas "+i.touchmove+"-offcanvas").on(i.touchstart+"-offcanvas "+i.touchmove+"-offcanvas",function(t){t.preventDefault(),t.stopPropagation(),a.$blck.trigger(i.mousedown+"-offcanvas")}).off(i.mousedown+"-offcanvas").on(i.mousedown+"-offcanvas",function(t){t.preventDefault(),a.$html.hasClass(s.modal)||(e.closeAllOthers(),e.close())})};var s,n,i,a}(jQuery);
}));