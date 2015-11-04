<? renderable( $p );

?>
<ul class="file-collection">
<? if( count( $p['file_collection'] ) <= 0 ) { ?>
    <li>No items found.</li>
<? } else { ?>
    <? foreach( $p['file_collection'] as $file => &$details ) { ?>
        <li>
            <div>
            <? if( $p['show_type'] === true ) { ?>
                <?= implode( 
                    ',', 
                    array_map( 
                        function( $a ) {
                            return "<span class=\"$a\">$a</span>";
                        },
                        $details["types"] 
                    )
                ) ?>:
            <? }?>
            <? if( $p['args']['display'] == "basename" ) { ?>
                <?= linkify( '[[' . undirify( $file ) . '|' . basename( $file ) . ']]' ) ?>
            <? } elseif( is_numeric( $p['args']['display'] ) ) { ?>
                <?= linkify( '[[' . undirify( $file ) . '|' . implode( '/', array_slice( explode( "/", undirify( $file ) ), $p['args']['display'] ) ) . ']]' ) ?>
            <? } else { ?>
                <?= linkify( '[[' . undirify( $file ) . ']]' ) ?>
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
                <code><?= ne( $e ) ?></code>
            <?  } ?>
        </li>
    <? } ?>
<? } ?>
</ul>
