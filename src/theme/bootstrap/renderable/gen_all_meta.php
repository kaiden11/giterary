<? renderable( $p ); ?>
<? 
$stash['css'][] = 'search.css'; 
$stash['css'][] = 'table.css';

$num_meta = 0;
$num_meta_in_docs = 0;
?>
<? ksort( $p['meta'] ); ?>
<div class="all meta container-fluid">
    <div class="all meta display">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>All Metadata Tags</span>
            </div>
            <div class="panel-body">
                <div>
                    <span>Displaying all metadata headers found</span>
                </div>
                <div>
                    <form action="meta.php" method="get">
                        <input type="text"   name="meta"     id="meta-search" value="">
                        <input type="submit"                                value="Search for a metadata header">
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="tabulizer" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="text-align: left"><span>Metadata Header</span></th>
                                <th style="text-align: left"><span>#Number of documents with header</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <? if( count( $p['meta'] ) <= 0 ) { ?>
                        <tr>
                            <td colspan="2">No content matches</td>
                        </tr>
                        <? } else { ?>
                            <?

                            ?>
                            <?  foreach( $p['meta'] as $meta =>  &$files ) { ?>
                                <? $num_meta++; $num_meta_in_docs += count( $files ); ?>
                                    <tr class="heading">
                                        <td>
                                            <div>
                                                <code><a href="meta.php?meta=<?= urlencode( preg_replace( '@^~@', '', $meta ) ) ?>"><?= he( $meta ) ?></a></code>
                                            </div>
                                        </td>
                                        <td>
                                            <?= plural( count( $files ), 'document', 's' ) ?>
                                        </td>
                                    </tr>
                            <? } ?>
                        <? } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <?= plural( 
                                        $num_meta, 
                                        'total metadata header', 
                                        's' 
                                    ) ?>
                                </td>
                                <td>
                                    <?= plural( 
                                        $num_meta_in_docs, 
                                        'total metadata header', 
                                        's' 
                                    ) . ' in documents' ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            $("#meta-search").text_suggest( "metadata header name..." );

            $('.tabulizer').tabulizer();
        }
    );


</script>
