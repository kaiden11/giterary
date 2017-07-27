var edit = {
    editor: null,
    initial_position: null,
    extension:  null,
    existing_wc:  0,
    meta_toggle: function() {
        $('.edit.root').toggle_class( 'meta-on meta-off' );
    },
    preview_toggle: function() {
        $('.edit.root').toggle_class( 'preview-on preview-off' );

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
    md_paste_drop_result: function( result ) {
        var random_sequence = Math.floor( 
                Math.random()*(10000000) + 1 
        ).toString( 16 );

        if( random_sequence.length < 3 ) {
            random_sequence = random_sequence.pad_left( 3, '0' )
        }

        random_sequence = random_sequence.substring( 0, 3 );

        edit.editor.replaceSelection(
            '![image-' + random_sequence + '][]'
        );

        edit.editor.replaceRange(
            '\n' +
            '[image-' + random_sequence + ']: ' + result + '\n',
            { line: Infinity }
        );
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
    timestamp_format: function() {

        var ret = '';

        var a = new Date();

        ret += ( "" + ( 1900+a.getYear()    ) ).pad_left( '4' );
        ret += ( "/" + ( 1+a.getMonth()     ) ).pad_left( '2' );
        ret += ( "/" + ( 1+a.getDate()      ) ).pad_left( '2' );
        ret += ( " " + ( 1+a.getHours()     ) ).pad_left( '2' );
        ret += ( ":" + ( 1+a.getMinutes()   ) ).pad_left( '2' );
        ret += ( ":" + ( 1+a.getSeconds()   ) ).pad_left( '2' );

        return ret;
    },
    timestamp_handler: function( cm ) {
        cm.replaceSelection(
            edit.timestamp_format()
        );
    },
    escape_handler: function( cm ) {
        if( layout && layout.toggle_metas ) {
            layout.toggle_metas();
        }
        
        if( cm.getOption('fullScreen') ) {
            cm.setOption( 'fullScreen', false );
        }
    },
    change_evt:  function( cm, change ) {

        /*
        cm.addLineClass(
            change.from.line,
            'background',
            'mod'
        );
        */
    },
    toggle_editor:  null,
    toggle_wordwrap:  function( e ) {

        if( edit.editor != null ) {
            
            edit.editor.setOption( 
                'lineWrapping', 
                !edit.editor.getOption( 'lineWrapping' )
            );
        }
    },
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
        extra_keys[ pcs( 'J' ) ] = edit.join_handler;
        extra_keys[ 'Alt--'    ] = edit.strike_handler;
        extra_keys[ pcs( 'A' ) ] = edit.annotation_handler;
        extra_keys[ pcs( 'C' ) ] = edit.comment_handler;
        extra_keys[ pcs( 'W' ) ] = edit.toggle_wordwrap;
        extra_keys[ pcs( 'Enter' ) ] = edit.wrap_handler;
        extra_keys[ 'Alt-T'    ] = edit.timestamp_handler;
        extra_keys[ 'Alt-P'    ] = edit.preview_toggle;
        extra_keys[ 'Alt-F'    ] = edit.fullscreen_handler;
        extra_keys[ 'Shift-Alt-Up' ] = edit.select_lines_up;
        extra_keys[ 'Shift-Alt-Down' ] = edit.select_lines_down;

        if( edit.extension == 'markdown' ) {
            extra_keys[ 'Shift-Enter' ] = 'newlineAndIndentContinueMarkdownList';
            // console.log( 'adding markdown list handling' );
        }

        /*
        extra_keys[ 'Alt-H' ] = function( cm ) {
            cm.foldCode( 
                cm.getCursor()
//              {
//                  minFoldSize:    0
//                  rangeFinder:    CodeMirror.braceRangeFinder
//              }
            );
        };
        */

        // Override default "Home" and "End" behavior
        CodeMirror.keyMap.default.End = "goLineRight";
        CodeMirror.keyMap.default.Home = "goLineLeft";

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
                // foldGutter:         true,
                gutters:            [
                                        "CodeMirror-linenumbers"
                                        // , "CodeMirror-foldgutter"
                                    ],
                rulers:             [
                                        {   column: 80,     className:  "eighty"        },
                                        {   column: 120,    className:  "one-twenty"    }
                                    ]
            }
        );

        edit.editor
            .on( 
                'change',  
                function( cm, e ) { 

                    $(cm.getTextArea()).trigger( 
                        'change.codemirror', 
                        [ cm, e ]
                    ); 
                } 
            )
        ;

        edit.editor
            .on(
                'change',
                edit.set_edited_marks
            )
        ;

        edit.editor
            .on( 'cursorActivity',  function( cm, e ) { $(cm.getTextArea()).trigger( 'cursorActivity.codemirror', cm, e ); } )
        ;


        if( CodeMirror.hint ) {

            if( edit.extension != 'css' ) {
                // If hinting is loaded, enable autocomplete
                CodeMirror.commands.autocomplete = function(cm) {
                    cm.showHint(
                        {
                            hint: CodeMirror.hint.auto,
                            completeSingle: false
                        }
                    );
                };
            } else {
                // No-op, as the autocomplete for CSS is infuriating
                CodeMirror.commands.autocomplete = function( cm ) {};
            }
        }

        edit.editor
            .on( 'change',  function( cm, e ) { $(cm.getTextArea()).trigger( 'autocomplete.codemirror', cm, e ); } )
        ;


        edit.editor
            .on( 'change',  edit.change_evt )
        ;

        $( edit.editor.getTextArea() ).when_settled(
            function() {
                if( CodeMirror.commands.autocomplete ) {
                    CodeMirror.commands.autocomplete( edit.editor ); 
                }
            },
            250,
            null,
            'autocomplete.codemirror'
        );

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
        /*
        edit.editor.operation(function() { 
            for (var l = edit.editor.firstLine(); l <= edit.editor.lastLine(); ++l) 
                edit.editor.foldCode({line: l, ch: 0}, null, "fold"); 
        }); 
        */
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

        edit.editor.getWrapperElement().addEventListener( 
            'paste',
            function( e ) {
                // From https://github.com/Rovak/InlineAttachment/blob/master/src/inline-attachment.js

                var result = false,
                  clipboardData = e.clipboardData,
                  items;

                if (typeof clipboardData === "object") {
                    items = clipboardData.items || clipboardData.files || [];

                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];

                        if( item.kind == 'file' ) {
                            result = true;

                            var blob = item.getAsFile();
                            var reader = new FileReader();

                            reader.onload = function(event){

                                if( edit.editor ) {

                                    if( edit.extension == 'markdown' ) {
                                        edit.md_paste_drop_result( event.target.result );
                                    } else {
                                        edit.editor.replaceSelection(
                                            event.target.result
                                        );
                                    }
                                }

                            }; // data url!

                            reader.readAsDataURL( blob );
                        } else {
                            console.log( 'Unknown file kind:' + item.kind );
                        }
                    }
                }

                if (result) { e.preventDefault(); }

                return result;
            },
            false
        );

        edit.editor.on(
            

        );

        edit.editor.on( 
            'drop',
            function( data, e ) {

                var f = function() {
                    var result = false;

                    for (var i = 0; i < e.dataTransfer.files.length; i++) {
                        var file = e.dataTransfer.files[i];

                        result = true;

                        var reader = new FileReader();

                        reader.onload = function(event){

                            if( edit.editor ) {

                                if( edit.extension == 'markdown' ) {
                                    edit.md_paste_drop_result( event.target.result );
                                } else {
                                    edit.editor.replaceSelection(
                                        event.target.result
                                    );
                                }
                            }

                        }; // data url!

                        try {
                            reader.readAsDataURL( file );
                        } catch( ex ) {
                            console.log( ex );
                            result = false;
                        }
                    }

                    return result;
                };

                if( f() ) {
                    e.stopPropagation();
                    e.preventDefault();
                    return true;
                } else {
                    return false;
                }
            }

        );
    },
    setup:  function( extension, mode, opts ) {
        console.log( 'Init edit' );

        $('body.giterary')
            // .on( 'click',   '.edit.container .meta.container .toggle a',    edit.meta_toggle )
            .on( 'click',   '.edit.root #preview-toggle', edit.preview_toggle )
        ;

        edit.extension = extension;

        var original_text = opts.textarea.val();

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
        edit.setup_beforeunload();

        edit.setup_wordcount_webworker();
        edit.setup_preview_webworker();

        // edit.setup_highlight();

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
            .on( 'click', '#toggle_editor',     function() { edit.toggle_editor();                     }  )
            .on( 'click', '#toggle_wordwrap',   then_focus( edit.toggle_wordwrap ) )
            .on( 'click', '#escape',            then_focus( edit.escape_handler                         ) )
            .on( 'click', '#fullscreen',        then_focus( edit.fullscreen_handler                     ) )
            .on( 'click', '#comment',           then_focus( edit.comment_handler                        ) )
            .on( 'click', '#annotations',       then_focus( edit.annotation_handler                     ) )
            .on( 'click', '#bold',              then_focus( edit.bold_handler                           ) )
            .on( 'click', '#italic',            then_focus( edit.italic_handler                         ) )
            .on( 'click', '#strike',            then_focus( edit.strike_handler                         ) )
            .on( 'click', '#wikilink',          then_focus( edit.wikilink_handler                       ) )
            .on( 'click', '#externallink',      then_focus( edit.url_handler                            ) )
            .on( 'click', '#tag-suggest',       then_focus( edit.tag_suggest_handler                    ) )
            .on( 'click', '#insert-timestamp',  then_focus( edit.timestamp_handler                   ) )
        ;

        /*
        if( CodeMirror.MergeView ) {
            if( typeof( layout ) != 'undefined' && layout != null && typeof( layout.modal ) != 'undefined' && layout.modal != null ) {
                $('body.giterary').on( 'click', '#show-diff', function() {

                    var dv = $('<div/>').get( 0 );

                    var ret = CodeMirror.MergeView(
                        dv,
                        {
                            value:                 edit.get_content(),
                            origLeft:              original_text,
                            lineNumbers:            false,
                            mode:                   mode,
                            highlightDifferences:   true,
                            connect:                true,
                            collapseIdentical:      false,
                            allowEditingOriginals:  false,
                            revertButtons:          false
                        }
                    );

                    layout.modal( 'Diff (Original / Modified)', dv );

                    (function() {
                        ret.leftOriginal().refresh();
                        ret.edit.refresh();
                    }).defer();

                });
            }
        }
        */


        $('.edit.root .preview .view.file table.tabulizer' ).tabulizer();

        if( opts.initial_position ) {
            edit.editor.scrollTo( null, opts.initial_position );
        }

        if( opts.talk_append ) {
            edit.editor.setCursor( 
                edit.editor.lastLine(),
                0
            );
        }

        if( opts.existing_wc ) {
            edit.existing_wc = opts.existing_wc;
        } else {
            edit.existing_wc = edit.wordcounter( edit.get_content() );    
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
    before_unload: function( e ) {

        e = e || window.event;
        console.log( e );

        var draft_success   = edit.check_draft_success();
        var draft_contents  = edit.get_content();
        var submitted       = $( 'form#edit-file' ).data( 'submitted' );

        if( !submitted ) {

            if( !draft_success ||  ( draft_contents != '' && draft_contents != edit.draft_cache ) ) {

                var msg = 'You are trying to navigate away from this page, but the last draft for this page was not saved adequately.';

                if(e) {
                    e.returnValue = msg;
                }

                return msg;
            }
        }

        if( 
            navigator.userAgent.indexOf( 'MSIE' ) > -1 
//            || navigator.userAgent.indexOf( 'rv:' ) > -1 
        ) {

            if(e) {
                e.returnValue = undefined;
            }
    
            return undefined;
        }

        return null;

    },
    setup_beforeunload: function() {

        $( 'form#edit-file' ).submit( function( e ) {
            $( this ).data( 'submitted', true );
        } );

        if( window ) {
            window.onbeforeunload = edit.before_unload;
        }
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
    wordcounter: function( content ) {


        var word = null;
        var count = 0;
        
        while( ( word = edit.wordcount_regex.exec( content ) ) !== null ) {
            count++;
        }

        edit.wordcount_regex.lastIndex = 0;

        return count;

    },
    wordcount_helper: function( content ) {

        edit.wordcount_update( 
            edit.wordcounter( content )
        );
        
    },
    wordcount_update: function( count ) {

        var result = count + " word" + ( count == 1 ? "" : "s" );

        if( edit.existing_wc && edit.existing_wc > 0 ) {

            var diff = count - edit.existing_wc;

            if( diff != 0 ) {

                result = result + ' (' + ( diff < 0 ? '' : '+' ) + diff + ')';
            }
        }

        $('.edit.root nav .wordcount')
            .html( result  )
        ;
    },
    preview:    function() {

        if( $('.edit.root').is('.preview-on' ) ) {
            // console.log( 'Generate ' + edit.extension + ' preview...' );
            if( edit.preview_webworker != null ) {
                edit.preview_webworker.postMessage( 
                    {
                        extension:  edit.extension,
                        content:    edit.get_content(), 
                        position:   edit.get_position()
                    } 
                );
            } else {
                console.log( 'null preview webworker' );
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
            if( [ 'markdown','javascript','css','csv' ].indexOf( extension ) != -1 ) {
                var coords = edit.editor.cursorCoords( false, "page" );
                if( coords.top > 0 ) {
                    top_offset = Math.max( coords.top - fudge, 0 );
                }
            }
        }

        $('.edit.root.preview-on .preview .view.file').html(
            content
        );

        if( extension == "csv" ) {
            $('.preview .view.file table.tabulizer' ).tabulizer();
        }

        if( extension == "storyboard" ) {
            $('.cards').sortable(
                {
                    connectWith:    '.cards',
                    tolerance:      'pointer'
                }
            );
        }
       
        var caret = null;
        if( caret = $('#caret' ).get( 0 ) ) {
            // console.log( top_offset );

            $('.preview-on .edit.preview .view.file' )
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
    wrap_handler: function( cm ) {
        var options = {
            column: 80
        };

        if( !cm.somethingSelected() ) {
            cm.wrapParagraph( cm.getCursor(), options );
        } else {
            cm.wrapParagraphsInRange( 
                cm.getCursor( 'anchor' ), 
                cm.getCursor( 'head' ), 
                options 
            );
        }
    },
    select_lines_up: function( cm ) {
        cm.operation(function() {
            var ranges = cm.listSelections();
            for (var i = 0; i < ranges.length; i++) {
                var range = ranges[i];
                if (range.head.line > cm.firstLine())
                    cm.addSelection(CodeMirror.Pos(range.head.line - 1, range.head.ch));
            }
        });
    },
    select_lines_down: function( cm ) {
        cm.operation(function() {
            var ranges = cm.listSelections();
            for (var i = 0; i < ranges.length; i++) {
                var range = ranges[i];
                if (range.head.line < cm.lastLine())
                    cm.addSelection(CodeMirror.Pos(range.head.line + 1, range.head.ch));
            }
        });

    },
    join_handler: function( cm ) {

        var line_count = cm.lineCount();

        if( !cm.somethingSelected() ) {

            var pos = cm.getCursor();

             // Don't try to join past the end of the file
            if( pos.line < (line_count-1) ) {

                var current_line = cm.getLine( pos.line );
                var next_line = cm.getLine( pos.line+1 );

                cm.replaceRange( 
                    ( current_line + " " + next_line ), 
                    { line: pos.line,   ch: 0                   },
                    { line: pos.line+1, ch: next_line.length    }
                );
            }
        } else {
            cm.replaceSelection( 
                cm.getSelection().replace( /\r?\n/g, '' )
            )
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
    /*
    setup_highlight:  function( opts ) {
        console.log( opts );

        edit.editor
            .on( 
                'viewportChange',  
                function( cm, from, to ) { 
                    if( from <= to ) {

                        var m = cm.findMarks( from, to );

                        if( m.length > 0 ) {
                            for( var i = 0; i < m.length; i++ ) {
                                m[i].clear();
                            }
                        }

                        cm.eachLine( from, to, function( h ) {
                            var line = console.log( cm.getLineNumber( h ) );

                            

                        });
                    }
                }
            )
        ;

    },
    */
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
    set_edited_marks: function( cm, change ) {

        console.log( change );
        
        cm.markText( 
            { 
                line:   change.from.line,
                ch:     change.from.ch,
            },
            {
                line:   change.to.line,
                ch:     change.to.ch + change.text[ 0 ].length
            },
            {
                className: 'edited'
            }

        );


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

                    var payload = {
                        'draft_contents': draft_contents,
                        'draft_notes': draft_notes,
                        'draft_commit': draft_commit,
                        'draft_filename': draft_filename,
                        'draft_work_time': uniq_draft_work_seconds
                    };

                    if( typeof( storage ) != 'undefined' && storage != null && storage.local_storage ) {
                        storage.set_item( 
                            'Scratch',
                            'Drafts', 
                            draft_filename, 
                            draft_commit.substr( 0, 6 ), 
                            payload 
                        );

                        // Establish a set of draft snapshots, going 
                        // back every minute while editing.
                        if( true ) {

                            var dt = new Date();

                            dt.setSeconds( 0 );
                            dt.setMilliseconds( 0 );

                            var fmt = dt.toString();

                            if( dt.toISOString ) {
                                fmt = dt.toISOString();
                            }

                            storage.set_item( 
                                'Scratch',
                                'Drafts', 
                                draft_filename, 
                                draft_commit.substr( 0, 6 ), 
                                ( 
                                    fmt
                                ),
                                payload 
                            );
                        }

                    }

                    var r = $.ajax( 
                        "a_draft.php",
                        {
                            type: 'POST',
                            data: payload
                        }
                    ).done(
                        function( data ) { 

                            if ( data.match(/^success$/)) {
                                var d = new Date();
                                $('#draft_msg').html( d.toLocaleTimeString() );

                                edit.draft_cache = draft_contents;
                                edit.draft_last_success = d.getTime();
                                edit.draft_working_seconds = {};

                                $('.edit.editor' ).removeClass( 'draft-fail' );

                            } else {
                                if ( data.match(/^no draft to save$/)) {
                                    // Do nothing 
                                } else {
                                    $('#draft_msg').html( 'Unable to save draft..' );


                                    $('.edit.editor' ).addClass( 'draft-fail' );

                                }
                            }
                        }
                    ).fail(
                        function() { 
                            // Do nothing
                            $('#draft_msg').html( '***Draft failed to save.***' );

                            $('.edit.editor' ).addClass( 'draft-fail' );
                        }
                    );
                }
            }
        }
    }
};
