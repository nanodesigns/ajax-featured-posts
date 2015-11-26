/* JavaScripts for AJAX Featured Posts Admin Tweaks */
jQuery(document).ready(function($) {

	/* ----------------------------------------------------------- */
    /*  Make Featured
    /*  Make an item featured using AJAX
    /* ----------------------------------------------------------- */
    $('.make-featured').on( 'click', function() {

        var this_item   = $(this),
            this_id     = this_item.attr('id');
        
        $.ajax({
            type: 'POST',
            url: ajaxurl, //already defined by WordPress in admin end
            data: {
                    action: 'afp_make_featured',  // WP AJAX function's action hook
                    id: this_id
                },
            success: function (data) {
                if( data != false ) {

                    if( 'added' === data ) {
                        this_item.addClass('featured');
                        this_item.find('.dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
                    }
                    else if( 'deleted' === data ) {
                        this_item.removeClass('featured');
                        this_item.find('.dashicons').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
                    }
                }
            }
        });
    });

});