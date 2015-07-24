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
 * jQuery mmenu sectionIndexer addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */
!function(e){var a="mmenu",r="sectionIndexer";e[a].addons[r]={setup:function(){var i=this,d=this.opts[r];this.conf[r],s=e[a].glbl,"boolean"==typeof d&&(d={add:d}),"object"!=typeof d&&(d={}),d=this.opts[r]=e.extend(!0,{},e[a].defaults[r],d),this.bind("init",function(a){if(d.add){switch(d.addTo){case"panels":var r=a;break;default:var r=e(d.addTo,this.$menu).filter("."+n.panel)}r.find("."+n.divider).closest("."+n.panel).addClass(n.hasindexer)}if(!this.$indexer&&this.$menu.children("."+n.hasindexer).length){this.$indexer=e('<div class="'+n.indexer+'" />').prependTo(this.$menu).append('<a href="#a">a</a><a href="#b">b</a><a href="#c">c</a><a href="#d">d</a><a href="#e">e</a><a href="#f">f</a><a href="#g">g</a><a href="#h">h</a><a href="#i">i</a><a href="#j">j</a><a href="#k">k</a><a href="#l">l</a><a href="#m">m</a><a href="#n">n</a><a href="#o">o</a><a href="#p">p</a><a href="#q">q</a><a href="#r">r</a><a href="#s">s</a><a href="#t">t</a><a href="#u">u</a><a href="#v">v</a><a href="#w">w</a><a href="#x">x</a><a href="#y">y</a><a href="#z">z</a>'),this.$indexer.children().on(t.mouseover+"-sectionindexer "+n.touchstart+"-sectionindexer",function(){var a=e(this).attr("href").slice(1),r=i.$menu.children("."+n.current),t=r.find("."+n.listview),s=!1,d=r.scrollTop(),h=t.position().top+parseInt(t.css("margin-top"),10)+parseInt(t.css("padding-top"),10)+d;r.scrollTop(0),t.children("."+n.divider).not("."+n.hidden).each(function(){s===!1&&a==e(this).text().slice(0,1).toLowerCase()&&(s=e(this).position().top+h)}),r.scrollTop(s!==!1?s:d)});var s=function(e){i.$menu[(e.hasClass(n.hasindexer)?"add":"remove")+"Class"](n.hasindexer)};this.bind("openPanel",s),s.call(this,this.$menu.children("."+n.current))}})},add:function(){n=e[a]._c,i=e[a]._d,t=e[a]._e,n.add("indexer hasindexer"),t.add("mouseover touchstart")},clickAnchor:function(e){return e.parent().is("."+n.indexer)?!0:void 0}},e[a].defaults[r]={add:!1,addTo:"panels"};var n,i,t,s}(jQuery);
}));