jQuery(document).ready(function($) {
	"use strict";
	function isTouchSupported() {
	    var msTouchEnabled = window.navigator.msMaxTouchPoints;
	    var generalTouchEnabled = "ontouchstart" in document.createElement("div");
	    if ((msTouchEnabled || generalTouchEnabled) && ($(document).width() < 960)) {
	    	console.log(msTouchEnabled);
	    	console.log(generalTouchEnabled);
	        $(".bb-ultimate-carousel .slick-next.slick-arrow, .bb-ultimate-carousel .slick-prev.slick-arrow").css("display", "none");
	    } else {
	    	return false;
	    }
	}
	isTouchSupported();
});
