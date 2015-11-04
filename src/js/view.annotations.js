
$(document).ready( 
    function() {

        // menu to document
        $(".annotations-container dt a").each( 
            function(k,v) {
                $(this).mouseover( 
                    function( e ) {
                        $('.view.file a[name=' + $(this).attr('href').replace('#','') + ']').addClass( 'highlight' );
                    }
                )
                .mouseout( 
                    function( e ) {
                        $('.view.file a[name=' + $(this).attr('href').replace('#','') + ']').removeClass( 'highlight' );
                    } 
                );
            } 
        );

        // Document to menu
        $(".view.file annotate").each( 
            function(k,v) {
                $(this).click(
                    function( e ) {
                        $(".enable-annotations .annotations-container dt a[href=#" + $(v).attr('name') + ']').addClass('highlight').focus();
                    }
                ).mouseout(
                    function( e ) {
                        $(".enable-annotations .annotations-container dt a[href=#" + $(v).attr('name') + ']').removeClass('highlight').blur();
                    }
                )
            }
        )
    } 
);
