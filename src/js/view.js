var view  = {
    meta_toggle:    function() {

        $('.view.container').toggle_class( 'meta-on meta-off' );
    },
    handle_toc_click: function( e ) {

        var el = $(e.target);
        var href = el.attr( 'href' ).replace( /^.*#/, '' );
        href = decodeURIComponent( href );

        e.preventDefault();

        $(window).scrollTo( 
            'a[name="' + href + '"]',
            0,
            {
                offset: {
                    top:    ( -1*( $('.meta.pane').height() + 20 ) ),
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
                    top:    ( -1*( $('.meta.pane').height() + 20 ) ),
                    left:   0
                }
            }
        );

    },
    setup:  function() {
        view.setup_decorations();
        view.setup_toc();
        view.setup_annotations();
        view.setup_refs();
        view.setup_keypress();
        view.setup_other_dropdown();
        view.setup_tables();
        view.setup_highlight();
        view.setup_dialog();

        $('body')
            .on( 'click',    '.view.container .meta.toggle a.toggle',                       view.meta_toggle                )
            .on( 'click',    '.view.container .meta.container .annotations a.annotation',   view.handle_annotation_click    )
            .on( 'click',    '.view.container .meta.container .toc a',                      view.handle_toc_click           )
        ;

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
    setup_highlight: function() {
        $( '.view.file.markdown p, .view.file.markdown li, .view.file.markdown pre' ).dblclick(
            function( evt ) {
                $(this).toggle_class( 'flag' );
                evt.stopPropagation();
            }
        );

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
                    !$('select.other-dropdown').first().is(':focus')
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
                            $('a#edit-link').each( function(k,v) { window.location = $(v).prop('href'); } );
                            break;
                        case 104:
                            $('a#history-link').each( function(k,v) { window.location = $(v).prop('href'); } );
                            break;
                        case 105:
                            $('a#directory-link').each( function(k,v) { window.location = $(v).prop('href'); } );
                            break;
                        case 111:
                            $('select.other-dropdown').first().focus().click();
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
    }
};

$(document).ready(
    function() {
        view.setup();
    }
);
