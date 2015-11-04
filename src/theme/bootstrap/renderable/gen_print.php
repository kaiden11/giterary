<? renderable( $p ) ?>
<? 
$stash['css'][] = 'view.css';
$stash['css'][] = 'display.css';
$stash['css'][] = 'table.css';
// Base JS
$stash['core_js'][] = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['js'][] = 'view.js';
$stash['js'][] = 'view.annotations.js';

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
    <style type="text/css">
        * {
            -webkit-print-color-adjust: exact;
        }
    </style>
    <div class="view">
        <div class="view container-fluid meta-off <?= implode( " ", $path_components  ) ?>" id="<?= $m ?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="view file <?= $p['extension'] ?>">
                        <?=  $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? } ?>
