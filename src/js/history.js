var history = {
    meta_toggle:    function() {
        $('#gen_history').toggle_class( 'meta-on meta-off' );
    },
    setup:  function() {

        $('body.giterary').on( 'click', '.history .meta .toggle a', history.meta_toggle );

        var assoc_func = function() {
            if( this.checked ) {
                $( '#gen-history' ).addClass( "enable-assoc" );
            } else {
                $( '#gen-history' ).removeClass( "enable-assoc" );
            }
        };

        var alias_func = function() {
            if( this.checked ) {
                $( '#gen-history' ).addClass( "enable-alias" );
            } else {
                $( '#gen-history' ).removeClass( "enable-alias" );
            }
        };


        $('input.checkbox-enable-assoc').change( 
            assoc_func
        );

        $('input.checkbox-enable-alias').change( 
            alias_func
        );


        $('li#assoc-list-item').mouseover( 
            function() {
                $( '#gen-history' ).addClass( "enable-assoc" );
            }
        ).mouseout(
            function() {
                $('input.checkbox-enable-assoc').trigger( 'change' );
            }
        );

        $('li#alias-list-item').mouseover( 
            function() {
                $( '#gen-history' ).addClass( "enable-alias" );
            }
        ).mouseout(
            function() {
                $('input.checkbox-enable-alias').trigger( 'change' );
            }
        );


        $(document).keypress(
            function( event ) {

                if( 
                    !$('input#uname').is(':focus')  && 
                    !$('input#pass').is(':focus')   && 
                    !$('input#term').is(':focus')   &&
                    !$('input#quick-nav').is(':focus')   &&
                    !$('select.other-dropdown').first().is(':focus')
                ) {

                    // alert( event.keyCode );
                    switch ( event.keyCode ) {
                        case 97:    // a
                            $('input.checkbox-enable-assoc').trigger( 'click' );
                            break;
                        case 108:    // l
                            $('input.checkbox-enable-alias').trigger( 'click' );
                            break;
                        default:
                            break;
                    }
                }
            }
        );


    }
};


$(document).ready( 
    function() {
        history.setup();
    }
);
