var cherrypick = {
    submit: function() {
        cherrypick.to_output();
        $('form#edit').submit();
    },
    clear: function() {
        $('div.cherrypick span.diff.add').removeClass( 'cancel' );
        $('div.cherrypick span.diff.remove').addClass( 'cancel' );
        $('div.cherrypick span.diff').addClass( 'unseen' );
    },
    to_preview: function() {
        $('div#cherrypick-preview').empty();
        
        $('div#cherrypick-preview').append(
            $('div#cherrypick span.line-of-code').clone().filter(
                function(k,v) {
                     
                    var removed_cancel = false;
                    $(v).find('span.diff.cancel').each( 
                        function(a,b) { 
                            $(b).remove(); removed_cancel = true; 
                        } 
                    );
                    $(v).find('span.diff').addClass('revised');

                    if( $(v).children().length <= 0 && removed_cancel ) {
                        // Take this line out if it's been "canceled"
                        return false;
                    } else {
                        // Nothing, we're fine.
                        return true;
                    }
                }
            )
        );
    },
    to_output: function() {
        $('textarea#cherrypick-output').val(
            $('div.cherrypick-diff span.line-of-code').clone().filter(
                function(k,v) {

                    var removed_cancel = false;
                    $(v).find('span.diff.cancel').each( 
                        function(k,j) { 
                            $(j).remove(); 
                            removed_cancel = true; 
                        } 
                    );
                    $(v).find('span.diff').contents().unwrap();

                    if( $(v).html().length <= 0 && removed_cancel ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            ).map( 
                function(k,v) {
                    return cherrypick.htmlDecode( $(v).html().replace( '&nbsp;', ' ' ) );
                }
            ).toArray().join('')
        );
    },
    htmlEncode: function(value){
        if (value) {
            return jQuery('<div />').text(value).html();
        } else {
            return '';
        }
    },
    htmlDecode: function(value) {
        if (value) {
            return $('<div />').html(value).text();
        } else {
            return '';
        }
    },
    next_diff: function() {
        if( $('div.cherrypick-diff span.diff.unseen').length <= 0 ) {
            $('div.cherrypick-diff span.diff').addClass( 'unseen' );
        }

        $(document).scrollTo( $('div.cherrypick-diff span.diff.unseen').first().removeClass('unseen') );
    },
    toggle_subtractions: function() {
        $('span.diff.remove').each( function( k, v ) {
            $(v).trigger( 'click' );
        } );
    },
    toggle_additions: function() {
        $('span.diff.add').each( function( k, v ) {
            $(v).trigger( 'click' );
        } );
    }
};

$(document).ready( 
    function() {

        $('body.giterary').on(
            'click',    
            '.cherrypick.container .meta.container .toggle a', 
            function() { 
                $('.cherrypick.container').toggle_class( 'meta-on meta-off' ); 
            } 
        );

        $('div.cherrypick-diff span.diff.add, div.cherrypick-diff span.diff.remove').click(
            function() {
                if( $(this).is('.cancel') ) {
                    $(this).removeClass( 'cancel' );
                } else {
                    $(this).addClass( 'cancel' );
                }

                $(this).removeClass( 'unseen' );

                cherrypick.to_preview();
            }
        );

        $('div.cherrypick-diff span.diff.remove').addClass( 'cancel' );
        $('div.cherrypick-diff span.diff').addClass( 'unseen' );

        cherrypick.to_preview();

        $('textarea#cherrypick-output').css( 'height', $('div#cherrypick').css( 'height' ) );
    }
);
