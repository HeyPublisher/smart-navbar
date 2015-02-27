/*
  SmartNavbar JS functions
  Copyright 2014-2015 Loudlever (wordpress@loudlever.com)

*/
(function( smartnav, $, undefined ) {
  smartnav.above_height = null;
  smartnav.navbar = null;

  smartnav.init = function() {
    smartnav.above_height = $('header').outerHeight();
    console.log('above_height',smartnav.above_height);
    smartnav.navbar = $('#smart-navbar');
    smartnav.navbar.hide();
  };
  smartnav.hide = function() {
    if (smartnav.navbar.is(":visible")) {
      smartnav.navbar.hide();
    }
  };
  smartnav.show = function() {
    if (!smartnav.navbar.is(":visible")) {
      if (smartnav.navbar.hasClass('with-admin')) {
        smartnav.navbar.css('top',$('#wpadminbar').css('height'));
      }
      smartnav.navbar.show();
    }
  };
}( window.smartnav = window.smartnav || {}, jQuery ));

jQuery(window.smartnav).ready(function() {
  smartnav.init();
  jQuery(window).scroll(function(){
    if (jQuery(window).scrollTop() > smartnav.above_height) { smartnav.show(); } 
    else { smartnav.hide(); }
  });
});