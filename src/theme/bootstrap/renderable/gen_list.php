<? renderable( $p );
if( isset( $p['args']['sort'] ) ) {
    $sort = $p['args']['sort'];

    if( in_array( $sort,  array( "descending","reverse" ) ) ) {
        rsort( $p['file_collection'] );
    } elseif( in_array( $sort, array( "forward", "ascending" ) ) ) {
        sort( $p['file_collection'] );

    } elseif( in_array( $sort, array( "orig", "original","none" ) ) ) {
        # Do nothing
    } else {
        sort( $p['file_collection'] );
    }

} else {
    sort( $p['file_collection'] ); 
}

$p['file_collection'] = array_unique( $p['file_collection'] );

$num = false;
if( isset( $p['args']['num'] ) ) {

    $num = numeric_or( $p['args']['num'], false );
    $num = ( $num > 0 ? $num : false );
}

if( $num !== false ) {
    $p['file_collection'] = array_splice( $p['file_collection'], 0, $num );
}

?>
<ul class="file-collection">
<? if( count( $p['file_collection'] ) <= 0 ) { ?>
    <li>No items found.</li>
<? } else { ?>
    <? foreach( $p['file_collection'] as $file ) { ?>
        <li>
            <div>
            <? if( $p['args']['display'] == "basename" ) { ?>
                <?= linkify( '[[' . undirify( $file ) . '|' . basename( $file ) . ']]' ) ?>
            <? } elseif( $p['args']['display'] == "whole" ) { ?>
                <?= linkify( '[[' . undirify( $file ) . '|' . undirify( $file ) . ']]' ) ?>
            <? } elseif( is_numeric( $p['args']['display'] ) ) { ?>
                <?= linkify( '[[' . undirify( $file ) . '|' . implode( '/', array_slice( explode( "/", undirify( $file ) ), $p['args']['display'] ) ) . ']]' ) ?>
            <? } else { ?>
                <?= linkify( '[[' . undirify( $file ) . ']]' ) ?>
            <? } ?>
            <? 
                $use_as_template = set_or( $p['args']['use_as_template'], false );
            ?><? if( $use_as_template ) { ?>
                <? if( $use_as_template == "yes" ) { ?>
                        (<a
                            class="edit"
                            href="template.php?template=<?= urlencode( $file ) ?>"
                        >Use as a template</a>)
                <? } else { ?>
                        (<a
                            class="edit"
                            href="template.php?file=<?= urlencode( strftime( $use_as_template ) ) ?>&template=<?= urlencode( $file ) ?>"
                        >Use as a template</a>)
                <? } ?>
            <? } ?>
            </div>
            <? if( isset( $p['args']['excerpt'] ) ) { 
                $c = git_file_get_contents( $file );
                $e = '';

                if( is_numeric( $p['args']['excerpt'] ) ) {

                    $e = he( excerpt( $c, $p['args']['excerpt'] ) );

                } elseif( $p['args']['excerpt'] == "line" ) {

                    $s = preg_split( '/^([\*\-\_]\s*){3,}$/m', $c );

                    if( count( $s ) > 1 ) {
                        $e = array_shift( $s );

                        // Remove any title elements
                        // $e = preg_replace( '/^(#){1,6}.*$/', '', $e );

                        // TODO: underline and underequals headers
                        //
                        //   Ex:
                        //
                        //   My Title          My Title
                        //   --------          ========
                        //

                    } else {
                        $e = he( excerpt( $c, 100 ) );
                    }

                    
                } elseif( $p['args']['excerpt'] == "all" ) {
                    $e = $c;
                }
                ?>
                <code class="list-excerpt"><?= ne( $e ) ?></code>
            <?  } ?>
        </li>
    <? } ?>
<? } ?>
</ul>
