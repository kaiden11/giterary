;(function( $ ) {

    $(document).ready(
        function() {
            $("#tag-search").text_suggest( "tag name..." );

            $('body').on( 
                'click', 
                '.dir-view.container .meta.container .toggle a',
                function() {
                    $('.dir-view.container').toggle_class( 'meta-on' );
                }
            );
        }
    );


})( jQuery );
