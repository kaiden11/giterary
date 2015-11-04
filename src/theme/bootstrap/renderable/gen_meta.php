<? renderable( $p ); ?>
<? 
$stash['css'][] = 'search.css';
$stash['css'][] = 'table.css';
GLOBAL $php_meta_header_pattern;
ksort( $p['search'] );

function meta_url( $meta = array() ) {
    $ret = "meta.php";

    if( is_array( $meta ) && count( $meta ) > 0 ) {
        $ret .= '?';

        $ret .= join( '&', 
            array_map(
                function( $a ) {
                    return 'meta=' . urlencode( $a );
                },
                $meta
            )
        );
    }

    return $ret;

}

?>
<div class="meta container-fluid">
    <div class="meta display">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div>
                    <span>Searching documents with the following metadata headers:
                    <?= implode( ", ", $p['meta'] ) ?>
                    [<a href="meta.php">All Metadata Headers</a>]
                    </span>
                </div>
            </div>
            <div class="panel-body">
                <div>
                    <form action="meta.php" method="get">
                        <input type="text"   name="meta"     id="meta-search" value="">
                        <input type="submit"                                value="Search for another metadata header">
                    </form>
                </div>
                <div>
                    <? if( count( $p['meta'] ) > 0 ) { ?>
                    <form action="meta.php" method="get">
                        <? foreach( $p['meta'] as $t ) { ?>
                            <input type="hidden"   name="meta" value="<?= $t ?>">
                        <? } ?>
                        <input type="text"   name="meta"     id="and-meta-search" value="">
                        <input type="submit"                                value="... And another header">
                    </form>
                    <? } ?>
                </div>
                <table class="tabulizer" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="text-align: left"><span>@File</span></th>
                            <th style="text-align: left"><span>@Matching metadata</span></th>
                            <th style="text-align: left"><span>@Other metadata</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    <? if( count( $p['search'] ) <= 0 ) { ?>
                            <tr>
                                <td colspan="2">No content matches</td>
                            </tr>
                    <? } else { ?>
                        <?  foreach( $p['search'] as $file =>  &$s ) { ?>
                                <tr class="heading">
                                    <td>
                                        <?= linkify( '[[' . undirify( $file ) . ']]' ) ?>
                                    </td>
                                    <td>
                                        <? if( $s['type'] == 'contents match' ) { ?>
                                            <? ksort( $s['match'] ); ?>
                                            <? foreach( $s['match'] as $meta => &$values ) { ?>

                                                <? 
                                                $mv = false;
    
                                                $match = array();
                                                if( preg_match( $php_meta_header_pattern, "%$meta", $match ) == 1 ) {
                                                    $meta   = $match[ 3 ];
                                                    $mv     = $match[ 4 ];
                                                }

                                                ?>
                                                <div>
                                                    <code>
                                                        <a 
                                                            href="<?= meta_url( 
                                                                ( $mv !== false ? array( "$meta:$mv" ) : array( $meta ) )
                                                            ) ?>"
                                                        >
                                                            <?= he( ( $mv !== false ? "$meta:$mv" : $meta ) ) ?>
                                                        </a>
                                                    </code>
                                                    <? foreach( $values as $v ) { ?>
                                                        <? if( $mv !== false && $v == $mv ) { continue; } ?>
                                                            <a 
                                                                href="<?= meta_url( 
                                                                    array_merge(
                                                                        array_filter(
                                                                            array_keys( $s['match'] ),
                                                                            function( $a ) use ( $meta ) {
                                                                                return $a != $meta;
                                                                            }
                                                                        ),
                                                                        array( "$meta:$v" )
                                                                    )
                                                                ) ?>"
                                                            >
                                                                <kbd>
                                                                +<?= he( $v ) ?>
                                                                </kbd>
                                                            </a>
                                                    <? } ?>
                                                </div>
                                            <? } ?>
                                        <? } else { ?> 
                                            ?
                                        <? } ?>
                                    </td>
                                    <td>
                                        <? sort( $s['all_meta'] ); ?>
                                        <? foreach( $s['all_meta'] as $t ) { ?>
                                            <? if( !in_array( $t, array_keys( $s['match'] ) ) ) { ?>
                                                <code>
                                                    <a 
                                                        href="<?= meta_url( 
                                                            array_merge( 
                                                                array_keys( $s['match'] ), 
                                                                array( $t ) 
                                                            ) 
                                                        ) ?>"
                                                    >
                                                        <?= he( $t ) ?>
                                                    </a>
                                                </code>
                                            <? } ?>
                                        <? } ?>
                                    </td>
                                </tr>
                        <? } ?>
                    <? } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">
                               <?= plural( count( $p['search'] ), "matched record", "s" ) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            $("#meta-search").text_suggest( "meta name..." );
            $("#and-meta-search").text_suggest( "<?= implode(", ", $p['meta'] ) . ' and...' ?>" );

            $('.tabulizer').tabulizer();
        }
    );


</script>
