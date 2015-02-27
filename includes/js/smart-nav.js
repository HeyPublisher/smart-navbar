(function( smartnav, $, undefined ) {
  // called when code loaded - before rendered.
  smartnav.above_height = null;
  smartnav.navbar = null;
  
  smartnav.init = function() {
    smartnav.above_height = $('header').outerHeight();
    console.log('above_height',smartnav.above_height);
    smartnav.navbar = $('#smart-navbar');
    smartnav.navbar.hide();
  };

}( window.smartnav = window.smartnav || {}, jQuery ));

jQuery(window.smartnav).ready(function() {
  smartnav.init();
  
  jQuery(window).scroll(function(){
    //if scrolled down more than the header's height
    if (jQuery(window).scrollTop() > smartnav.above_height){
      if (!smartnav.navbar.is(":visible")) {
        // if yes, add "fixed" class to the <sticknav>
        // add padding top to the #content (value is same as the height of the sticknav)
        smartnav.navbar.show(); //.addClass('fixed').css('top','0').next().css('padding-top','60px');
        console.log("showing")
      }
    } else {
      if (smartnav.navbar.is(":visible")) {
        // when scroll up or less than aboveHeight, remove the "fixed" class, and the padding-top
        smartnav.navbar.hide(); //removeClass('fixed').next().css('padding-top','0');
        console.log("hiding")
      }
    }
  });
});