<? renderable( $p ) ?>
<? 
$stash['css'][] = 'view.css';
$stash['css'][] = 'display.css';
$stash['css'][] = 'table.css';
// Base JS
$stash['core_js'][] = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['core_js'][] = 'csv.js';
$stash['js'][] = 'view.js';
$stash['js'][] = 'snippets.js';
$stash['js'][] = 'view.annotations.js';

if( ANNOTATORJS_ENABLE && is_logged_in() ) {
    $stash['js'][]  = 'annotator.min.js';
    $stash['js'][]  = 'annotator.store.min.js';
    $stash['js'][]  = 'annotator.tags.min.js';
    $stash['js'][]  = 'annotator.draft.js';
    $stash['js'][]  = 'giterary.annotator.js';
    $stash['css'][] = 'annotator.css';
    $stash['css'][] = 'giterary-annotator.css';
}

$relationships_to_flag = ( isset( $p['notable_relationships'] ) && count( $p['notable_relationships'] ) > 0 ? $p['notable_relationships'] : array() );
// Annotations JS
if( isset( $p['annotations'][$commit_file] ) && count( $p['annotations'][$commit_file] ) > 0 ) { 
    $stash['js'][] = 'view.annotations.js';
}
?>
<? $stash['body_classes'] = array(); ?>
<?  foreach( $p['view'] as $commit_file => &$content ) { ?>
    <? 
    list( $c, $f ) = explode( ":", $commit_file );
    $m = md5( $commit_file ); 
    $path_components = explode( "/", undirify( $f ) );
    $body_class_components = array_map( "path_to_filename", $path_components );
    $stash['body_classes'] = array_merge( $stash['body_classes'], $body_class_components );

    // Setting up a few common names
    $df = dirify( $f, true );
    $dirf = ( ( $f == DEFAULT_FILE || strpos( $f, '/' ) === false ) ? dirify( DEFAULT_FILE, true ) : dirify( dirname( $f ), true ) );
    $uf = undirify( $f );

    ?>
    <div class="view">
        <nav class="navbar navbar-default navbar-fixed-bottom">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button 
                        type="button" 
                        class="navbar-toggle" 
                        data-toggle="collapse" 
                        data-target="#view-nav-collapse"
                    >
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="view-nav-collapse">
                    <ul class="nav navbar-nav">
                        <? if( is_logged_in() ) { ?>
                            <li class="dropdown">
                                <div class="btn-group">
                                    <? 
                                        $draft_exists = false;
                                        if( isset( $p['drafts'][$commit_file] ) && ( $p['drafts'][$commit_file] !== false ) && count( $p['drafts'][$commit_file] ) > 0 ) { 
                                            $draft_exists = true;
                                        }
                                    ?>
                                    <button
                                        class="btn <?= ( $draft_exists ? "btn-danger" : "btn-default" ) ?> navbar-btn clickable"
                                        id="edit-link" 
                                        value="edit.php?file=<?= $f ?>"
                                    >
                                        <kbd>E</kbd>dit
                                    </button>
                                    <button
                                        class="btn btn-default navbar-btn dropdown-toggle"
                                        data-toggle="dropdown"
                                        value="edit.php?file=<?= $f ?>"
                                    >
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="move.php?file=<?= $f  ?>">Move</a></li>
                                        <? if( in_array( $p['extension'], array( 'talk','storyboard' ) ) ) { ?>
                                            <? if( $p['extension'] == "talk" ) { ?>
                                                <li>
                                                    <a href="edit.php?file=<?= urlencode( $f ) ?>&talk_append=yes">Continue the conversation</a>
                                                </li>
                                            <? } ?>
                                        <? } else { ?>
                                            <li>
                                                <a href="edit.php?file=<?= "$f/" . TALK_PAGE ?>&talk_append=yes">Talk about this page</a>
                                            </li>
                                            <li>
                                                <a href="edit.php?file=<?= "$f/" . STORYBOARD_PAGE ?>">Storyboard this page</a>
                                            </li>

                                        <? } ?>

                                        <? if( $draft_exists ) {  ?>
                                            <li>
                                                <? $draft = basename( $p['drafts'][$commit_file][0] ); ?>
                                                <a 
                                                    class="bg-danger" 
                                                    href="edit.php?draft=<?= urlencode( $draft ) ?>"
                                                >
                                                    A draft exists for this file. Click to edit with existing draft.
                                                </a>
                                            </li>
                                        <? } ?>
                                        <li><a href="import.php?file=<?= $f  ?>">Import HTML into this document</a></li>
                                        <li><a href="template.php?template=<?= $f ?>">Use this document as a template</a></li>
                                    </ul>
                                </div>
                            </li>
                        <? } ?>

                        <li class="dropdown">
                            <div class="btn-group">
                                <button 
                                    id="history-link" 
                                    value="history.php?file=<?= $f ?>"
                                    class="btn btn-default navbar-btn clickable"
                                >
                                    <kbd>H</kbd>istory
                                </button>
                                <button 
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a 
                                            href="show_commit.php?commit=<?= $c ?>" 
                                            title="Last Edit @ <?= he( $p['show'][$c]['author_date'] ) ?>, Last commit message by <?= he( $p['show'][$c]['author_name'] ) ?>: '<?= he( $p['show'][$c]['subject'] ) ?>'"
                                        >
                                            Last Commit: <?= medium_time_diff( $p['show'][$c]['author_date_epoch'] ) ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a 
                                            href="diff.php?file=<?= urlencode( $f ) ?>&commit_before=<?= urlencode( "$c~" ) ?>&commit_after=<?= urlencode( $c ) ?>&plain=yes"
                                        >
                                            Difference between this and previous version
                                        </a>
                                    </li>
                                    <li>
                                        <a 

                                            href="diff.php?file=<?= urlencode( $f ) ?>&commit_before=<?= urlencode( "$c~" ) ?>&commit_after=<?= urlencode( $c ) ?>"
                                        >
                                            Difference between this and previous version (formatted)
                                        </a>
                                    </li>

                                    <li>
                                        <a 
                                            href="blame.php?file=<?= $f ?>"
                                        >
                                            Show blame for this file (line by line authorship/timestamp)
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </li>
                        <li class="dropdown">
                            <div class="btn-group">
                                <button
                                    class="btn btn-default navbar-btn clickable"
                                    id="directory-link" 
                                    value="index.php?file=<?= "$f." . DIRIFY_SUFFIX  ?>"
                                >
                                    D<kbd>i</kbd>rectory<?= ( $p['ls_tree_count'][$df] > 0 ? ' (' . $p['ls_tree_count'][$df] . ')' : ' (&#8709;)' ) ?>
                                </button>
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a 
                                            href="index.php?file=<?= $dirf ?>"
                                        >
                                            View Parent directory
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="dropdown">
                            <div class="btn-group">
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    Other...
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li role="presentation" class="dropdown-header">View this document as...</li>
                                    <li class="divider"></li>
                                    <li><a href="index.php?as=text&file=<?= $f ?>">Plain Text</a></li>
                                    <li><a href="index.php?as=wrap&file=<?= $f ?>">Pretty Source</a></li>
                                    <li><a href="index.php?as=print&file=<?= $f ?>">Printable</a></li>
                                    <li><a href="index.php?as=read&file=<?= $f ?>">Readable</a></li>
                                    <? if( $p['extension'] == 'pub' ) { ?>
                                        <li><a href="raw.php?file=<?= $f ?>">EPUB Archive</a></li>
                                    <? } else { ?>
                                        <li><a href="raw.php?file=<?= $f ?>">Raw Text (no formatting)</a></li>
                                    <? } ?>
                                    <? if( $_GET['as'] ) { ?>
                                        <li><a href="index.php?file=<?= $f ?>">Normal</a></li>
                                    <? } ?>
                                    <? if( in_array( $p['extension'], array( 'collection', 'list' ) ) ) { ?>
                                        <? if( $p['extension'] == "collection" ) { ?>
                                        <li><a href="index.php?file=<?= $f ?>&as=list">List</a></li>
                                        <? } else { ?>
                                        <li><a href="index.php?file=<?= $f ?>&as=collection">Collection</a></li>
                                        <? } ?>
                                        <li><a href="index.php?file=<?= $f ?>&as=csv">Sortable Table</a></li>
                                    <? } ?>
                                    <? if( ASSOC_ENABLE ) { ?>
                                        <li class="divider"></li>
                                        <li role="presentation" class="dropdown-header">Associations...</li>
                                        <li><a href="assoc.php?file=<?= $f ?>">Show All Associated Pages</a></li>
                                        <? if( is_logged_in() ) { ?>
                                            <li><a href="build_assoc.php?file=<?= $f  ?>">Rebuild Associations</a></li>
                                            <li><a href="disassociate.php?file=<?= $f  ?>">Remove Associations</a></li>
                                            <li><a href="index.php?file=<?= implode( "/", array( rtrim( ASSOC_DIR, "/" ), assoc_file_normalize( $f ) ) ) ?>">Association Node</a></li>
                                        <? } ?>
                                    <? } ?>
                                    <? if( CACHE_ENABLE && is_logged_in() ) { ?>
                                    <li class="divider"></li>
                                    <li role="presentation" class="dropdown-header">Groundskeeping...</li>
                                    <li>
                                        <a  href="clear_cache.php?file=<?= $f ?>">Clear Cache for this file</a>
                                    </li>
                                    <? } ?>
                                    <li class="divider"></li>
                                    <li role="presentation" class="dropdown-header">Stats...</li>
                                    <li><a href="stats.php?file=<?= $f ?>">Stats for this file (wordcounts, etc.)</a></li>
                                    <li><a href="work_stats.php?file=<?= $f ?>">Work Stats for this file (time spent, etc.)</a></li>
                                    <li class="divider"></li>
                                    <li><a href="raw.php?file=<?= $f ?>&download=yes">Download</a></li>
                                    <li><a href="delete.php?file=<?= $f  ?>">Delete</a></li>
                                    <li><a href="partition.php?file=<?= $f  ?>">Partition</a></li>
                                    <li><a href="cherrypick.php?file=<?= urlencode( $f ) ?>&commit_before=<?= urlencode( "$c~" ) ?>&commit_after=<?= urlencode( $c ) ?>">Cherrypick last change</a></li>

                                    <li><a href="javascript:view.random_paragraph()"><kbd>R</kbd>andom Paragraph</a></li>
                                    <li class="divider"></li>
                                    <li role="presentation" class="dropdown-header">Scratch</li>
                                    <li><a href="javascript:view.show_highlights( '<?= $f ?>','<?= $c ?>' )">Show Highlights</a></li>
                                </ul>
                            </div>
                        </li>
                        <? if( ASSOC_ENABLE && isset( $p['file_refs'][$commit_file] ) && count( $p['file_refs'][$commit_file] ) > 0) {
                            $refs = $p['file_refs'][$commit_file]; 
                            uksort( 
                                $refs,
                                function( $a, $b ) {
                                    return strcasecmp( basename( $a ), basename( $b ) );
                                }
                            ); 

                            $flagged_refs = array();

                            foreach( $refs as $r ) {
                                if( in_array( "source", $r['directions'] ) ) {
                                    foreach( $r['types'] as $t ) {
                                        foreach( $relationships_to_flag as $it ) {
                                            if( strpos( $t, $it ) === 0 ) {
                                                $flagged_refs[ $t ] = 1;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            $status_links = array_map( 
                                                function( $fr ) use( $f )  {
                                                    return  '<a '
                                                            . 'class="' . he( $fr ) . '" '
                                                            . 'title="' . 'Another document targets this one with a notable relationship' . '" '
                                                            . 'href="assoc.php?file=' 
                                                            . urlencode( $f ) 
                                                            . '&assoc_type=' 
                                                            . urlencode( $fr ) 
                                                            . '">' 
                                                            . he( $fr ) 
                                                            . '</a>';

                                                },
                                                array_keys( $flagged_refs )
                                            );
                            foreach( $status_links as $anchor ) { ?>
                                <li class="status-link"><?= $anchor ?></li>
                            <? } ?>
                        <? } ?>
                        <li id="scratch-activity" class="no-selection"  >
                            <button
                                id="add-to-scratch"
                                class="btn btn-primary navbar-btn"
                            >
                                Add to Scratch
                                <span id="add-to-scratch-word-count"></span>
                            </button>
                        </li>
                        <?= render( 'gen_snippet_widget', array() ) ?>
                    </ul>

                    <ul class="nav navbar-nav navbar-right">
                        <li
                            id="decoration-list-item"
                        >
                            <div class="btn-group" data-toggle="buttons">
                                <label 
                                    class="btn btn-default navbar-btn decoration-checkbox"
                                    for="checkbox-enable-decorations-<?= $m ?>"
                                >
                                    <input
                                        type="checkbox"
                                        class="checkbox-enable-decorations"
                                        id="checkbox-enable-decorations-<?= $m ?>"
                                        title="Enable file decorations"
                                        value="<?= $m ?>"
                                    />
                                    Show <kbd>D</kbd>ecorations
                                </label>
                            </div>
                        </li>
                        <? if( isset( $p['toc'][$commit_file] ) && count( $p['toc'][$commit_file] ) > 0 ) {  ?>
                            <li
                                class="dropdown toc"
                            >
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    Table of Contents
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu scrollable">
                                    <li role="presentation" class="dropdown-header">Go to...</li>
                                    <li>
                                        <a href="#top-<?= $m ?>">Top</a>
                                    </li>
                                    <li>
                                        <a href="#bottom-<?= $m ?>">Bottom</a>
                                    </li>
                                    <li class="divider"></li>
                                    <? foreach( $p['toc'][$commit_file] as $h ) { ?>
                                        <li><?= $h['text'] ?></li>
                                    <? } ?>
                                </ul>
                            </li>
                        <? } ?>
                        <? if( isset( $p['meta'][$commit_file] ) && count( $p['meta'][$commit_file] ) > 0 ) {  ?>
                            <li
                                class="dropdown meta"
                            >
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    Metadata
                                    <span class="caret"></span>
                                </button>
                                <dl class="dropdown-menu scrollable dl-horizontal">
                                    <? foreach( $p['meta'][$commit_file] as $mk => $mvalues ) { ?>
                                        <? foreach ( $mvalues as $mv ) { ?>
                                            <dt>
                                                <a href="meta.php?meta=<?= urlencode( $mk ) ?>">
                                                    <?= he( $mk ) ?>
                                                </a>
                                            </dt>
                                            <dd>
                                                <a href="meta.php?meta=<?= urlencode( "$mk:$mv" ) ?>">
                                                    <?= he( $mv ) ?>
                                                </a>
                                            </dd>
                                        <? } ?>
                                    <? } ?>
                                </dl>
                            </li>
                        <? } ?>

                        <? if( isset( $p['annotations'][$commit_file] ) && count( $p['annotations'][$commit_file] ) > 0 ) {  ?>
                            <li class="dropdown annotations">
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    <?= plural( count( $p['annotations'][$commit_file] ), 'Annotation' ) ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu scrollable">
                                    <li role="presentation" class="dropdown-header">
                                        <?= plural( count( $p['annotations'][$commit_file] ), 'Annotation' ) ?>
                                    </li>
                                    <li class="divider"></li>
                                    <? foreach( $p['annotations'][$commit_file] as $a ) { ?>
                                        <li>
                                            <a class="annotation" href="#<?= $a['key'] ?>"><?= he( $a['content'] ) ?></a>
                                        </li>
                                        <li role="presentation" class="dropdown-header annotation-comment">
                                            <pre class="pre-scrollable"><?= $a['comment'] ?></pre>
                                        </li>
                                    <? } ?>
                                </ul>
                            </li>
                        <? } ?>

                        <? if( isset( $p['file_refs'][$commit_file] ) && count( $p['file_refs'][$commit_file] ) > 0 ) {  ?>
                            <li
                                class="dropdown"
                            >
                                <button
                                    class="btn btn-default navbar-btn dropdown-toggle"
                                    data-toggle="dropdown"
                                >
                                    <?= plural( count( $p['file_refs'][$commit_file] ), 'Association' ) ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu scrollable">
                                    <li role="presentation" class="dropdown-header">File Associations</li>
                                    <li class="divider"></li>
                                    <? 
                                        $refs = $p['file_refs'][$commit_file]; 
                                        uksort( 
                                            $refs,
                                            function( $a, $b ) {
                                                return strcasecmp( basename( $a ), basename( $b ) );
                                            }
                                        ); 
                                    ?>
                                    <?foreach( $refs as $ref_path => &$ref ) { ?>
                                        <li class="<?= he( join( " ", $ref['types'] ) ) ?> <?= he( join( " ", $ref['directions'] ) ) ?>">
                                            <span><?= linkify( '[[' . undirify( $ref_path ) . ']]', array( 'title' => he( join( ",", $ref['types'] ) . ": " . undirify( $ref_path ) ) ) ) ?></span>
                                        </li>
                                    <? } ?>
                                </ul>
                            </li>
                        <? } ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="view container-fluid meta-off <?= implode( " ", $path_components  ) ?>" id="<?= $m ?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="panel-title"><?= linkify(
                                '[[' . $f . ']]',
                                array(
                                    'minify'    =>  true
                                )
                            ) ?></span>
                        </div>
                        <div class="panel-body">
                            <div 
                                class="view file <?= $p['extension'] ?>" 
                                data-file="<?= $f ?>"
                                data-commit="<?= $c ?>"
                            >

                                <a name="top-<?= $m ?>"></a>
                                <?=  $content ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a name="bottom-<?= $m ?>"></a>
        </div>
    </div>
    <script type="text/javascript">

        (function( $ ) {
            $(document).ready(
                function() {

                    var opts = {};

                    opts.highlightify_content = <?= json_encode( get_highlightify_content() ) ?>;

                    view.setup( opts );

                    snippets.setup();
                }
            );
        })(jQuery);
    </script>
    <? if( ANNOTATORJS_ENABLE && is_logged_in() ) { ?>
    <script type="text/javascript">
        $(document).ready( function() {
            console.log('Initializing AnnotatorJS' );
    
            var selector = <?= json_encode( 
                        "." . implode( ".", $path_components ) . " .view.file"
                    )
                ?>;
    
            giterary_annotator.init( 
                selector,
                <?= json_encode( $f ) ?>,
                <?= json_encode( ANNOTATORJS_PREFIX ) ?>
            );
    
            console.log('Done initializing AnnotatorJS' );
        } );
    </script>
    <? } ?>
<? } ?>
