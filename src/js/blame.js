(function() {
    var blame = {
        meta_toggle: function() {
            $('.blame.container').toggle_class( 'meta-on meta-off' );

        },
        setup: function() {
            $('body.giterary')
                .on( 'click',   '.blame.container .meta.container .toggle a', blame.meta_toggle )
            ;

            $('input.checkbox-enable-wrapping').change( 
                function() {
                    if( this.checked ) {
                        $( '#gen-blame' ).addClass( "wrap" );
                    } else {
                        $( '#gen-blame' ).removeClass( "wrap" );
                    }
                }
            );
        }
    };

    $(document).ready( 
        function() {
            blame.setup();
        }
    );
})();
