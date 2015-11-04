<? renderable( $p ) ?>
<? $stash['body_classes'] = array(); ?>
<div class="constrained-content expandable">
    <div class="edit">
        <?
        $path_components = array_map( "path_to_filename", explode( "/", undirify( $p['parameters']['file'] ) ) );
        $stash['body_classes'] = array_merge( $stash['body_classes'], $path_components );
        ?>
        <div class="edit-contents <?= implode( " ", $path_components ) ?>">
        
            <form id="edit-file" method="post" action="edit.php" >
                <input type="hidden" id="existing_commit" name="existing_commit" value="<?= $p['existing_commit'] ?>">
                <input type="hidden" id="file" name="file" value="<?= $p['parameters']['file'] ?>">
                <input type="hidden" id="position" name="position" value="<?= $p['parameters']['position'] ?>">
                <? if( $p['parameters']['error_message'] ) { ?>
                <h2 class="error-message"><?= is_array( $p['parameters']['error_message'] ) ? join( '', array_map( 'he', $p['parameters']['error_message'] ) ) : he( $p['parameters']['error_message'] ) ?></h2>
                <? } ?>
                <div><span id="draft_msg"></span></div>
                <? if( $p['parameters']['submit'] == "Preview" ) { ?>
                <div>
                    Preview Render
                </div>
                <div>
                    <a name="preview"></a><div id="rendered_edit_contents" class="preview-pane">
                    <div class="file-name">
                        <div>
                            <span class="title" title="<?= $f ?>"><?= basename( $p['parameters']['file'] ) ?></span>
                        </div>
                        <? if( strpos( $p['parameters']['file'], '/' ) !== FALSE ) { ?>
                        <div>
                            <span class="breadcrumb"><a href="index.php?file=<?= DEFAULT_FILE ?>">&lt;</a> <?= linkify( '[[' .  undirify( $p['parameters']['file'] ) . ']]', " | " ) ?></span>
                        </div>
                        <? } ?>
                    </div>
                    <div><?= $p['rendered_edit_contents'] ?></div>
                </div>
                <div>
                    <input 
                        name="synchronize" 
                        type="checkbox"  
                        id="scroll-lock" 
                        <?= ( isset( $parameters['synchronize'] ) && $parameters['synchronize'] == "" ? "" : "checked" ) ?>
                    > Synchronous Scrolling
                </div>
                <? } ?>
                <div class="pane-container">
                    <div class="child-pane">
                        <div>
                            <? if( $p['existing_commit'] ) { ?>
                            New Version
                            <? } else { ?>
                            New File: <?= he( $p['parameters']['file'] ) ?>
                            <? } ?>
                        </div>
                        <div>
                            <textarea name="edit_contents" id="edit_contents" ><?= he( $p['parameters']['edit_contents'] ) ?></textarea>
                        </div>
                        <div class="buttons">
                            <span id="meta"></span>
                            <a class="wikilink" title="WikiLink Selected Text, Key Combo: Control-L"            href="javascript:edit.wikilink_selected()"><span class="first">L</span><span class="rest">ink (wiki)</span></a>
                            <?//[<a title="Or use Control-Backtick to trigger the same functionality" href="javascript:void( edit.annotate_selected() )">Annotate Selected Text</a>]?>
                            <? // [<a href="javascript:void( edit.link_selected() )">Link Selected Text</a>] ?>


                            <a class="help"     title="Markdown Syntax Reference" target="_new"                 href="http://daringfireball.net/projects/markdown/syntax"><span class="first">?</span><span class="rest"> Help</span></a>
                            <?//Helpful links: <a href="http://daringfireball.net/projects/markdown/syntax">Markdown Syntax</a>?>
                        </div>
                        <br/>
                    </div>
                </div>
                <div class="pane-container">
                    <div class="child-pane">
                        <div>
                            Commit Notes (submit blank to automatically generate a word count difference for the note):
                        </div>
                        <div>
                            <textarea name="commit_notes" id="commit_notes"><?= he( $p['parameters']['commit_notes'] ) ?></textarea>
                        </div>
                    </div>
                </div>
                <div>
                    <? if( isset( $p['parameters']['submit'] ) && in_array( $p['parameters']['submit'], array( "Preview", "Commit", "Commit and Edit" ) ) ) { ?>
                        <input type="submit" name="submit" id="submit_button" value="Commit" >
                        <input type="submit" name="submit" id="submit_button" value="Commit and Edit" >
                    <? } ?>
                    <input type="submit" name="submit" id="preview_button" value="Preview" >
                    <input type="button" id="cancel_button" value="Cancel">
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript" src="js/jquery.scrollTo-1.4.3.1-min.js"></script>
<script type="text/javascript">
    
    var edit_form               = $('#edit_file').get(0);
    var edit_contents           = $('#edit_contents').get(0);
    var commit_notes            = $('#commit_notes').get(0);
    var rendered_edit_contents  = $('#rendered_edit_contents').get(0);
    var cancel_button           = $('#cancel_button').get(0);
    var scroll_lock             = $('#scroll-lock').get(0);
    var existing_commit_hidden  = $('#existing_commit').get(0);
    var file_hidden             = $('#file').get(0);
    var position_hidden         = $('#position').get(0);

    var edit = {
        form: edit_form,
        contents: edit_contents,
        notes: commit_notes,
        // existing: existing_contents,
        rendered_edit: rendered_edit_contents,
        cancel: cancel_button,
        // rendered_existing: rendered_existing_contents,
        scroll: scroll_lock,
        existing_commit: existing_commit_hidden,
        file: file_hidden,
        draft_cache: '',
        initial_position: <?= ( isset( $p['parameters']['position'] ) ? $p['parameters']['position'] : 0 )  ?>,
        position: position_hidden,
        sync_enable: function() { 
            return edit.scroll != null; 
        },
        /*
        key_combo_check: function( event ) {

           switch ( event.keyCode ) {
                case 96:
                    edit.annotate_selected();
                    break;
                default:
                    break;
            }

        },
        */
        /*
        annotate_selected: function() {
            if( edit.contents.selectionStart && edit.contents.selectionEnd ) {
                var start = edit.contents.selectionStart;
                var end = edit.contents.selectionEnd;

                if( start < end && end <= edit.contents.value.length ) {
                    var to_annotate = edit.contents.value.substring( start, end );

                    var comment = prompt( "Comments for this annotation?" );

                    if( comment.length > 0 ) {

                        edit.contents.value = edit.contents.value.substring( 0, start ) +
                            '<annotate>' +
                            edit.contents.value.substring( start, end ) +
                            '<comment>' +
                            comment +
                            '</comment>' +
                            '</annotate>' +
                            edit.contents.value.substring( end, edit.contents.value.length );
                    }
                }
            }
        },
        link_selected: function() {
            if( edit.contents.selectionStart && edit.contents.selectionEnd ) {
                var start = edit.contents.selectionStart;
                var end = edit.contents.selectionEnd;

                if( start < end && end <= edit.contents.value.length ) {
                    var to_annotate = edit.contents.value.substring( start, end );

                    var wiki_page = prompt( "Which wiki page do you want to link to?", $(edit.file).val() + '/NewFile' );

                    if( wiki_page.length > 0 ) {

                        edit.contents.value = edit.contents.value.substring( 0, start ) +
                            '[[' +
                            wiki_page +
                            '|' +
                            edit.contents.value.substring( start, end ) +
                            ']]' +
                            edit.contents.value.substring( end, edit.contents.value.length );
                    }
                }
            }
        },
        */
        wikilink_selected: function() {
            if( typeof edit.contents.selectionStart != 'undefined' && edit.contents.selectionEnd != 'undefined' ) {
                var start = edit.contents.selectionStart;
                var end = edit.contents.selectionEnd;

                if( start < end && end <= edit.contents.value.length ) {

                    var wiki_page = prompt( "Which wiki page do you want to link to?", $(edit.file).val() + '/NewFile' );

                    if( wiki_page && wiki_page.length > 0 ) {

                        edit.contents.value = edit.contents.value.substring( 0, start ) +
                            '[[' +
                            wiki_page +
                            '|' +
                            edit.contents.value.substring( start, end ) +
                            ']]' +
                            edit.contents.value.substring( end, edit.contents.value.length );
                    }
                } else {

                    if( start == end ) {
                        var link = $(edit.file).val()
                        var display = 'Display Text';

                        edit.contents.value = edit.contents.value.substring( 0, start ) +
                            '[[' +
                            link +
                            '|' +
                            display +
                            ']]' +
                            edit.contents.value.substring( end, edit.contents.value.length );

                        edit.contents.selectionStart    = start + 2;
                        edit.contents.selectionEnd      = start + 2 + link.length;
                    }
                }
            }
        },

        edit_sync_scroll: function( el ) {
   
            if( edit.sync_enable() ) {
                if( $(edit.scroll).val() != null ) {

                    
                    if( edit.contents != el ) { 
                        edit.contents.scrollTop = el.scrollTop;
                    }
                    if( edit.rendered_edit != el ) { 
                        edit.rendered_edit.scrollTop = el.scrollTop;
                    }

                }
            }

            if( edit.position != null ) {
                edit.position.value = edit.contents.scrollTop;
            }

        },
        draft: function( contents, notes, commit, filename ) {

            var draft_contents = ( typeof( contents ) == 'undefined' ? null : $( contents ).val() );
            var draft_notes = ( typeof( notes ) == 'undefined' ? null : $( notes ).val() );
            var draft_commit = ( typeof( commit ) == 'undefined' ? null : $( commit ).val() );
            var draft_filename = ( typeof( filename ) == 'undefined' ? null : $( filename ).val() );

            if( draft_contents != null && draft_commit != null && draft_filename != null && draft_notes != null ) {

                if( draft_contents != '' && draft_contents != edit.draft_cache ) {

                    var r = $.ajax( 
                        "a_draft.php",
                        {
                            type: 'POST',
                            data: {
                                'draft_contents': draft_contents,
                                'draft_notes': draft_notes,
                                'draft_commit': draft_commit,
                                'draft_filename': draft_filename
                            }
                        }
                    ).done(
                        function( data ) { 

                            if ( data.match(/^success$/)) {
                                var d = new Date();
                                $('#draft_msg').html( 'Draft saved at ' + d );

                                edit.draft_cache = draft_contents;
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
                        }
                    );
                }
            }
        }
    };


    $(edit.contents).scroll(
        function() {
            edit.edit_sync_scroll( edit.contents );
        }
    );

    // Control-a: annotations
    $(edit.contents).meta( 'l', edit.wikilink_selected  );

    /*
    $(edit.contents).keypress(
        function( evt ) {
            edit.key_combo_check( evt );
        }
    );
    */

    $(document).disable_backspace(
        function( e ) {
            if (e.which == 8 && !$(edit.contents).is(':focus') && !$(edit.notes).is(':focus') ) { //"backspace" key
                e.preventDefault();
                return false;
            }

            return true;
        } 
    );


    $(document).ready(
        function() {

            edit.contents.scrollTop = edit.initial_position;

            if( edit.rendered_edit ) {

                edit.rendered_edit.scrollTop = edit.initial_position;

            }

            if( edit.initial_position != 0 ) {
                if( edit.rendered_edit ) {
                    $(edit.rendered_edit).scrollTo();
                } else {
                    $(edit.contents).scrollTo();
                }
            }
        }
    );

    if( edit.rendered_edit ) {
        $(edit.rendered_edit).scroll(
            function() {
                edit.edit_sync_scroll( edit.rendered_edit );
            }
        );
    }

    $(edit.cancel).click(
        function() {
            window.location = 'index.php?file=<?= ( $p['already_exists'] ? $p['parameters']['file'] : DEFAULT_FILE )  ?>';
        }
    );

    if( true ) {
        setInterval( 
            function() { edit.draft( edit.contents, edit.notes, edit.existing_commit, edit.file ) }, 
            10000
        );
    }

    $(document).ready(
        function() {
            $( '.preview-pane table.tabulizer' ).tabulizer();
        }
    )

    // "Prime" the draft cache so that the cache contents the initial edit
    // contents of the editing window.
    $(document).ready( function() {
        edit.draft_cache = $(edit.contents).val();
    } );



</script>

