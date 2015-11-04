
var snippets = {
    setup: function() {

        // Mini word-counter
        var wordcount = {
            wordcount_regex: /\b([\w\']+)\b/gm,
            wordcount_helper: function( content ) {
            
                var word = null;
                var count = 0;
                
                while( ( word = wordcount.wordcount_regex.exec( content ) ) !== null ) {
                    count++;
                }

                wordcount.wordcount_regex.lastIndex = 0;
        
                return count;
            }
        };

        var get_selected_text = function() {
            var text = "";
            if (window.getSelection) {
                text = window.getSelection().toString();
            } else if (document.selection && document.selection.type != "Control") {
                text = document.selection.createRange().text;
            }
            return text;
        };

        var getTag = function(node) {
            while (node) {
                if( node.nodeType == 1 ) {
                    return node;
                }
                node = node.parentNode;
            }
            return null;
        }

        var get_selected_context = function() { 

            var paragraphs = function() {
                var sel = null, range = null;
                if (window.getSelection) {
                    sel = window.getSelection();
                    if (sel.rangeCount) {
                        range = sel.getRangeAt(0);
                    }
                } else if ( (sel = document.selection) && sel.type != "Control") {
                    range = sel.createRange();
                }

                if( range ) {

                    var begin = null, end = null;
                    begin = getTag( range.startContainer );

                    if( begin.tagName.toUpperCase() != "P" ) {
                        begin = $(begin).closest( 'p' );
                    } else {
                        begin = $(begin);
                    }

                    if( begin.length > 0 ) {

                        end = getTag( range.endContainer );

                        if( end.tagName.toUpperCase() != "P" ) {
                            end = $(end).closest( 'p' );
                        } else {
                            end = $(end);
                        }

                        if( end.length > 0  ) {

                            // Now we have both a beginning and an end. We
                            // should verify that they are both siblings.

                            if( $.contains( begin.parent().get( 0 ), end.get( 0 ) ) ) {

                                var b = begin.index();
                                var e = end.index();

                                if( b == e ) {
                                    return [ begin.get( 0 ) ];
                                } else {
                                    var ret = [];

                                    while( begin.length > 0 ) {
                                    
                                        ret.push( begin.get( 0 ) );

                                        begin = begin.next();

                                        if( begin.index() == e ) {
                                            break;
                                        }
                                    }
                                    
                                    ret.push( end.get( 0 ) );

                                    return ret;

                                }
                            }
                        }
                    }
                }


                return null;
            }

            var p = paragraphs();

            if( p != null && p.length > 0 ) {

                var ret = '';

                for( var i = 0; i < p.length; i++ ) {
                    ret += $(p[i]).text() + "\n\n";
                }
                return ret;
            }
            
            return null;
        }

        var store_selected = function( ) {

            var view_file = $( '.view.file' );

            var selected = get_selected_text();

            if( selected == null || selected == '' ) {
                // check if the add-to-snippets data element was
                // set, if so, try to do that
                selected = $('#add-to-snippets').data( 'selected' );
            }

            
            var context = get_selected_context();

            if( context == null || context == '' ) {
                // check if the add-to-snippets data element was
                // set, if so, try to do that
                context = $('#add-to-snippets').data( 'context' );
            }

            var snippet_type = $('#snippet-type').val()

            if( selected != "" ) {
                if( typeof( storage ) != 'undefined' && storage != null ) {
                    if( storage.local_storage ) {
                        storage.set_item( 
                            'Snippets',
                            'Selected',
                            view_file.data( 'file' ),
                            view_file.data( 'commit' ).substr( 0, 6 ),
                            new Date().toLocaleTimeString(),
                            selected.substr( 0, 20 ),
                            selected
                        );
                    }
                }

                var r = $.ajax( 
                    "a_snippet.php",
                    {
                        type: 'POST',
                        data: {
                            'file':     view_file.data( 'file' ),
                            'commit':   view_file.data( 'commit' ),
                            'snippet':  selected,
                            'context':  context,
                            'type':     snippet_type
                        }
                    }
                ).done(
                    function( data ) { 
                        if ( data.match(/^success$/)) {
                            var d = new Date();
                            $('#error').html( 
                                'Snippet saved.'
                            );

                            setTimeout(
                                function() {
                                    $('#error').html( 
                                        ''
                                    );
                                },
                                3000
                            );

                        } else {
                            $('#error').html( 
                                'Unable to save snippet to server.' 
                            );
                        }


                    }
                ).fail(
                    function() { 
                        // Do nothing
                        $('#error').html( 'Unable to save snippet to server' );
                    }
                );

                /*
                $('#snippets-activity')
                    .addClass( 'no-selection' )
                    .removeClass( 'selection' )
                    .find( '#add-to-snippets-word-count' )
                        .html( '' )
                ;
                */
            }

        };

        var hide_snip = function() {
            ( function( ) {

                if( 
                    !$('#snippet-type').is( ':focus' ) 
                ) {
                    $('#snippets-activity')
                        .addClass( 'no-selection' )
                        .removeClass( 'selection' )
                        .find( '#add-to-snippets-word-count' )
                            .html( '' )
                    ;

                    $('#snippet-type').val( '' );

                    $('#add-to-snippets').data( 
                        'selected', 
                        ''
                    );

                    $('#add-to-snippets').data( 
                        'context', 
                        ''
                    );

                }
            } ).defer();
        }

        var show_snip = function( selected_text ) {
            ( function( ) {
                $('#snippets-activity')
                    .addClass( 'selection' )
                    .removeClass( 'no-selection' )
                    .find( '#add-to-snippets-word-count' )
                        .html( '(' + wordcount.wordcount_helper( selected_text ) + ' words)' )

                ;

            } ).defer();
        }
        
        $(document).when_settled(
            function() {
                var selected_text = get_selected_text();
                var selected_context = get_selected_context();

                if( selected_text != null && selected_text != "" ) {
                    // Because we lose focus on the click
                    $('#add-to-snippets').data( 
                        'selected', 
                        selected_text 
                    );

                    $('#add-to-snippets').data( 
                        'context', 
                        selected_context 
                    );

                    show_snip( selected_text );
                } 
            },
            100,
            null,
            'selectionchange'
        );


        // $('#snippets-activity').on( 'blur', '#snippet-type', hide_snip );

        $('#snippets-activity').on( 'click', '#add-to-snippets', function() {
            store_selected();
            hide_snip();
        });

        $('#snippets-activity li .snippet-type-preset ').on( 'click', function() {
            $('#snippet-type').val(
                $(this).text().trim()
            );
        });
    }
};

