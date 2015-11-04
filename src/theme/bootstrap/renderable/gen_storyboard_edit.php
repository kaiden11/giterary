<? 
renderable( $p );

# $stash['css'][] = 'simpler.v2.sequence.css';
$stash['css'][] = 'view.css';
$stash['css'][] = 'display.css';
$stash['css'][] = 'edit.css';
$stash['css'][] = 'giterary-codemirror.css';
$stash['css'][] = 'codemirror.css';

$stash['core_js'][]   = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['js'][]  = 'cm/current/lib/codemirror.js';
$stash['js'][]  = 'cm/current/addon/display/fullscreen.js';
$stash['js'][]  = 'cm/current/addon/display/rulers.js';
$stash['js'][]  = 'cm/current/addon/dialog/dialog.js';
$stash['js'][]  = 'cm/current/addon/search/search.js';
$stash['js'][]  = 'cm/current/addon/search/searchcursor.js';
$stash['js'][]  = 'cm/current/addon/fold/foldcode-multi.js';
$stash['js'][]  = 'cm/current/addon/fold/foldgutter.js';
$stash['js'][]  = 'cm/current/addon/wrap/hardwrap.js';
# $stash['js'][]  = 'cm/current/addon/fold/markdown-fold.js';
$stash['js'][]  = 'annotation-fold.js';
$stash['js'][]  = 'edit.js';

$stash['core_js'][]  = 'csv.js';
$stash['core_js'][]  = 'storyboard.js';

if( SHAREJS_ENABLE ) {
    $stash['js'][]  = 'bcsocket-uncompressed.js';
    $stash['js'][]  = 'share.uncompressed.js';
    $stash['js'][]  = 'share.cm.js';
    $stash['js'][]  = 'giterary.share.js';
}

GLOBAL $wikilink_pattern;

if( !isset( $stash['body_classes'] ) ) {
    $stash['body_classes'] = array();
}

$path_components = array_map( "path_to_filename", explode( "/", undirify( $p['parameters']['file'] ) ) );
$stash['body_classes'] = array_merge( $stash['body_classes'], $path_components );

$cm_mode = "markdown";
switch( $p['extension'] ) {
    case "markdown":
        $cm_mode = "giterary_markdown";
        $stash['js'][]  = 'cm/current/mode/xml/xml.js';
        $stash['js'][]  = 'cm/current/mode/markdown/markdown.js';
        $stash['js'][]  = 'cm/current/addon/mode/overlay.js';
        $stash['js'][]  = 'edit.annotations.js';
        break;

    case "storyboard":
    case "csv":
        $cm_mode = "csv";
        $stash['js'][]  = 'giterary.codemirror.mode.csv.js';
        break;

    case "css":
        $cm_mode = "css";
        $stash['js'][]  = 'cm/current/mode/css/css.js';
        break;
    case "js":
    case "anno":
        $cm_mode = "javascript";
        $stash['js'][]  = 'cm/current/mode/javascript/javascript.js';
        break;


    default:
        $cm_mode = "giterary_markdown";
        $stash['js'][]  = 'cm/current/mode/xml/xml.js';
        $stash['js'][]  = 'cm/current/mode/markdown/markdown.js';
        $stash['js'][]  = 'cm/current/addon/mode/overlay.js';
        $stash['js'][]  = 'edit.annotations.js';
        break;
}

?>
<div 
    class="edit root giterary-extension-<?= $p['extension'] ?> <?= ( $p['parameters']['submit'] == "Preview" ? 'meta-off' : 'meta-on' ) ?> preview-on"
