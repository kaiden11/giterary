var show = {


    image_extension_regex: /(.+)\.(jpg|png|gif)$/i,
    gifv_extension_regex: /(.+)\.(gifv)$/i,
    rewrite_clickable_url_images: function() {

        $('.commit .notes .Responses a.clickable.url').each( function(k,v) {

            var a = $(v);

            var href = '';

            if( href = a.attr( 'href') ) {

                var match = [];

                if( match = show.image_extension_regex.exec( href ) ) {

                    a.append(
                        $('<br>'),
                        $('<img>')
                            .attr( 
                                'src',
                                href
                            )
                    );

                    
                    
                    return;
                }

                show.image_extension_regex.lastIndex = 0;


                if( match = show.gifv_extension_regex.exec( href ) ) {


                    a.append(
                        $('<br>'),
                        $('<video>')
                            .attr( 'autoplay', 'autoplay' )
                            .attr( 'preload', 'auto' )
                            .attr( 'controls', 'controls' )
                            .attr( 'muted', 'muted' )
                            .append( 
                                $('<source>')
                                    .attr( 'src',   match[1] + '.mp4' )
                                    .attr( 'type',  'video/mp4' )
                            )
                    );


               
                    return;

                }

                show.gifv_extension_regex_text.lastIndex = 0;
            }


        });

    },
    setup:  function() {
        show.rewrite_clickable_url_images();
    }
};


$(document).ready( show.setup );
