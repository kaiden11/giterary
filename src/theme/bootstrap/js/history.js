var hist = {
    meta_toggle:    function() {
        $('#gen_history').toggle_class( 'meta-on meta-off' );
    },
    file_prior_dblclick: function( evt ) {
        // var handle = this;
    },
    before_after_click: function( evt ) {

        var handle = this;

        ( function() {
            var name    =   $(handle).find( 'input[type=radio]' ).attr( 'name' );
            var id      =   $(handle).find( 'input[type=radio]' ).attr( 'id' );

            $( '.history .display label.before-after-radio input[name=' + name + ']:not( #' + id + '):checked' ).each( function( k, v ) {
                if( $(v).parent().hasClass( 'active' ) ) {
                    $(v).parent().removeClass( 'active' );
                }

                $(v).attr( 'checked', false );
            } );

            // Check if a before and after are clicked, and if so, redirect.
            var before  =   $('label.before-after-radio.active input[name=commit_before]:checked').attr( 'value' );
            var after   =   $('label.before-after-radio.active input[name=commit_after]:checked').attr( 'value' );

            if( before && after ) {
                window.location = 'diff.php?plain=yes&commit_before=' + before + '&commit_after=' + after;
            }
        } ).defer();
    },
    setup:  function() {

        $('body.giterary').on( 'click', '.history .meta .toggle a', hist.meta_toggle );

        $('.history .btn.clickable').btn_clickable(); 

        $('.history .display label.before-after-radio ').click( hist.before_after_click );

        $('.history .display label.before-after-radio.file-prior').dblclick( hist.file_prior_dblclick );

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
        hist.setup();
    }
);
