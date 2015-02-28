/*
  SmartNavbar JS functions
  Copyright 2014-2015 Loudlever (wordpress@loudlever.com)

*/
(function( smartnav, $, undefined ) {
  smartnav.above_height = null;
  smartnav.navbar = null;

  // what's the initial state
  function chooseDisplay() {
    if ($(document).scrollTop() > smartnav.above_height) { 
      smartnav.show(); 
    } else { 
      smartnav.hide(); 
    }
  };

  // bind to scrolling
  function bindScroll() {
    $(window).scroll(function(){ chooseDisplay(); });
  };

  smartnav.init = function() {
    smartnav.above_height = $('header').outerHeight();
    smartnav.navbar = $('#smart-navbar');
    chooseDisplay();
    bindScroll();
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
});