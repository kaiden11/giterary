
if( typeof( layout ) != 'undefined' && layout != null ) {
    layout.modal = function( title, body, opts ) {

        if( typeof( title ) != 'undefined' && title != null ) {
            $( '#modal .modal-title' ).html(
                title
            );
        }

        if( typeof( body ) != 'undefined' && body != null ) {
            $( '#modal .modal-body' ).html(
                body
            );
        }

        $('#modal').modal( opts );
    };
}

$(document).ready( function() {
    $('.meta nav .btn.clickable').btn_clickable(); 

    $('.dropdown-menu').on('click', function(e) {
        if($(this).hasClass('dropdown-menu-form')) {
            e.stopPropagation();
        }
    });

    var pad_top = function() { 
        $('body.giterary').css( 
            'padding-top', 
            $('.meta nav').height() + 20
        )
    };


    $(window).resize( pad_top );

    ( function() {
        pad_top();
    } ).defer();

    /*
    // Programmatically set scale according to device's pixel ratio
    // https://coderwall.com/p/ikrswg/bootstrap-3-grid-system-and-hi-density-small-screens-detected-as-xs
    var scale = 1 / (window.devicePixelRatio || 1);

    scale = ( scale < 0.5 ? 0.5 : scale );
    var content = 'width=device-width, initial-scale=' + scale + ', minimum-scale=' + scale;

    $('meta[name="viewport"]').attr('content', content);
    */

    var update_live_timestamps = function() {

        $( '.live-timestamp' ).each( function( k,v ) {
            var $v = $( v );

            $v.html( 
                util.short_time_diff( 
                    $v.data( 'time' ),
                    Math.floor( new Date().getTime() / 1000 )
                )
            );

            if( !$v.data( 'count' ) ) {
                $v.data( 'count', 0 );
            }

            $v.addClass( 'updating' );
        } );


        setTimeout( 
            update_live_timestamps,
            1000
        );
    };

    setTimeout( 
        update_live_timestamps,
        1000
    );



} );

