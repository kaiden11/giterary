<? renderable( $p ); ?>
<? 
$stash['css'][] = 'search.css'; 
$stash['css'][] = 'table.css';

$num_tags = 0;
$num_tags_in_docs = 0;
?>
<? ksort( $p['tags'] ); ?>
<div class="all tags container-fluid">
    <div class="all tags display">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>All Tags</span>
            </div>
            <div class="panel-body">
                <div>
                    <span>Displaying all tags found</span>
                </div>
                <div>
                    <form action="tags.php" method="get">
                        <input type="text"   name="tag"     id="tag-search" value="">
                        <input type="submit"                                value="Search for a tag">
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="tabulizer" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="text-align: left"><span>@Tag Name</span></th>
                                <th style="text-align: left"><span>#Number of documents with tag</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <? if( count( $p['tags'] ) <= 0 ) { ?>
                        <tr>
                            <td colspan="2">No content matches</td>
                        </tr>
                        <? } else { ?>
                            <?

                            ?>
                            <?  foreach( $p['tags'] as $tag =>  &$files ) { ?>
                                <? $num_tags++; $num_tags_in_docs += count( $files ); ?>
                                    <tr class="heading">
                                        <td>
                                            <div>
                                                <code><a href="tags.php?tag=<?= urlencode( preg_replace( '@^~@', '', $tag ) ) ?>"><?= he( $tag ) ?></a></code>
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
                                    <?= plural( $num_tags, 'total tag', 's' ) ?>
                                </td>
                                <td>
                                    <?= plural( $num_tags_in_docs, 'total tag', 's' ) . ' in documents' ?>
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
            $("#tag-search").text_suggest( "tag name..." );

            $('.tabulizer').tabulizer();
        }
    );


</script>
