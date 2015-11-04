<? renderable( $p ); ?>
<? $stash['css'][] = 'search.css'; ?>
<? $stash['css'][] = 'table.css'; ?>
<? ksort( $p['search'] ); ?>
<?
function tag_url( $tags = array() ) {
    $ret = "tags.php";

    if( is_array( $tags ) && count( $tags ) > 0 ) {
        $ret .= '?';

        $ret .= join( '&', 
                    array_map(
                        function( $a ) {
                            return 'tag=' . urlencode( preg_replace( '@^~@', '', $a ) );
                        },
                        $tags
                    )
                );
    }

    return $ret;

}

?>
<div class="tags container-fluid">
    <div class="tags display">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div>
                    <span>Searching documents the following tags: 
                    <?= implode( ", ", $p['tags'] ) ?>
                    [<a href="tags.php">All Tags</a>]
                    </span>
                </div>
            </div>
            <div class="panel-body">
                <div>
                    <form action="tags.php" method="get">
                        <input type="text"   name="tag"     id="tag-search" value="">
                        <input type="submit"                                value="Search for another tag">
                    </form>
                </div>
                <div>
                    <? if( count( $p['tags'] ) > 0 ) { ?>
                    <form action="tags.php" method="get">
                        <? foreach( $p['tags'] as $t ) { ?>
                            <input type="hidden"   name="tag" value="<?= $t ?>">
                        <? } ?>
                        <input type="text"   name="tag"     id="and-tag-search" value="">
                        <input type="submit"                                value="... And another tag">
                    </form>
                    <? } ?>
                </div>
                <table class="tabulizer" style="width: 100%">
                    <thead>
                        <tr>
                            <th style="text-align: left"><span>@File</span></th>
                            <th style="text-align: left"><span>@Matching tags</span></th>
                            <th style="text-align: left"><span>@Other tags</span></th>
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
                                            <? foreach( $s['match'] as $i => $match ) { ?>
                                                <div>
                                                    <code><a href="<?= tag_url( array( $match ) ) ?>"><?= he( $match ) ?></a></code>
                                                </div>
                                            <? } ?>
                                        <? } else { ?> 
                                            ?
                                        <? } ?>
                                    </td>
                                    <td>
                                        <? sort( $s['all_tags'] ); ?>
                                        <? foreach( $s['all_tags'] as $m ) { ?>
                                            <? if( !in_array( $m, $s['match'] ) ) { ?>
                                                <code>
                                                    <a href="<?= tag_url( array_merge( $s['match'], array( $m ) ) ) ?>"><?= he( $m ) ?></a>
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
            $("#tag-search").text_suggest( "tag name..." );
            $("#and-tag-search").text_suggest( "<?= implode(", ", $p['tags'] ) . ' and...' ?>" );

            $('.tabulizer').tabulizer();
        }
    );


</script>
