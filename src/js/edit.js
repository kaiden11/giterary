var edit = {
    editor: null,
    initial_position: null,
    extension:  null,
    meta_toggle: function() {
        $('.edit.container').toggle_class( 'meta-on meta-off' );
    },
    preview_toggle: function() {
        $('.edit.container').toggle_class( 'preview-on preview-off' );

        edit.preview();
    },
    meta_key_picker: function() {

        if( navigator.platform.indexOf( "Mac" ) > -1 ) {
            return 'Ctrl';
        }

        if( navigator.platform.indexOf( "Win" ) > -1 ) {
            return 'Alt';
        }

        if( navigator.platform.indexOf( "Lin" ) > -1 ) {
            return 'Alt';
        }

        // Best effort
        return 'Ctrl';
    },
    setup_draft: function() {

        // Prime the draft cache value
        edit.draft_cache = edit.get_content();

        var draft_failure_helper = function( e ) {
            if( !edit.check_draft_success() ) {
                var ans = confirm( 
                    'Your last successful draft was ' + 
                    ( ( ( new Date().getTime() ) - edit.draft_last_success ) / 1000 ) + 
                    ' seconds ago. This might imply that the server has gone down while you were writing. Hit Okay to continue this action, or No to return to the form and find a way to copy your submission elsewhere while Giterary comes back. It is recommended you at least copy and paste your content elsewhere if you\'re afraid of losing your work.' 
                );
        
                if( ans == false  ) {
                    e.preventDefault();
                    return false;
                }
            }
        };

        $('body.giterary')
            .on( 
                'click', 
                '#preview_buton, #commit_button, #commit_and_edit_button', 
                draft_failure_helper
            )
        ;

        setInterval( 
            function() { 
                edit.draft( 
                    $( '#commit_notes' ), 
                    $( '#existing_commit' ), 
                    $( '#file' )
                ) 
            }, 
            10000
        );


        $( edit.editor.getTextArea() )
            .on(
                'change.codemirror',
                edit.maintain_working_time
            )
        ;
    },
    tag_suggest_handler:    function( ) {
        $.ajax(
            'a_tags.php?action=all',
            {
                dataType:   'json'
            }
        ).done( 
            function( data ) {

                var suggestion = '\n';
                
                var content = edit.editor.getValue();

                for( var i = 0; i < data.length; i++ ) {
                    
                    var tag_rx       = new RegExp( '~' + data[i], 'gi' );
                    var content_rx   = new RegExp( data[i], 'gi' );

                    if( content.match( tag_rx ) ) {
                        continue;
                    }

                    if( content.match( content_rx ) ) {
                        suggestion = suggestion +  '~' + data[i] + '\n';
                    }
                }

                edit
                    .editor
                        .replaceRange(
                            suggestion,
                            CodeMirror.Pos(
                                edit.editor.lastLine()
                            )
                        )
                ;


            }
        ).fail(
            function(  ) {
                console.log( 'Error retrieving tags' );
            }
        );

    },
    fullscreen_handler: function( cm ) {

        cm.setOption("fullScreen", !cm.getOption( "fullScreen" ) );

    },
    escape_handler: function( cm ) {
        if( layout && layout.toggle_metas ) {
            layout.toggle_metas();
        }
        
        if( cm.getOption('fullScreen') ) {
            cm.setOption( 'fullScreen', false );
        }
    },
    toggle_editor:  null,
    setup_editor: function( mode, textarea ) {
        // platform combo spec
        var pcs = function( c ) {
            return edit.meta_key_picker() + '-' + c;
        };
        var extra_keys = {
            Esc:        edit.escape_handler,
            // Enter:      'newlineAndIndentContinueMarkdownList',
            Tab:        function(cm) {

                            var indent_unit     = cm.getOption("indentUnit");
                            var line_offset     = cm.getCursor().ch;
                            var spaces_needed   = ( indent_unit - ( line_offset % indent_unit ) );

                            var spaces = Array( spaces_needed + 1).join(" ");
                            cm.replaceSelection(spaces, "end", "+input");
            }
        };



        extra_keys[ pcs( 'U' ) ] = edit.url_handler;
        extra_keys[ pcs( 'L' ) ] = edit.wikilink_handler;
        extra_keys[ pcs( 'B' ) ] = edit.bold_handler;
        extra_keys[ pcs( 'I' ) ] = edit.italic_handler;
        extra_keys[ 'Alt--'    ] = edit.strike_handler;
        extra_keys[ pcs( 'A' ) ] = edit.annotation_handler;
        extra_keys[ pcs( 'C' ) ] = edit.comment_handler;
        extra_keys[ 'Alt-F'    ] = edit.fullscreen_handler;

        extra_keys[ 'Alt-H' ] = function( cm ) {
            cm.foldCode( 
                cm.getCursor()
//              {
//                  minFoldSize:    0
//                  rangeFinder:    CodeMirror.braceRangeFinder
//              }
            );
        };

        edit.editor = CodeMirror.fromTextArea(
            textarea,
            {
                mode:               mode,
                lineNumbers:        true,
                /// theme:              'default',
                theme:              'giterary-codemirror giterary-user',
                smartIndent:        true,
                indentUnit:         4,
                lineWrapping:       true,
                undoDepth:          500,
                historyEventDelay:  1000,
                workDelay:          1000,
                extraKeys:          extra_keys,
                foldGutter:         true,
                gutters:            ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                rulers:             [
                                        {   column: 80,     className:  "eighty"        },
                                        {   column: 120,    className:  "one-twenty"    }
                                    ]
            }
        );

        edit.editor
            .on( 'change',  function( cm, e ) { $(cm.getTextArea()).trigger( 'change.codemirror', cm, e ); } )
        ;

        edit.editor
            .on( 'cursorActivity',  function( cm, e ) { $(cm.getTextArea()).trigger( 'cursorActivity.codemirror', cm, e ); } )
        ;

        $( edit.editor.getTextArea() ).when_settled(
            edit.wordcount,
            250,
            null,
            'change.codemirror cursorActivity.codemirror propertychange input onselect'
        );

        $( edit.editor.getTextArea() ).when_settled(
            edit.preview,
            500,
            null,
            'change.codemirror cursorActivity.codemirror propertychange input'
        );

        // Hide all foldable sections (annotations) initially
        edit.editor.operation(function() { 
            for (var l = edit.editor.firstLine(); l <= edit.editor.lastLine(); ++l) 
                edit.editor.foldCode({line: l, ch: 0}, null, "fold"); 
        }); 
        /*
        $( edit.editor.getTextArea() ).on( 
            'cursorActivity.codemirror',
            null,
            null,
            function( evt ) {
                var pos = edit.editor.getCursor();

                if( edit.editor.getLine( pos.line ).length > 80 ) {
                    edit.editor.addLineClass( pos.line, "wrap", "over-eighty" );
                } else {
                    edit.editor.removeLineClass( pos.line, "wrap", "over-eighty" );
                }
                
                // $('#position').html( 'Line ' + pos.line + ', Char ' + pos.ch );
            }
        );
        */

        // what the hell

        edit.editor.on( 
            'scroll', 
            function( cm ) {
                $('#position').val( cm.getScrollInfo().top );
            }
        );
        
    },
    setup:  function( extension, mode, opts ) {
        console.log( 'Init edit' );

        $('body.giterary')
            .on( 'click',   '.edit.container .meta.container .toggle a',    edit.meta_toggle )
            .on( 'click',   '.edit.container .edit.preview   .toggle a',    edit.preview_toggle )
        ;

        edit.extension = extension;

        edit.setup_editor( 
            mode,
            opts.textarea.get(0)
        );

        edit.toggle_editor = function() {
            if( edit.editor != null ) {
                edit.editor.save();
                edit.editor.toTextArea();
                edit.editor = null;
            } else {
                edit.setup_editor( 
                    mode,
                    opts.textarea.get(0)
                );
            }
        }

        edit.setup_draft();


        edit.setup_wordcount_webworker();
        edit.setup_preview_webworker();

        var then_focus = function( handler ) {
            return function() {
                ( function() {
                    handler( edit.editor );
                    edit.editor.focus();
                } ).defer();
            };
        };

        $('body.giterary')
            .on( 
                'click',   
                'input#cancel_button',
                function() {
       
                    window.location = 'index.php?file=' + opts.cancel_to;
                }
            )
            .on( 'click', '#toggle_editor', function() { edit.toggle_editor();                     }  )
            .on( 'click', '#escape',        then_focus( edit.escape_handler                         ) )
            .on( 'click', '#fullscreen',    then_focus( edit.fullscreen_handler                     ) )
            .on( 'click', '#comment',       then_focus( edit.comment_handler                        ) )
            .on( 'click', '#annotations',   then_focus( edit.annotation_handler                     ) )
            .on( 'click', '#bold',          then_focus( edit.bold_handler                           ) )
            .on( 'click', '#italic',        then_focus( edit.italic_handler                         ) )
            .on( 'click', '#strike',        then_focus( edit.strike_handler                         ) )
            .on( 'click', '#wikilink',      then_focus( edit.wikilink_handler                       ) )
            .on( 'click', '#externallink',  then_focus( edit.url_handler                            ) )
            .on( 'click', '#tag-suggest',   then_focus( edit.tag_suggest_handler                    ) )
        ;

        $('.preview.container .view.file table.tabulizer' ).tabulizer();

        if( opts.initial_position ) {
            edit.editor.scrollTo( null, opts.initial_position );
        }

        $('body.giterary').trigger( 'giterary_edit_ready' );

        console.log( 'Edit init complete' );


    },
    get_position: function() {
        if( edit.editor != null ) {
            return edit.editor.getCursor();
        }

        return $('#edit_contents').get( 0 ).selectionEnd;

    },
    something_selected: function() {

        if( edit.editor != null ) {
            return edit.editor.somethingSelected();
        }

        var ta = $('#edit_contents').get( 0 );

        return ta.selectionStart != ta.selectionEnd;
    },
    get_selected:   function() {
        if( edit.something_selected() ) {
            
            if( edit.editor != null ) {
                return edit.editor.getSelection();
            }

            var ta = $( '#edit_contents' ).get( 0 );

            return ta.value.substring( ta.selectionStart, ta.selectionEnd );
        }

        return false;
    },
    get_content: function() {
        if( edit.editor != null ) {
            return edit.editor.getValue();
        }

        return $('#edit_contents').val();
    },
    wordcount_regex: /\b([\w\']+)\b/gm,
    wordcount_webworker: null,
    setup_wordcount_webworker:  function() {
        if( edit.supports_web_workers() ) {
            edit.wordcount_webworker = util.workout(
                'js/wordcount-ww.js',        
                function( event ) {
                    edit.wordcount_update( event.data );
                }
            );
        }
    },
    supports_web_workers: function() {
        return !!window.Worker;
    },
    wordcount: function( ) {

        if( edit.wordcount_webworker != null ) {

            if( edit.something_selected() ) {
                edit.wordcount_webworker.postMessage( edit.get_selected() );
            } else {
                edit.wordcount_webworker.postMessage( edit.get_content() );
            }
            return;
        }

        return edit.wordcount_helper( edit.get_content() );    
    },
    wordcount_helper: function( content ) {
    
        var word = null;
        var count = 0;
        
        while( ( word = edit.wordcount_regex.exec( content ) ) !== null ) {
            count++;
        }

        edit.wordcount_update( count );
        
    },
    wordcount_update: function( count ) {
        $('.edit.container .wordcount')
            .html( count + " word" + ( count == 1 ? "" : "s" ) )
        ;
    },
    preview:    function() {

        if( $('.edit.container').is('.preview-on' ) ) {
            console.log( 'Generate ' + edit.extension + ' preview...' );
            if( edit.preview_webworker != null ) {
                edit.preview_webworker.postMessage( 
                    {
                        extension:  edit.extension,
                        content:    edit.get_content(), 
                        position:   edit.get_position()
                    } 
                );
            }
        }

        // We only do previews if we know we can support web workers,
        // otherwise overhead is too much.
        return;
    },
    preview_webworker: null,
    setup_preview_webworker:  function() {
        if( edit.supports_web_workers() ) {
            edit.preview_webworker = util.workout(
                'js/preview-ww.js',        
                function( event ) {
                    edit.preview_update( event.data );
                }
            );
        }
    },
    preview_update: function( data ) {

        var content     = data.content;
        var extension   = data.extension;

        // console.log( content );

        var top_offset = 200;
        var fudge = 120;
        if( edit.editor ) {
            var coords = edit.editor.cursorCoords( false, "page" );
            if( coords.top > 0 ) {
                top_offset = Math.max( coords.top - fudge, 0 );
            }
        }

        $('.edit.container.preview-on .edit.preview .preview.container .view.file').html(
            content
        );

        if( extension == "csv" ) {
            $('.preview.container .view.file table.tabulizer' ).tabulizer();
        }

       
        var caret = null;
        if( caret = $('#caret' ).get( 0 ) ) {

            $('.edit.container.preview-on .edit.preview .preview.container .view.file' )
                .scrollTo(
                    caret,
                    200,
                    {
                        offset: {
                            top:    ( -1*( top_offset ) ),
                            left:   0
                        }
                    }
                )
            ;
        }

    },
    bold_handler: function( cm ) {

        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '**' + cm.getSelection() + '**',
                'end',
                '+input'
            );

        } else {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '****',
                'end'
            );

            cm.setCursor( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 2
                }
            );
        }
    },
    comment_handler: function( cm ) {

        var dummy = '(...)'; 
        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            var prev_selection = cm.getSelection();

            cm.replaceSelection( 
                '{' + dummy + '}(' + prev_selection + ')',
                'end',
                '+input'
            );

            cm.setSelection( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 1
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 1 + dummy.length
                }
            );
        }
    },
    strike_handler: function( cm ) {

        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '<strike>' + cm.getSelection() + '</strike>',
                'end',
                '+input'
            );

        } else {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '<strike></strike>',
                'end'
            );

            cm.setCursor( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 8
                }
            );
        }
    },
    italic_handler: function( cm ) {

        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '*' + cm.getSelection() + '*',
                'end',
                '+input'
            );

        } else {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '**',
                'end'
            );

            cm.setCursor( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 1
                }
            );
        }
    },
    url_handler: function( cm ) {

        var dummy = 'http://url'; 
        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            var prev_selection = cm.getSelection();

            cm.replaceSelection( 
                '[' + prev_selection + '](' + dummy + ')',
                'end',
                '+input'
            );

            cm.setSelection( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 1 + prev_selection.length + 2
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 1 + prev_selection.length + 2 + dummy.length
                }
            );
        } else {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '[Linked Text](' + dummy + ')',
                'end'
            );

            cm.setSelection(
                {
                    line:   prev.line,
                    ch:     prev.ch + 14
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 14 + dummy.length
                }
            );
        }
    },
    wikilink_handler: function( cm ) {

        var dummy = 'Wiki/Link'; 
        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '[[' + dummy + '|' + cm.getSelection() + ']]',
                'end',
                '+input'
            );

            cm.setSelection( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 2
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 2 + dummy.length
                }
            );
        } else {
            var prev = cm.getCursor( 'start' );
            cm.replaceSelection( 
                '[[' + dummy + ']]',
                'end'
            );

            cm.setSelection( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 2
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 2 + dummy.length
                }
            );
        }
    },
    annotation_handler: function( cm ) {

        var dummy = 'Annotation...'; 
        if( cm.somethingSelected() ) {
            var prev = cm.getCursor( 'start' );
            var prev_selection = cm.getSelection();

            cm.replaceSelection( 
                '{' + prev_selection + '}(' + dummy + ')',
                'end',
                '+input'
            );

            cm.setSelection( 
                {
                    line:   prev.line,
                    ch:     prev.ch + 1 + prev_selection.length + 2
                },
                {
                    line:   prev.line,
                    ch:     prev.ch + 1 + prev_selection.length + 2 + dummy.length
                }
            );
        }
    },
    draft_cache: '',
    draft_last_success: null,
    check_draft_success: function() {
        if( edit.draft_last_success != null ) {
            if( edit.draft_last_success < ( new Date().getTime() - ( 20*1000 ) ) ) {
                return false;                    
            }
        }

        return true;
    },
    draft_working_seconds:  {},
    maintain_working_time: function() {
        var t = Math.floor( (new Date()).getTime() / 1000 ) + '';
        edit.draft_working_seconds[ t ] = 1;
        // console.log( 'working time: ' + t );
    },
    draft: function( notes, commit, filename ) {


        var draft_contents = null;

        draft_contents      = edit.get_content();
        var draft_notes     = ( typeof( notes )     == 'undefined' ? null : $( notes ).val() );
        var draft_commit    = ( typeof( commit )    == 'undefined' ? null : $( commit ).val() );
        var draft_filename  = ( typeof( filename )  == 'undefined' ? null : $( filename ).val() );


        if( draft_contents != null && draft_commit != null && draft_filename != null && draft_notes != null ) {

            if( draft_contents != '' ) { 
            
                if( draft_contents == edit.draft_cache ) {
                    // Update draft_last_success to show that
                    // we determined that the last time we checked,
                    // saving a draft wasn't necessary.
                    edit.draft_last_success = new Date().getTime();
                } else {

                    // Count the number of unique epoch timestamps (seconds)
                    // during which a keypress was identified.
                    var uniq_draft_work_seconds = 0;
                    for ( k in edit.draft_working_seconds ) if ( edit.draft_working_seconds.hasOwnProperty( k )) uniq_draft_work_seconds++;

                    console.log( new Date() + ' Saving draft' );

                    var r = $.ajax( 
                        "a_draft.php",
                        {
                            type: 'POST',
                            data: {
                                'draft_contents': draft_contents,
                                'draft_notes': draft_notes,
                                'draft_commit': draft_commit,
                                'draft_filename': draft_filename,
                                'draft_work_time': uniq_draft_work_seconds
                            }
                        }
                    ).done(
                        function( data ) { 

                            if ( data.match(/^success$/)) {
                                var d = new Date();
                                $('#draft_msg').html( 'Draft saved at ' + d.toLocaleTimeString() );

                                edit.draft_cache = draft_contents;
                                edit.draft_last_success = d.getTime();
                                edit.draft_working_seconds = {};

                            } else {
                                if ( data.match(/^no draft to save$/)) {
                                    // Do nothing 
                                } else {
                                    $('#draft_msg').html( 'Unable to save draft..' );
                                }
                            }
                        }
                    ).fail(
                        function() { 
                            // Do nothing
                            $('#draft_msg').html( '***Draft failed to save.***' );
                        }
                    );
                }
            }
        }
    }
};
