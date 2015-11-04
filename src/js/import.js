var giterary_import = {
    setup: function() {
        $('#import-source').when_settled( 
            giterary_import.convert,
            200,
            null,
            'change keyup'
        );

        // Bootstrap
        giterary_import.convert();
    },
    tags_to_strip:  [
        'o',
        'div',
        'span',
        '!--'
    ],
    process: function( html  ) {
        html = giterary_import.strip( html );
        html = giterary_import.whitespace( html );

        return html;
    },
    whitespace: function( html ) {
        // return html.replace( /(\&nbsp;)+/g, ' ' );
        return html;
    },
    strip: function( html ) {
        for( var i  in giterary_import.tags_to_strip ) {
            var tag = giterary_import.tags_to_strip[i];

            var regex = new RegExp( '<\/?' + tag + '[^>]*>', 'g' );
            // console.log( regex );
            html = html.replace( regex, '' )
        }

        return html;
    },
    convert: function() {
        console.log( 'converting' );

        if( !toMarkdown ) {
            console.log( 'unable to convert, markdown converter did not load' );
            return;
        } 

        $('#import-target').text( 
            giterary_import.process( 
                toMarkdown(
                    $('#import-source').html()
                )
            ).trim()
        );
    }
};
