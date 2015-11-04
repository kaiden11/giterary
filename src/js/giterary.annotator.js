var giterary_annotator = {
    init: function( selector, docname, prefix ) {

        if( !$ || !$( selector ).annotator ) {
            console.log( "Unable to initialize AnnotatorJS" );
        }

        console.log( selector );
        
        $(selector).annotator()
            .annotator(
                'addPlugin',
                'Tags',
                {}
            )
            .annotator(
                'addPlugin',
                'Draft',
                {}
            )
            .annotator(
                'addPlugin', 
                'Store', 
                {
                    prefix: prefix,
                    annotationData: {
                      'uri': docname
                    },
                    loadFromSearch: {
                        'limit':    100,
                        'uri':      docname
                    }
                }
            )
        ;

        var scan_for_source = function() {
            $('.annotator-hl').each( function( k, v ) {
                $(v).removeClass( 'draft' ).removeClass( 'file' );
                $(v).addClass( $(v).data( 'annotation' ).source );
            } );
        };

        setInterval(
            scan_for_source,
            1000
        );

    }
};
