jQuery(document).ready(function() {
    function scroll_to_top() {
        jQuery('#scroll_to_top').click(function() {
            jQuery('html,body').animate({scrollTop: 0}, 'slow');
        });
        jQuery(window).scroll(function(){
            if(jQuery(window).scrollTop()<300){
                jQuery('#scroll_to_top').fadeOut();
            } else{
                jQuery('#scroll_to_top').fadeIn();
            }
        });
    }
    scroll_to_top();


});
