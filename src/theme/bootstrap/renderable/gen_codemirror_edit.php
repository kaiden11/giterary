<? 
renderable( $p );

# $stash['css'][] = 'simpler.v2.sequence.css';
$stash['css'][] = 'view.css';
$stash['css'][] = 'display.css';
$stash['css'][] = 'edit.css';
$stash['css'][] = 'giterary-codemirror.css';
$stash['css'][] = 'giterary-show-hint.css';
$stash['css'][] = 'codemirror.css';
$stash['css'][] = 'matchesonscrollbar.css';
// $stash['css'][] = 'merge.css';

$stash['core_js'][]   = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['js'][]  = 'cm/current/lib/codemirror.js';
$stash['js'][]  = 'cm/current/addon/display/fullscreen.js';
$stash['js'][]  = 'cm/current/addon/display/rulers.js';
$stash['js'][]  = 'cm/current/addon/dialog/dialog.js';
$stash['js'][]  = 'cm/current/addon/search/search.js';
$stash['js'][]  = 'cm/current/addon/search/searchcursor.js';
// $stash['js'][]  = 'cm/current/addon/fold/foldcode.js';
// $stash['js'][]  = 'cm/current/addon/fold/foldgutter.js';
$stash['js'][]  = 'cm/current/addon/scroll/annotatescrollbar.js';
$stash['js'][]  = 'cm/current/addon/search/matchesonscrollbar.js';
// $stash['core_js'][] = 'diff_match_patch.js';
// $stash['js'][]  = 'cm/current/addon/merge/merge.js';
$stash['js'][]  = 'cm/current/addon/wrap/hardwrap.js';
$stash['js'][]  = 'cm/current/addon/hint/show-hint.js';
$stash['js'][]  = 'funcify-hint.js';
# $stash['js'][]  = 'cm/current/addon/fold/markdown-fold.js';
// $stash['js'][]  = 'annotation-fold.js';
$stash['js'][]  = 'edit.js';

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
        $stash['js'][]  = 'cm/current/addon/edit/continuelist.js';
        $stash['js'][]  = 'edit.annotations.js';
        break;

    case "pub":
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
    case "text":
    case "txt":
        $cm_mode = "text";
        // $stash['js'][]  = 'cm/current/mode/javascript/javascript.js';
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
    class="edit root giterary-extension-<?= $p['extension'] ?> <?= ( $p['parameters']['submit'] == "Preview" ? 'meta-off' : 'meta-on' ) ?> <?= ( $p['parameters']['submit'] == "Preview" ? 'preview-on' : 'preview-off' ) ?>"
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
                    <li>
                        <div class="btn-group" data-toggle="buttons">
                            <label
                                id="preview-toggle"
                                class="btn btn-default navbar-btn <?= ( $p['parameters']['submit'] == "Preview" ? 'active' : '' ) ?>"
                            >
                                <input 
                                    type="checkbox" 
                                    name="preview-toggle" 
                                    <?= ( $p['parameters']['submit'] == "Preview" ? 'checked="checked"' : '' ) ?>
                                />
                                Show <kbd>P</kbd>review
                            </label>
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
                            <li class="shortcut">
                                <a 
                                    href="import.php?file=<?= urlencode( $p['parameters']['file'] ) ?>"
                                >
                                    Convert/import into this document
                                </a>
                            </li>
                            <li
                                class="shortcut"
                            >
                                <a 
                                    id="tag-suggest" 
                                    href="#"
                                >
                                    Insert suggested tags
                                </a>
                            </li>
                            <li
                                class="shortcut"
                            >
                                <a 
                                    id="insert-timestamp" 
                                    href="#"
                                >
                                    Insert current <kbd>t</kbd>imestamp
                                </a>
                            </li>
                            <? /*
                            <li
                                class="shortcut"
                            >
                                <a 
                                    id="show-diff" 
                                    href="#"
                                >
                                    Show differences introduced in this edit
                                </a>
                            </li>
                            */ ?>
                            <li class="divider" role="presentation"></li>
                            <li role="presentation" class="dropdown-header">Editor Options</li>
                            <li class="shortcut"><a href="#" id="escape">Toggle navigation <kbd>ESC</kbd></a></li>
                            <?php /*<li role="presentation" class="dropdown-header">Fold/unfold elements <kbd>H</kbd></li> */ ?>
                            <li class="shortcut"><a href="#" id="toggle_editor">Toggle editor</a></li>
                            <li class="shortcut"><a href="#" id="toggle_wordwrap">Toggle line <kbd>w</kbd>rapping</a></li>
                            <li class="shortcut"><a href="#" id="fullscreen"><kbd>F</kbd>ull Screen</a></li>

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
                <div class="row">
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
                <div class="edit display col-md-6">
                   <div class="panel panel-default">
                        <div class="panel-heading">
                            <span 
                                class="panel-title filename" 
                                title="<?=he( undirify( $p['parameters']['file'] ) )?>"
                            >
                                <?= linkify( 
                                    '[[' . 
                                    undirify( $p['parameters']['file'] ) . 
                                    ']]',
                                    array( 'minify' => true )
                                ) ?>
                                <?= ( 
                                    git_file_exists( $p['parameters']['file'] . "/" . TALK_PAGE ) 
                                        ?   '<span>(' . linkify( '[[' . $p['parameters']['file'] . '/' . TALK_PAGE . '|' . TALK_PAGE . ']]' ) . ')</span>'
                                        :   ''
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
                                    he( 
                                        $p['parameters']['edit_contents']
                                    ) 
                                ?></textarea>
                            </div>
                            <div class="edit clear"></div>
                        </div>
                   </div>
                </div>
                <div class="edit preview col-md-6">
                    <div class="panel panel-default">
                         <div class="panel-heading">
                             <span 
                                 class="panel-title"
                             >
                                 Preview
                             </span>
                         </div>
                         <div class="panel-body">
                            <div id="rendered_edit_contents" class="preview">
                                <div class="view">
                                    <div class="view file <?= $p['extension'] ?>">
                                        <? if( $p['parameters']['submit'] == "Preview" ) { ?>
                                            <?= $p['rendered_edit_contents'] ?>
                                        <? } ?>
                                    </div>
                                </div>
                            </div>
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
                    cancel_to:          '<?= urlencode( ( $p['already_exists'] ? $p['parameters']['file'] : DEFAULT_FILE ) ) ?>',
                    talk_append:        <?= json_encode( set_or( $p['parameters']['talk_append'], false ) ) ?>,
                    existing_wc:        <?= json_encode( giterary_word_count( $p['existing_contents'] ) ) ?>

                }
            );

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
