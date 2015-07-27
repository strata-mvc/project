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
 * jQuery mmenu searchfield addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */
!function(e){function s(e){switch(e){case 9:case 16:case 17:case 18:case 37:case 38:case 39:case 40:return!0}return!1}var n="mmenu",a="searchfield";e[n].addons[a]={setup:function(){var o=this,d=this.opts[a],c=this.conf[a];r=e[n].glbl,"boolean"==typeof d&&(d={add:d}),"object"!=typeof d&&(d={}),d=this.opts[a]=e.extend(!0,{},e[n].defaults[a],d),this.bind("close",function(){this.$menu.find("."+t.search).find("input").blur()}),this.bind("init",function(n){if(d.add){switch(d.addTo){case"panels":var a=n;break;default:var a=e(d.addTo,this.$menu)}a.each(function(){var s=e(this);if(!s.is("."+t.panel)||!s.is("."+t.vertical)){if(!s.children("."+t.search).length){var n=c.form?"form":"div",a=e("<"+n+' class="'+t.search+'" />');if(c.form&&"object"==typeof c.form)for(var l in c.form)a.attr(l,c.form[l]);a.append('<input placeholder="'+d.placeholder+'" type="text" autocomplete="off" />'),s.hasClass(t.search)?s.replaceWith(a):s.prepend(a).addClass(t.hassearch)}if(d.noResults){var i=s.closest("."+t.panel).length;if(i||(s=o.$menu.children("."+t.panel).first()),!s.children("."+t.noresultsmsg).length){var r=s.children("."+t.listview).first();e('<div class="'+t.noresultsmsg+'" />').append(d.noResults)[r.length?"insertAfter":"prependTo"](r.length?r:s)}}}}),d.search&&e("."+t.search,this.$menu).each(function(){var n=e(this),a=n.closest("."+t.panel).length;if(a)var r=n.closest("."+t.panel),c=r;else var r=e("."+t.panel,o.$menu),c=o.$menu;var h=n.children("input"),u=o.__findAddBack(r,"."+t.listview).children("li"),f=u.filter("."+t.divider),p=o.__filterListItems(u),v="> a",m=v+", > span",b=function(){var s=h.val().toLowerCase();r.scrollTop(0),p.add(f).addClass(t.hidden).find("."+t.fullsubopensearch).removeClass(t.fullsubopen).removeClass(t.fullsubopensearch),p.each(function(){var n=e(this),a=v;(d.showTextItems||d.showSubPanels&&n.find("."+t.next))&&(a=m),e(a,n).text().toLowerCase().indexOf(s)>-1&&n.add(n.prevAll("."+t.divider).first()).removeClass(t.hidden)}),d.showSubPanels&&r.each(function(){var s=e(this);o.__filterListItems(s.find("."+t.listview).children()).each(function(){var s=e(this),n=s.data(l.sub);s.removeClass(t.nosubresults),n&&n.find("."+t.listview).children().removeClass(t.hidden)})}),e(r.get().reverse()).each(function(s){var n=e(this),i=n.data(l.parent);i&&(o.__filterListItems(n.find("."+t.listview).children()).length?(i.hasClass(t.hidden)&&i.children("."+t.next).not("."+t.fullsubopen).addClass(t.fullsubopen).addClass(t.fullsubopensearch),i.removeClass(t.hidden).removeClass(t.nosubresults).prevAll("."+t.divider).first().removeClass(t.hidden)):a||(n.hasClass(t.opened)&&setTimeout(function(){o.openPanel(i.closest("."+t.panel))},1.5*(s+1)*o.conf.openingInterval),i.addClass(t.nosubresults)))}),c[p.not("."+t.hidden).length?"removeClass":"addClass"](t.noresults),this.update()};h.off(i.keyup+"-searchfield "+i.change+"-searchfield").on(i.keyup+"-searchfield",function(e){s(e.keyCode)||b.call(o)}).on(i.change+"-searchfield",function(){b.call(o)})})}})},add:function(){t=e[n]._c,l=e[n]._d,i=e[n]._e,t.add("search hassearch noresultsmsg noresults nosubresults fullsubopensearch"),i.add("change keyup")},clickAnchor:function(){}},e[n].defaults[a]={add:!1,addTo:"panels",search:!0,placeholder:"Search",noResults:"No results found.",showTextItems:!1,showSubPanels:!0},e[n].configuration[a]={form:!1};var t,l,i,r}(jQuery);
}));