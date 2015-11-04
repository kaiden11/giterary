var diff = {
    meta_toggle:    function() {
        $('.diff.container').toggle_class( 'meta-on meta-off' );
    },
    diff_count: function() {

        var current = $('.line-of-code .diff.current').get( 0 );

        var visible_diffs = $('.line-of-code .diff').not( ':hidden' );

        if( current ) {
            $('#diff-count').html(
                ( visible_diffs.index( current ) + 1 ) + ' / ' +
                visible_diffs.length
            );
        } else {
            $('#diff-count').html(
                visible_diffs.length
            );
        }

    },
    scroll_to_prev_difference: function() {
        var diffs = $('div.diff span.diff.seen').not(':hidden');

        // Last item will be the current item
        $('div.diff span.diff.current.seen').removeClass( 'current' ).removeClass( 'seen' );
        
        if( diffs && diffs.length > 1 ) {
            $(window).scrollTo( 
                diffs.get( diffs.length - 2 ),
                200,
                {
                    offset: {
                        top:    ( -1*( $('.meta nav').height() + 20 ) ),
                        left:   0
                    }
                }
            );

            $(diffs.get( diffs.length-2 )).addClass( 'seen' );
            $(diffs.get( diffs.length-2 )).addClass( 'current' );
        } else {

            // Nowhere to back up to. Mark all as seen, 
            // and scroll to the last
            diffs = $('div.diff span.diff').addClass( 'seen' );

            $( $(diffs).get( diffs.length - 1 ) ).addClass( 'current' );

            $(window).scrollTo( 
                diffs.get( diffs.length - 1 ),
                200,
                {
                    offset: {
                        top:    ( -1*( $('.meta nav').height() + 20 ) ),
                        left:   0
                    }
                }
            );
        }

        diff.diff_count();
    },

    scroll_to_next_difference: function() {
        var diffs = $('div.diff span.diff').removeClass( 'current' ).not(':hidden').not( '.seen' );
        
        if( diffs && diffs.length > 0 ) {
            $(window).scrollTo( 
                diffs.get(0),
                200,
                {
                    offset: {
                        top:    ( -1*( $('.meta nav').height() + 20 ) ),
                        left:   0
                    }
                }
            );

            $(diffs.get(0)).addClass( 'seen' );
            $(diffs.get(0)).addClass( 'current' );
        } else {

            diffs = $('div.diff span.diff').removeClass( 'seen' );

            if( diffs.size() <= 0 ) {
                alert( "No differences." );
            } else {

                if( $('div.diff span.diff').not( ':hidden' ) ) {
                    diff.scroll_to_next_difference();
                }
            }
        }

        diff.diff_count();
    },
    setup_highlight: function() {
        /*
        $( '.line-of-code' ).dblclick(
            function( evt ) {
                $(this).toggle_class( 'flag' );
                evt.stopPropagation();
            }
        );
        */
        $( '.diff.display' )
            .on( 
                'dblclick', 
                '.line-of-code', 
                null,
                function( evt ) {
                    $(this).toggle_class( 'flag' );
                    var diff_display = $( this ).closest( '.diff.display' );

                    if( typeof( storage ) != 'undefined' && storage != null ) {
                        if( storage.local_storage ) {

                            var html = diff_display.wrap( $('<div/>') ).parent().html();
                            diff_display.unwrap();

                            storage.set_item( 
                                'Scratch',
                                'Highlights',
                                diff_display.data( 'file' ),
                                diff_display.data( 'commit' ),
                                html
                            );
                        }
                    }

                    evt.stopPropagation();
                }
            )
        ;

    },
    focus_difference: function( evt ) {
        var handle = this;

        var diffs = $('div.diff span.diff').removeClass( 'seen' ).removeClass( 'current' );

        for( var i = 0; i < diffs.length; i++ ) {
            if( diffs.get( i ) == handle ) {
                if( i > 0 ) {
                    for( var j = 0; j <= i && j < diffs.length; j++ ) {
                        $( diffs.get( j ) ).addClass( 'seen' );
                    }
                }

                break;
            }
        }

        $(handle).addClass( 'current' );
    },
    setup: function() {

        $('body').on( 'click', 'nav a.first',           diff.scroll_to_next_difference );
        $('body').on( 'click', 'nav a.previous',        diff.scroll_to_prev_difference );
        $('body').on( 'click', '.diff.display .diff',   diff.focus_difference );

        $('input.checkbox-enable-adds').change( 
            function() {
                if( this.checked ) {
                    $( '#diff' ).addClass( "adds" );
                } else {
                    $( '#diff' ).removeClass( "adds" );
                }

                diff.diff_count();
            }
        );

        $('input.checkbox-enable-removes').change( 
            function() {
                if( this.checked ) {
                    $( '#diff' ).addClass( "removes" );
                } else {
                    $( '#diff' ).removeClass( "removes" );
                }

                diff.diff_count();
            }
        );

        diff.diff_count();

        $('input.checkbox-enable-adds').trigger( 'change' );
        $('input.checkbox-enable-removes').trigger( 'change' );


        $('body.giterary').keypress(
            function( event ) {

                if( 
                    !$('input#uname').is(':focus')  && 
                    !$('input#pass').is(':focus')   && 
                    !$('input#term').is(':focus')   &&
                    !$('input#quick-nav').is(':focus')   &&
                    !$('select.other-dropdown').first().is(':focus')
                ) {

                    var keyCode = ( event.keyCode ? event.keyCode : event.which );

                    switch ( keyCode ) {
                        case 110:
                            $('a.first').trigger( 'click' );
                            break;
                        case 78:
                        case 80:
                        case 112:
                            $('a.previous').trigger( 'click' );
                            break;

                        case 97:
                            $('input.checkbox-enable-adds').trigger( 'click' );
                            break;
                        case 115:
                            $('input.checkbox-enable-removes').trigger( 'click' );
                            break;
                        case 101:
                            window.location = $('.edit-latest').attr( 'href' );
                            break;

                        default:
                            console.log( keyCode );
                            break;
                    }
                }
            }
        );

        diff.setup_highlight();


    }
};

$(document).ready(
    function() {
        diff.setup();
    }
)

