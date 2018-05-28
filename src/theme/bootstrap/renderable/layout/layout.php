<? renderable( $p ) ?>
<? 
$header = '';
$footer = null;

$header = '';
$header .= '<!DOCTYPE html>' . "\n";
$header .= '<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>';
if($stash['pg_title'] != NULL) {
    # $header .= SITE_NAME . ' // ' . $stash['pg_title'];
    $header .=  he( $stash['pg_title'] ) . ' // ' . he( SITE_NAME );
} else {
    $header .= he( SITE_NAME );
}
$header .=  '
        </title>';


# $header .= '<script src="js/prototype.js" type="text/javascript"></script>';
$header .= '<script type="text/javascript" src="js/jquery-1.11.0.min.js"></script>';
$header .= '<script type="text/javascript" src="js/jquery-ui.js"></script>';
// $header .= '<script type="text/javascript" src="js/jquery-ui-1.8.18.custom.min.js"></script>';
$header .= '<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>';
$header .= '<script type="text/javascript" src="js/layout.js"></script>';
$header .= '<script type="text/javascript" src="js/util.js"></script>';

$header .= '<script type="text/javascript" src="theme/bootstrap/js/quick-nav.js"></script>';
$header .= '<script type="text/javascript" src="theme/bootstrap/js/bootstrap.min.js"></script>';
$header .= '<script type="text/javascript" src="theme/bootstrap/js/layout.js"></script>';

if($stash['core_js'] != null ) {

    if( !is_array( $stash['core_js'] ) ) {
        $stash['core_js'] = array( $stash['core_js'] );
    }

    foreach( $stash['core_js'] as $js ) {

            $header .= '
                <script type="text/javascript" src="js/' . $js . '"></script>';
    }
}

if($stash['js'] != null ) {

    if( !is_array( $stash['js'] ) ) {
        $stash['js'] = array( $stash['js'] );
    }

    foreach( $stash['js'] as $js ) {

            $header .= '
                <script type="text/javascript" src="theme/bootstrap/js/' . $js . '"></script>';
    }
}


if( is_mobile() ) {
    # $header .= '<meta name="viewport" content="width=800px, initial-scale=0.5">';
}

$header .= '<link rel="stylesheet" type="text/css" href="' . CSS_DIR . 'normalize.css' . '" />';
$header .= '<link rel="stylesheet" type="text/css" href="' . CSS_DIR . 'simpler.v2.flags.css' . '" />';

$header .= '<link rel="stylesheet" type="text/css" href="theme/bootstrap/css/bootstrap.min.css' . '" />';
$header .= '<link rel="stylesheet" type="text/css" href="theme/bootstrap/css/giterary.bootstrap.layout.css' . '" />';

if($stash['css'] != null ) {

    if( !is_array( $stash['css'] ) ) {
        $stash['css'] = array( $stash['css'] );
    }

    foreach( $stash['css'] as $css ) {

            $header .= '
                <LINK REL="stylesheet" TYPE="text/css" HREF="theme/bootstrap/css/' . $css . '">';
    }
}

$header .= '<link rel="shortcut icon" href="favicon.ico?v=2" />';
if( is_logged_in() ) {
    if( $_SESSION['usr']['name'] != "" ) {
    
        $user_css_path = $_SESSION['usr']['name'] . "/style.css" ;
        if( git_file_exists( $user_css_path  ) ) {

            $header .= '<link rel="stylesheet" type="text/css" href="raw.php?file=' . urlencode( $user_css_path ) . '" />';
        }

        $user_js_path = $_SESSION['usr']['name'] . "/script.js" ;
        if( git_file_exists( $user_js_path  ) ) {

            $header .= '<script type="text/javascript" src="raw.php?file=' . urlencode( $user_js_path ) . '"></script>';
        }
    }
}
$header .= "\n</head>\n";
$header .= "\n<body id=\"theBody\" class=\"giterary meta-on " . ( is_array( $stash['body_classes'] ) ? implode( " ", $stash['body_classes']  ) : "" ) . "\">\n";
# $header .= "\n<body id=\"theBody\" class=\"giterary " . ( is_array( $stash['body_classes'] ) ? implode( " ", $stash['body_classes']  ) : "" ) . "\">\n";

echo $header;
unset( $header );

$meta_contents = array();

if( isset( $p['contents']['header'] ) ) {
    $meta_contents[] = $p['contents']['header'];
    unset( $p['contents']['header'] );
} else {
    $meta_contents[] = gen_header();
}

if( isset( $p['contents']['footer'] ) ) {
    $footer = $p['contents']['footer'];
    unset( $p['contents']['footer'] );
} else {
    # We can't *really* rely on generating the footer
    # this early in the game. We'll wait until
    # later to generate, if we really need to.
}

if( isset( $p['contents']['navigation'] ) ) {
    $meta_contents[] = $p['contents']['navigation'];
    unset( $p['contents']['navigation'] );
} else {
    $meta_contents[] = gen_nav(); 
}

?>
<div class="meta container-fluid">
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
<? 
echo implode( "", $meta_contents );
unset( $meta_contents );
?>
        </div>
    </nav>
<!-- end meta -->
</div>
<div class="content">
    <div class="content container-fluid">
<?
foreach( $p['contents'] as $name => $content ) {
    echo $content;
    echo "
<!-- end $name -->
";

}
?>  
    </div>
</div>
<div class="sub container-fluid">
<?
if( is_null( $footer ) ) {
    $footer = gen_footer();
}

echo $footer;
?>
</div>
<div class="modal fade" id="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body">
                Test
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
