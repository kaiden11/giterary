var view  = {
    meta_toggle:    function() {

        $('.view.container').toggle_class( 'meta-on meta-off' );
    },
    show_highlights: function( file, commit ) {
        if( typeof( layout ) == 'undefined' || layout == null ) {
            return;
        }

        if( typeof( storage ) == 'undefined' || storage == null ) {
            return;
        }

        if( typeof( file ) == 'undefined' || file == null || file == "" ) {
            return;
        }

        if( typeof( commit ) == 'undefined' || commit == null || commit == "" ) {
            commit = false;
        }

        var h = storage.get_item( 
            'Scratch', 
            'Highlights',
            file,
            commit
        );

        if( h == null ) {

            var search = storage.search( 
                'Scratch', 
                'Highlights',
                file
            );

            if( search.length <= 0 ) {
                layout.modal(
                    file,
                    "No highlights exist for this file or file version."
                );
            } else {
                h = storage.get_item( 
                    search[ 0 ][ 0 ],
                    search[ 0 ][ 1 ],
                    search[ 0 ][ 2 ],
                    search[ 0 ][ 3 ]
                );

                layout.modal(
                    file,
                    h
                );
            }

        } else {
            layout.modal(
                file,
                h
            );
        }

    },
    handle_toc_click: function( e ) {

        var el = $(e.target);
        var href = el.attr( 'href' ).replace( /^.*#/, '' );
        var dhref = decodeURIComponent( href );

        e.preventDefault();

        $(window).scrollTo( 
            // Accounting for how browsers might interpret the values
            // of the name (decoded comparison vs. encoded comparison )
            'a[name="' + href + '"], a[name="' + dhref + '"]',
            0,
            {
                offset: {
                    top:    ( -1*( $('.meta nav').height() + 20 ) ),
                    left:   0
                }
            }
        );
    },
    handle_annotation_click: function( e ) {

        var el = $(e.target);
        var href = el.attr( 'href' ).replace( /^#/, '' );

        e.preventDefault();

        $(window).scrollTo( 
            'annotate[name=' + href + ']',
            0,
            {
                offset: {
                    top:    ( -1*( $('.meta nav').height() + 20 ) ),
                    left:   0
                }
            }
        );

    },
    setup:  function( opts ) {

        opts = ( typeof( opts ) == 'undefined' ? {} : opts );

        view.setup_decorations();
        view.setup_toc();
        view.setup_annotations();
        view.setup_refs();
        view.setup_keypress();
        view.setup_other_dropdown();
        view.setup_tables();
        view.setup_template();
        view.setup_highlight();
        // view.setup_selection();
        view.setup_dialog();

        if( opts.highlightify_content ) {
            view.setup_highlightify( opts );
        }

        $('body')
//            .on( 'click',    '.view .meta.toggle a.toggle',                       view.meta_toggle                )
            .on( 'click',    '.view .nav .annotations a.annotation',   view.handle_annotation_click    )
            .on( 'click',    '.view .nav .toc a',                      view.handle_toc_click           )
        ;

        $('.view nav .btn.clickable').btn_clickable();

        /*
        $('.meta.container').css( 'position', 'fixed' );
        var offset          = $('.meta.container').offset();
        var initial_scroll  = $(document).scrollTop();

        $(document).when_settled(
            function() { 
                var scroll_pos = $(document).scrollTop();
                console.log( 'at ' + scroll_pos + ', set offset to ' + ( ( scroll_pos - initial_scroll ) + offset.top )  ); 
                $('.view.container.meta-on .meta.container').offset(
                    {
                        top:    ( ( scroll_pos - initial_scroll ) + offset.top ),
                        left:   offset.left
                    }
                );
            },
            500, 
            null, 
            'scroll'
        );
        */
    },
    setup_dialog: function() {
        $( '.view.file' )
            .mouseout( 
                function() {
                    $('.view.container.enable-decorations' ).removeClass( 'only-dialog' );
                }
            ).when_settled(
                function() {
                    $('.view.container.enable-decorations' ).addClass( 'only-dialog' );
                },
                50,
                '.dialog',
                'mouseover'
            )
        ;
        
    },
    random_paragraph: function() {
        var ps = $('.view.file p' );

        // var m = $('#randomize-modal');

        $('.random').removeClass( 'random' );

        var f = function() { 
            return $( 
                ps.get( 
                    Math.floor( 
                        Math.random() * ps.length 
                    ) 
                ) 
            );
        };

        var p = f();

        while( p.html() == "" ) {
            p = f();
        }

        p.addClass( 'random' );

        $(window).scrollTo( 
            p,
            0,
            {
                offset: {
                    top:    ( -1*( $('.meta nav').height() + 100 ) ),
                    left:   0
                }
            }
        );

        if( typeof( layout ) != 'undefined' && layout != null && layout.modal != null ) {
            layout.modal( 
                'Randomly selected paragraph ' + '<a class="btn btn-primary" href="javascript:view.random_paragraph()">Another!</a>',
                p.html(),
                {
                    keyboard:   true
                }
            );
        }
    },
    setup_highlight: function() {
        $( '.view.file.markdown' )
            .on( 
                'dblclick', 
                'p, li, pre', 
                null,
                function( evt ) {
                    
                    $(this).toggle_class( 'flag' );
                    var view_file = $( this ).closest( '.view.file' );

                    if( typeof( storage ) != 'undefined' && storage != null ) {
                        if( storage.local_storage ) {

                            var html = view_file.wrap( $('<div/>') ).parent().html();
                            view_file.unwrap();

                            storage.set_item( 
                                'Scratch',
                                'Highlights',
                                view_file.data( 'file' ),
                                view_file.data( 'commit' ).substr( 0, 6 ),
                                html
                            );
                        }
                    }

                    evt.stopPropagation();
                }
            )
        ;
    },
    setup_highlightify: function( opts ) {

        if( opts && opts.highlightify_content ) {

            if( typeof( csv ) != 'undefined' && csv.str_getcsv ) {


                var $paragraphs = $( '.view.file p' );

                var top = function() {

                    // http://stackoverflow.com/questions/3464876/javascript-get-window-x-y-position-for-scroll
                    var doc = document.documentElement;
                    var ret = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

                    return ret;
                };

                var visible = function() {
                    return top() + window.innerHeight;
                };

                $( document ).when_settled( 
                    function() {

                        $paragraphs.each( function( k, p ) {

                            p = $(p);

                            if( top() <= p.offset().top && p.offset().top <= visible() ) {
                                p.addClass( 'visible' ); 
                            } else  {
                                p.removeClass( 'visible' ); 
                            }

                            if( p.hasClass( 'visible' ) && !p.hasClass( 'highlighted' ) ) {
                                p.addClass( 'highlighted' );

                                opts.highlightify_content.split( /\r?\n/ ).forEach( function( v, i ) {

                                    v = v.trim();
                                    if( v == '' ) {
                                        return;
                                    }

                                    var a = csv.str_getcsv( v );
                                    var pattern = a[0].trim();
                                    var classes = false;
                                    if( a.length > 1 ) {
                                        classes = a.slice( 1 ).map( function( b ) { return b.trim(); } );
                                    }

                                    if( pattern.match( /^\/.+\/[i]*$/ ) ) {
                                        pattern = eval( pattern ); // Oh man, this is terrible.
                                    } else {
                                        pattern = new RegExp( '/' + RegExp.quote( pattern ) + '/i' );
                                    }

                                    p.highlightify( pattern, classes );
                                } );
                            }
                        });

                    },
                    100,
                    null,
                    'scroll'
                );



                /*
                */
            }
        }

        return;
    },
    setup_selection: function() {

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

        var store_selected = function() {
            if( typeof( storage ) != 'undefined' && storage != null ) {
                if( storage.local_storage ) {

                    var view_file = $( '.view.file' );

                    var selected = get_selected_text();

                    if( selected == null || selected == '' ) {
                        // check if the add-to-scratch data element was
                        // set, if so, try to do that
                        selected = $('#add-to-scratch').data( 'selected' );
                    }

                    if( selected != "" ) {
                        storage.set_item( 
                            'Scratch',
                            'Selected',
                            view_file.data( 'file' ),
                            view_file.data( 'commit' ).substr( 0, 6 ),
                            new Date().toLocaleTimeString(),
                            selected.substr( 0, 20 ),
                            selected
                        );
                    }
                }
            }
        };

        
        $(document).when_settled(
            function() {
                var selected_text = get_selected_text();

                if( selected_text != null && selected_text != "" ) {
                    $('#scratch-activity')
                        .addClass( 'selection' )
                        .removeClass( 'no-selection' )
                        .find( '#add-to-scratch-word-count' )
                            .html( '(' + wordcount.wordcount_helper( selected_text ) + ' words)' )

                    ;
                } else {
                    $('#scratch-activity')
                        .addClass( 'no-selection' )
                        .removeClass( 'selection' )
                        .find( '#add-to-scratch-word-count' )
                            .html( '' )
                    ;
                }

                // Because we lose focus on the click
                $('#add-to-scratch').data( 
                    'selected', 
                    selected_text 
                );
            },
            100,
            null,
            'selectionchange'
        );

        $('#scratch-activity').on( 'click', '#add-to-scratch', function() {
            store_selected();
        });


    },
    setup_decorations:  function() {
        var decoration_func = function() {
            if( this.checked ) {
                $( '#'+$(this).val() ).addClass( "enable-decorations" );
            } else {
                $( '#'+$(this).val() ).removeClass( "enable-decorations" );
            }
        };

        $('input.checkbox-enable-decorations').change( 
            decoration_func
        );

        $('li#decoration-list-item').mouseover( 
            function() {
                $(this).find('.checkbox-enable-decorations').each( 
                    function(k,v) {
                        $( '#' + $(v).val() ).addClass( "enable-decorations" );
                    }
                )
            }
        ).mouseout(
            function() {
                $(this).find('.checkbox-enable-decorations').each( 
                    function(k,v) {
                        if( !$(v).is(':checked') ) {
                            $( '#' + $(v).val() ).removeClass( "enable-decorations" );
                        }
                    }
                )
            }
        );

        $('input.checkbox-enable-decorations').each( function() {
            decoration_func.call( this );
        });
    },
    setup_refs:  function() {
        var ref_func = function( clazz ) {
            if( this.checked ) {
                $( '#'+$(this).val() ).addClass( clazz );
                $('.view.container').addClass('meta-on').removeClass('meta-off');
            } else {
                $( '#'+$(this).val() ).removeClass( clazz );
                $('.view.container').addClass('meta-off').removeClass('meta-on');
            }
        };

        $('input.checkbox-enable-file-refs').change( 
            function() { ref_func.call( this, 'enable-file-refs' ); }
        );

        $('li#file-refs-list-item').mouseover( 
            function() {
                $(this).find('.checkbox-enable-file-refs').each( 
                    function(k,v) {
                        $( '#' + $(v).val() ).addClass( "enable-file-refs" );
                    }
                )
            }
        ).mouseout(
            function() {
                $(this).find('.checkbox-enable-file-refs').each( 
                    function(k,v) {
                        if( !$(v).is(':checked') ) {
                            $( '#' + $(v).val() ).removeClass( "enable-file-refs" );
                        }
                    }
                )
            }
        );


        $('input.checkbox-enable-file-refs').each( function() {
            ref_func.call( this, 'enable-file-refs' );
        });
    },

    setup_toc:  function() {
        var toc_func = function( clazz ) {
            if( this.checked ) {
                $( '#'+$(this).val() ).addClass( clazz );
                $('.view.container').addClass('meta-on').removeClass('meta-off');
            } else {
                $( '#'+$(this).val() ).removeClass( clazz );
                $('.view.container').addClass('meta-off').removeClass('meta-on');
            }
        };

        $('input.checkbox-enable-toc').change( 
            function() { toc_func.call( this, 'enable-toc' ); }
        );

        $('li#toc-list-item').mouseover( 
            function() {
                $(this).find('.checkbox-enable-toc').each( 
                    function(k,v) {
                        $( '#' + $(v).val() ).addClass( "enable-toc" );
                    }
                )
            }
        ).mouseout(
            function() {
                $(this).find('.checkbox-enable-toc').each( 
                    function(k,v) {
                        if( !$(v).is(':checked') ) {
                            $( '#' + $(v).val() ).removeClass( "enable-toc" );
                        }
                    }
                )
            }
        );


        $('input.checkbox-enable-toc').each( function() {
            toc_func.call( this, 'enable-toc' );
        });
    },
    setup_annotations: function() {
        var annotations_func = function() {
            if( this.checked ) {
                $( '#'+$(this).val() ).addClass( "enable-annotations" );
                $('.view.container').addClass('meta-on')
                $('.view.container').removeClass('meta-off');
            } else {
                $( '#'+$(this).val() ).removeClass( "enable-annotations" );
                $('.view.container').addClass('meta-off');
                $('.view.container').removeClass('meta-on');
            }
        };

        $('input.checkbox-enable-annotations').change( 
            annotations_func
        );

        $('li#annotations-list-item').mouseover( 
            function() {
                $(this).find('.checkbox-enable-annotations').each( 
                    function(k,v) {
                        $( '#' + $(v).val() ).addClass( "enable-annotations" );
                    }
                )
            }
        ).mouseout(
            function() {
                $(this).find('.checkbox-enable-annotations').each( 
                    function(k,v) {
                        if( !$(v).is(':checked') ) {
                            $( '#' + $(v).val() ).removeClass( "enable-annotations" );
                        }
                    }
                )
            }
        );

        $('input.checkbox-enable-annotations').each( function() {
            annotations_func.call( this );
        });
    },
    setup_keypress: function() {
        $(document).keypress(
            function( event ) {

                if( 
                    !$('input#uname').is(':focus')  && 
                    !$('input#pass').is(':focus')   && 
                    !$('input#term').is(':focus')   &&
                    !$('input#quick-nav').is(':focus')   &&
                    !$('#annotator-field-0').is(':focus') &&
                    !$('#annotator-field-1').is(':focus') &&
                    !$('select.other-dropdown').first().is(':focus') &&
                    !$('#snippet-type').first().is(':focus')
                ) {

                    var keyCode = ( event.keyCode ? event.keyCode : event.which );

                    switch ( keyCode ) {
                        case 100:
                            $('input.checkbox-enable-decorations').trigger( 'click' );
                            break;
                        case 116:
                            $('input.checkbox-enable-toc').trigger( 'click' );
                            break;
                        case 97:
                            $('input.checkbox-enable-annotations').trigger( 'click' );
                            break;
                        case 115:
                            $('input.checkbox-enable-file-refs').trigger( 'click' );
                            break;

                        case 101:
                            // $('#edit-link').each( function(k,v) { window.location = $(v).prop('href'); } );
                            $('#edit-link').trigger( 'click' );
                            break;
                        case 104:
                            $('#history-link').trigger( 'click' );
                            break;
                        case 105:
                            $('#directory-link').trigger( 'click' );
                            break;
                        case 114: //
                            view.random_paragraph();
                            break;
                        case 32: //
                            // util.honk();
                            break;

                        default:
                            // console.log( event);
                            break;
                    }
                }
            }
        ); 
    },
    setup_other_dropdown: function() {
        $('select.other-dropdown').change( 
            function() {
                if( $( this ).val() != null && $(this).val() != "" ) {
                    window.location = $( this ).val();
                }
            }
        );
    },
    setup_tables: function() {
        $( '.view.file table.tabulizer' ).tabulizer();

        var cancel_keycombos = function(event){
            event.stopPropagation();
        };

        $('.view.file .template input').keypress( cancel_keycombos );  
        $('.view.file .template input').keydown( cancel_keycombos );  
    },
    setup_template: function() {

        $( 'form.template' ).submit(
            function( evt ) {

                var template_file = $(this).find( '.template-file' );

                if( template_file ) {
                    if( $( template_file ).val() == template_file.data( 'original' ) ) {

                        evt.stopPropagation();
                        alert( 'Please enter a new name for the file you wish to be created with this template' );
                        return false;
                    }
                }

            }
        );
    }

};