>
    <nav class="navbar navbar-default navbar-fixed-bottom">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#edit-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="edit-collapse">
                <ul class="nav navbar-nav">
                    <li class="shortcut"><a href="#" id="annotations"><kbd>A</kbd>nnotations</a></li>
                    <li class="shortcut"><a href="#" id="comment"><kbd>C</kbd>omment</a></li>
                    <li class="shortcut"><a href="#" id="bold"><kbd>B</kbd>old</a></li>
                    <li class="shortcut"><a href="#" id="italic"><kbd>I</kbd>talic</a></li>
                    <li class="shortcut"><a href="#" id="strike">Strike<kbd>-</kbd>through</a></li>
                    <li class="shortcut"><a href="#" id="wikilink">Wiki<kbd>l</kbd>ink</a></li>
                    <li class="shortcut"><a href="#" id="externallink"><kbd>U</kbd>RL Link</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><span class="wordcount navbar-text">-</span></li>
                    <li>
                        <div class="draft message">
                            <span 
                                class="navbar-text" 
                                id="draft_msg"
                                title="Draft saved at this time."
                            >-</span>
                        </div>
                    </li>
                    <li class="dropdown">
                        <button
                            class="btn btn-default navbar-btn dropdown-toggle"
                            data-toggle="dropdown"
                        >
                            Other
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li role="presentation" class="dropdown-header">Editor Options</li>
                            <li class="shortcut"><a href="#" id="escape">Toggle navigation <kbd>ESC</kbd></a></li>
                            <li role="presentation" class="dropdown-header">Fold/unfold elements <kbd>H</kbd></li>
                            <? if( SHAREJS_ENABLE ) { ?>
                                <li class="divider" role="presentation"></li>
                                <li class="multi-edit">
                                    <a href="#" id="multi-edit">
                                        <span id="multi-edit-status">Enable Multi-edit</span>
                                    </a>
                                </li>
                            <? } ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <button
                            class="btn btn-default navbar-btn dropdown-toggle"
                            data-toggle="dropdown"
                        >
                            Help
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li class="shortcut">
                                <pre class="pre-scrollable">Ctrl-F / Cmd-F: Start searching
Ctrl-G / Cmd-G: Find next
Shift-Ctrl-G / Shift-Cmd-G: Find previous
Shift-Ctrl-F / Cmd-Option-F: Replace
Shift-Ctrl-R / Shift-Cmd-Option-F: Replace all
</pre>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <form 
        id="edit-file" 
        method="post" 
        action="edit.php" 
        accept-charset="utf-8" 
        class="form-group"
    >
        <input type="hidden" id="existing_commit"   name="existing_commit"  value="<?= $p['existing_commit']                ?>" >
        <input type="hidden" id="file"              name="file"             value="<?= $p['parameters']['file']             ?>" >
        <input type="hidden" id="position"          name="position"         value="<?= $p['parameters']['position']         ?>" >
        <input type="hidden" id="preview_position"  name="preview_position" value="<?= $p['parameters']['preview_position'] ?>" >
        <input type="hidden" id="caret_position"    name="caret_position"   value="<?= $p['parameters']['caret_position']   ?>" >
        <input type="hidden" id="height"            name="height"           value="<?= $p['parameters']['height']           ?>" >
        <div class="container-fluid">
            <? if( $p['parameters']['error_message'] ) { ?>
                    <div class="col-md-12">
                        <h2 class="error-message then-disappear">
                            <?= is_array( $p['parameters']['error_message'] ) 
                                    ?
                                        implode( 
                                            '', 
                                            array_map( 
                                                function( $a ) { return he( $a ); }, 
                                                $p['parameters']['error_message'] 
                                            ) 
                                        ) 
                                    :
                                        he( $p['parameters']['error_message'] )
                            ?>
                        </h2>
                    </div>
                </div>
            <? } ?>
            <div class="row">
                <div class="edit preview col-md-12">
                    <div class="panel panel-default">
                         <div class="panel-heading">
                             <span 
                                 class="panel-title"
                             >
                                 Storyboard Editor
                             </span>
                         </div>
                         <div class="panel-body">
                            <div id="rendered_edit_contents" class="preview">
                                <div class="view">
                                    <div class="view file <?= $p['extension'] ?>">
                                        <?= $p['rendered_edit_contents'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit display col-md-12">
                   <div class="panel panel-default">
                        <div class="panel-heading">
                            <span 
                                class="panel-title filename" 
                                title="<?=he( undirify( $p['parameters']['file'] ) )?>"
                            >
                                <?= he( 
                                    minify( 
                                        dirname( 
                                            undirify( 
                                                $p['parameters']['file']
                                            )
                                        )
                                    ) . '/' .
                                    basename( 
                                        $p['parameters']['file']
                                    )
                                ) ?>
                            </span>
                        </div>
                        <div class="panel-body">
                            <div class="edit clear"></div>
                            <div class="edit editor">
                                <textarea 
                                    name="edit_contents" 
                                    id="edit_contents" 
                                    class="form-control"
                                ><?= 
                                    (
                                        (
                                            // Doesn't exist,
                                            !$p['already_exists'] &&
                                            // Nothing in the edit_contents
                                            ( $p['parameters']['edit_contents'] == null || $p['parameters']['edit_contents'] == "" ) && 
                                            // Not previewing
                                            $p['parameters']['submit'] != 'Preview'
                                        )
                                            ?  "Flags  ,    Subject     ,    Description
        ,   Card Subject,    Card Description
"
                                            :   he( $p['parameters']['edit_contents'] )
                                    )
                                ?></textarea>
                            </div>
                            <div class="edit clear"></div>
                        </div>
                   </div>
                </div>
            </div>
            <div class="row">
                <div class-"col-md-12">
                    <div class="commit notes">
                        <div class="child-pane">
                            <div>
                                Commit Notes (submit blank to automatically generate a word count difference for the note):
                            </div>
                            <div>
                                <textarea 
                                    name="commit_notes" 
                                    id="commit_notes"
                                    class="form-control"
                                ><?= he( $p['parameters']['commit_notes'] ) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class-"col-md-12">
                    <div class="submission btn-group">
                        <? if( isset( $p['parameters']['submit'] ) && in_array( $p['parameters']['submit'], array( "Preview", "Commit", "Commit and Edit" ) ) ) { ?>
                            <input 
                                type="submit" 
                                name="submit" 
                                id="commit_submit_button" 
                                value="Commit" 
                                class="btn btn-default"
                            >
                            <input 
                                type="submit" 
                                name="submit" 
                                id="commit_edit_submit_button" 
                                value="Commit and Edit" 
                                class="btn btn-default"
                            >
                        <? } ?>
                        <input
                            type="submit" 
                            name="submit" 
                            id="preview_button" 
                            value="Preview" 
                            class="btn btn-default"
                        >
                        <input 
                            type="button" 
                            id="cancel_button" 
                            value="Cancel"
                            class="btn btn-default"
                        >
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            edit.setup( 
                <?= json_encode( $p['extension'] ) ?>,
                <?= json_encode( $cm_mode ) ?>,
                {
                    textarea:           $( '#edit_contents' ),
                    initial_position:   <?= ( isset( $p['parameters']['position'] ) ? $p['parameters']['position'] : 0 ) ?>,
                    already_exists:     <?= json_encode( $p['already_exists'] == true ) ?>,
                    filename:           <?= json_encode( $p['parameters']['file'] ) ?>,
                    cancel_to:          '<?= urlencode( ( $p['already_exists'] ? $p['parameters']['file'] : DEFAULT_FILE ) ) ?>'
                }
            );

            if( edit.editor && edit.editor.setOption ) {
                edit.editor.setOption( 'lineWrapping', false );
            }

            setTimeout(
                function() {
                    $( '.error-message.then-disappear' ).hide(
                        1000
                    );

                    console.log( 'hiding' );
                },
                5000
            );
        }
    );
