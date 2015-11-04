<? renderable( $p );

$args = $p['args'];

$show   = set_or( $args['show'],    array( 'h1', 'h2', 'h3' ) );
$noshow = set_or( $args['noshow'],  array( 'h4', 'h5', 'h6' ) );

// Normalize 
if( !is_array( $show ) ) {
    $show = array( $show );
}

if( !is_array( $noshow ) ) {
    $noshow = array( $noshow );
}

$show = array_map(
    function( $a ) {
        return trim( strtolower( $a ) );
    },
    $show
);

$noshow = array_map(
    function( $a ) {
        return trim( strtolower( $a ) );
    },
    $noshow
);

// Filter

$show = array_filter( 
    $show,
    function( $a ) {
        return preg_match( '/^h[1-6]$/', $a ) != 0;
    }
);

$noshow = array_filter( 
    $noshow,
    function( $a ) {
        return preg_match( '/^h[1-6]$/', $a ) != 0;
    }
);




?>
<ul class="toc funcify">
    <? /* <li role="presentation" class="dropdown-header">Go to...</li> */ ?>
    <? /*
    <li>
        <a href="#top-<?= $m ?>">Top</a>
    </li>
    <li>
        <a href="#bottom-<?= $m ?>">Bottom</a>
    </li>
    */ ?>
    <? /* <li class="divider"></li> */ ?>
    <? if( is_array( $p['toc_headers'] ) && count( $p['toc_headers'] ) > 0 ) { ?>
        <? foreach( $p['toc_headers'] as $h ) { ?>
            <? if( in_array( $h['tag'], $show ) && !in_array( $h['tag'], $noshow ) ) { ?>
                <li><?= $h['text'] ?></li>
            <? } ?>
        <? } ?>
    <? } ?>
</ul>

