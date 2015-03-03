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
  function handleClick(elem) {
    
  };
  function bindClick(elem) {
    var obj = $('#snb-'+elem);

    var on_e = 'fa-'+elem;
    var off_e = on_e+'-o';
    // $(on_e).on('click',function() {
    //   if (onoff == 'on') {
    //     $(on_e).removeClass(on_e).addClass(off_e);
    //   } else {
    //     $(on_e).removeClass(off_e).addClass(on_e);
    //   }
    // });
    obj.on('click',function() {
      console.log("obj = ",obj)
      if (obj.hasClass(off_e)) {
        obj.removeClass(off_e).addClass(on_e);
        console.log("saving")
      } else if (obj.hasClass(on_e)) {
        console.log("unsaving")
        obj.removeClass(on_e).addClass(off_e);
      }
    });
  };
  
  function bindIcons() {
    $.each(['heart','bookmark','share-square'] ,function(idx,val) {
      bindClick(val);
    });
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
    bindIcons();
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