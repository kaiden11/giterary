<? renderable( $p ) ?>
<? 
$stash['css'][] = 'view.css';
$stash['css'][] = 'display.css';
$stash['css'][] = 'table.css';
$stash['css'][] = 'read.css';
// Base JS
# $stash['core_js'][] = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['core_js'][]   = 'jquery.scrollTo-2.1.2-min.js';
$stash['js'][] = 'view.js';
// $stash['js'][] = 'view.annotations.js';

$stash['js'][] = 'snippets.js';
$stash['js'][] = 'read.js';

?>
<? $stash['body_classes'] = array(); ?>
<?  foreach( $p['view'] as $commit_file => &$content ) { ?>
    <? 
    list( $c, $f ) = explode( ":", $commit_file );
    $m = md5( $commit_file ); 
    $path_components = explode( "/", undirify( $f ) );
    $body_class_components = array_map( "path_to_filename", $path_components );
    $stash['body_classes'] = array_merge( $stash['body_classes'], $body_class_components );
    $stash['body_classes'][] = 'read';
    $stash['body_classes'][] = 'readable';

    // Setting up a few common names
    $df = dirify( $f, true );
    $dirf = ( ( $f == DEFAULT_FILE || strpos( $f, '/' ) === false ) ? dirify( DEFAULT_FILE, true ) : dirify( dirname( $f ), true ) );
    $uf = undirify( $f );

    $read_opts = array();

    if( is_logged_in() ) {
        $read_opts['bookmark'] = array();

        $b = bookmark_get(
            $_SESSION['usr']['name'],
            $f,
            $c
        );


        $read_opts['bookmark'] = $b;
    }

    ?>
    <style type="text/css">
        * {
            -webkit-print-color-adjust: exact;
        }
    </style>
    <div class="view <?= he( $p['extension'] ) ?>">
        <nav class="navbar navbar-default navbar-fixed-bottom navbar-inverse">
            <div class="container-fluid">
                <ul class="nav navbar-nav">
                    <li class="btn-group">
                        <button 
                            id="bookmark"
                            class="btn btn-default navbar-btn" 
                            title="Bookmark your place in this file"
                        >
                            <kbd>S</kbd>et
                        </button>
                        <button 
                            id="recover"
                            class="btn btn-success navbar-btn" 
                            title="Return screen to your bookmark"
                        >
                            <kbd>R</kbd>ecover
                        </button>
                    </li>
                    <li>

                        <div class="navbar-right navbar-text progress" style="width: 100px";>
                            <div 
                                class="progress-bar progress-bar-success" 
                                id="read-percentage"
                                role="progressbar" 
                                aria-valuemin="0" 
                                aria-valuemax="100" 
                                style="width: 3rem; min-width: 3rem;"
                            >
                              %
                            </div>
                        </div>
                    </li>
                    <li>
                        <a 
                            href="index.php?file=<?= urlencode( $f ) ?>"
                        >
                            Go Back
                        </a>
                    </li>
                    <li>
                        <span id="error" class="navbar-text text-warning" ></span>
                    </li>
                </ul>
                <? /*
                    <span id="read-percentage" title="Percent paragraphs read">-</span> /
                    <span id="scroll-percentage" title="Percentage page scrolled">-</span>

                </span>
                */ ?>
                <ul class="nav navbar-nav navbar-right">
                    <?= render( 'gen_snippet_widget', array() ) ?>
                </ul>
            </div>
        </nav>
        <div class="view container-fluid meta-off <?= implode( " ", $path_components  ) ?>" id="<?= $m ?>">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="view file <?= $p['extension'] ?>">
                        <?=  $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        (function( $, read ) {
            if( $ && read ) {

                $( document ).ready( function() {

                    $( '.view.file' )
                        .data( 'file', <?= json_encode( $f ) ?> )
                        .data( 'commit', <?= json_encode( $c ) ?> )
                    ;

                    read.setup( 
                        <?= json_encode( $c ) ?>,
                        <?= json_encode( $f ) ?>,
                        <?= json_encode( $read_opts ) ?>
                    );

                    // view.setup_selection();
                    snippets.setup();
                });
            }
        })( jQuery, read );

    </script>
<? } ?>
