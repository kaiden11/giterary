var giterary_import = {
    setup: function() {
        $('#import-source').when_settled( 
            giterary_import.convert,
            200,
            null,
            'change keyup'
        );

        $('#import-source').bind( 'paste', giterary_import.on_paste );

        // Bootstrap
        giterary_import.convert();
    },
    tags_to_strip:  [
        'o',
        'div',
        'span',
        '!--'
    ],
    on_paste: function( e ) {

        var data = ( e.clipboardData ||  e.originalEvent.clipboardData );

        // Handling *very* basic data URL image pasting
        if( data.items.length == 1 ) {

            if( data.items[0].type.indexOf( "image/" ) == 0 ) {
                var blob = data.items[0].getAsFile();
                var reader = new FileReader();

                var new_image = $('<img/>');

                reader.onload = function(event){

                    new_image.attr( 'src', event.target.result );

                    $('#import-source').append( new_image );

                }; // data url!

                reader.readAsDataURL( blob );
            }
        }
    },
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
