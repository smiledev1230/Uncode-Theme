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
	$('.new-badge img').before('<img class="new-icon"/>');
	$('.mid-menu .mid-menu-logo').after('<div class="uncode_text_column mid-menu-drop"><p><i class="fa fa-angle-down"></i><i class="fa fa-angle-up"></i></p></div>');
	$('.mid-menu .mid-menu-drop p .fa').on('click', function(){
		if ($('.mid-menu-content .menu-smart').css('display') == 'none') {
			$('.mid-menu .mid-menu-drop p .fa.fa-angle-down').hide();
			$('.mid-menu .mid-menu-drop p .fa.fa-angle-up').show();
		} else {
			$('.mid-menu .mid-menu-drop p .fa.fa-angle-down').show();
			$('.mid-menu .mid-menu-drop p .fa.fa-angle-up').hide();
		}
		$('.mid-menu-content .menu-smart').toggle("slow");
	});
});
