var diff = {
    meta_toggle:    function() {
        $('.diff.container').toggle_class( 'meta-on meta-off' );
    },
    scroll_to_next_difference: function() {
        var diffs = $('div.diff span.diff').removeClass( 'current' ).not(':hidden').not( '.seen' );
        
        if( diffs && diffs.length > 0 ) {
            $(window).scrollTo( 
                diffs.get(0),
                200,
                {
                    offset: {
                        top:    ( -1*( $('.meta.pane').height() + 20 ) ),
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
                diff.scroll_to_next_difference();
            }
        }
    },
    setup_highlight: function() {
        $( '.line-of-code' ).dblclick(
            function( evt ) {
                $(this).toggle_class( 'flag' );
                evt.stopPropagation();
            }
        );
    },
    setup: function() {

        $('body').on( 'click', '.diff.container .meta.container .toggle a',             diff.meta_toggle                );
        $('body').on( 'click', '.diff.container .meta.container .activities a.first',   diff.scroll_to_next_difference );

        $('input.checkbox-enable-adds').change( 
            function() {
                if( this.checked ) {
                    $( '#diff' ).addClass( "adds" );
                } else {
                    $( '#diff' ).removeClass( "adds" );
                }
            }
        );

        $('input.checkbox-enable-removes').change( 
            function() {
                if( this.checked ) {
                    $( '#diff' ).addClass( "removes" );
                } else {
                    $( '#diff' ).removeClass( "removes" );
                }
            }
        );

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
                        case 97:
                            $('input.checkbox-enable-adds').trigger( 'click' );
                            break;
                        case 115:
                            $('input.checkbox-enable-removes').trigger( 'click' );
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

