var layout = {
    meta_toggle:    function() {
        $('body.giterary').toggle_class( 'meta-on meta-off' );
    },
    toggle_metas: function() {
        if( $('body.giterary').hasClass( 'meta-on' ) ) {
            $('.meta-on').addClass('meta-off').removeClass('meta-on');
        } else {
            $('.meta-off').addClass('meta-on').removeClass('meta-off');
        }
    },
    toggle_if_escape: function( e ) {

        if( $(e.target).is( 'input,textarea,select,button,submit' ) ) {
            return;
        }

        switch( e.which ) {
            case 27:
                layout.toggle_metas();
                break;
            default:
                break;
        }
    },
    setup_meta_scrolling: function() {

        var meta_pane_height    = $('.meta.pane').height();
        var selector_to_scroll  = '.meta.container .meta.scroller';

        var max_scroller_height = function() {
            return ( $(window).height() - meta_pane_height ) * 0.85;
        }

        $(selector_to_scroll).css( 'max-height', max_scroller_height()   );

        $(window).resize(
            function() {
                $(selector_to_scroll).css( 'max-height', max_scroller_height()   );
            }
        );


    },
    setup:  function() {
        $('body.giterary')
            .on(    'click',    '.meta.pane .meta.toggle a',    layout.meta_toggle          )
            .on(    'keyup',                                    layout.toggle_if_escape     )
        ;

        // $('.meta nav .btn.clickable').btn_clickable();

        layout.setup_meta_scrolling();
    }
}

$(document).ready( 
    function() {
        layout.setup();
    }
);