</script>
<script type="text/javascript">
    (function() {

        var sb  = {
            update_skip:    0,
            update_editor: function() {

                var content = edit.get_content();
                var lines   = content.split( /\r?\n/ );

                // var new_lines = [];

                var indices = storyboard.get_indices( lines[ 0 ] );

                /* Resetting flags */


                var max_col_lengths = [ 0, 0, 0 ];
                var new_content = [];
                

                for( var i = 0; i < lines.length; i++ ) {

                    if( i == 0 ) {

                        new_content.push(
                            csv.str_getcsv( lines[ i ] )
                        );

                    } else {

                        var card = storyboard.parse_line( lines[ i ], indices );
                        // Update any flags from the corresponding dom element
                        var e = $('.cards .card[data-line=' + i + ']');

                        card.flag = '';
                        card.flag += ( e.data( 'disabled' ) == 1 || e.hasClass( 'disabled' ) ? '!' : '' );
                        card.flag += ( e.data( 'questioned' ) == 1 || e.hasClass( 'questioned' ) ? '?' : '' );

                        new_content.push( 
                            [
                                card.flag,
                                card.subject,
                                card.description
                            ]
                        );
                    }
                }

                // Determine maximum column width for each column
                for( var i = 0; i < new_content.length; i++ ) {
                    
                    for( var j = 0; j < new_content[ i ].length; j++ ) {
                       
                        if( new_content[ i ][ j ] != null && new_content[ i ][ j ].length > 0 ) {
                            if( max_col_lengths[ j ] < new_content[ i ][ j ].length ) {
                                max_col_lengths[ j ] = new_content[ i ][ j ].length;
                            }
                        }
                    }
                }

                // Pad right to max column width for each column
                for( var i = 0; i < new_content.length; i++ ) {

                    for( var j = 0; j < new_content[ i ].length; j++ ) {

                        new_content[ i ][ j ] =  ( new_content[ i ][ j ] == null ? '' : new_content[ i ][ j ] );
                        new_content[ i ][ j ] = new_content[ i ][ j ].pad_right( 
                            ( 
                                max_col_lengths[ j ] == 0
                                    ?   max_col_lengths[ j ]
                                    :   ( max_col_lengths[ j ] + max_col_lengths[ j ] % 4 )
                            )
                        );
                    }
                }

                // Sort new content according to DOM order
                var sorted_new_content = [];
                sorted_new_content.push( new_content[ 0 ] );

                $('.cards .card' ).each( function( i, v ) {

                    var orig_line = $(v).data( 'line' );

                    if( orig_line < new_content.length ) {
                        sorted_new_content.push( new_content[ orig_line ] );
                        $(v).data( 'line', sorted_new_content.length-1 );
                    }
                } );

                edit.editor.setValue( 
                    sorted_new_content.map(
                        function( v, i, a ) {
                            return v.join( ',' ).trimRight();
                        }
                    ).join( '\n' ) 
                );
            },
            update: function( evt, ui ) {

                /*
                sb.update_skip++;

                if( sb.update_skip < $('.cards').length ) {
                    console.log( sb.update_skip );
                    return;
                }

                sb.update_skip = 0;
                */


                if( $(ui.item).parent().hasClass( 'disabled' ) ) {
                    $(ui.item).addClass( 'disabled' )
                        .data( 'disabled', 1 )
                    ;

                    $(ui.item).find( '.panel' )
                        .removeClass( 'panel-default' )
                        .addClass( 'panel-danger' )
                    ;

                } else {
                    $(ui.item).removeClass( 'disabled' )
                        .data( 'disabled', 0 )
                    ;

                    $(ui.item).find( '.panel' )
                        .addClass( 'panel-default' )
                        .removeClass( 'panel-danger' )
                    ;
                }

                sb.update_editor();
            }
        };
        $(document).ready( function() {
            $('.cards').sortable(
                {
                    connectWith:    '.cards',
                    tolerance:      'pointer'
                }
            );

            $('.view.file.storyboard').on( 'sortstop', sb.update );

            $('.view.file.storyboard').dblclick( '.cards .card .panel-heading', function( evt ) {
                var card = $(evt.target).closest( '.card' );

                if( $(card).hasClass( 'questioned' ) ) {
                    $(card).removeClass( 'questioned' )
                        .data( 'questioned', 0 )
                    ;
                } else {
                    $(card).addClass( 'questioned' )
                        .data( 'questioned', 1 )
                    ;
                }

                sb.update_editor();

            } );

            $('.view.file.storyboard').mouseover( '.cards .card', function( evt ) {
                var card = $(evt.target).closest( '.card' );

                if( card.length == 1 ) {
                    var line    = $(card).data( 'line' );

                    edit.editor.addLineClass( 
                        line,
                        'background',
                        'highlight'
                    );

                    edit.editor.scrollIntoView( 
                        line
                    );
                }
            }).mouseout( '.cards .card', function( evt ) {
                var card = $(evt.target).closest( '.card' );

                if( card.length == 1 ) {
                    var line    = $(card).data( 'line' );

                    edit.editor.removeLineClass( 
                        line,
                        'background',
                        'highlight'
                    );
                }
            } );

            /*
            if( edit && edit.editor ) {
                edit.editor.on( 'cursorActivity', function( cm, evt ) {

                    var cursor = cm.getCursor();

                    console.log( cursor );

                    // $('.cards .card.highlight').removeClass( 'highlight' );

                    var card = $('.cards .card[data-line=' + cursor.line + ']');

                    if( card.length > 0 ) {
                        $(card).addClass( 'highlight' );
                        console.log( card );
                    }
                });
            }
            */




        } );
    } )();
</script>

<? if( SHAREJS_ENABLE ) { ?>
<script type="text/javascript">
    $(document).ready(
        function() {
            giterary_sharejs.setup(
                $('#multi-edit'),
                $('#multi-edit-status'),
                edit.editor,
                <?= json_encode( SHAREJS_URL ) ?>,
                <?= json_encode( path_to_filename( undirify( $p['parameters']['file'] ) ) ) ?>
            );
        }
    );
</script>
<? } ?>
