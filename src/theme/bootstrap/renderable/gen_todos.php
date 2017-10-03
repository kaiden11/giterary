<? renderable( $p ); ?>
<? $stash['css'][] = 'search.css'; ?>
<? /* ksort( $p['search'] ); */ ?>
<?  

if( CACHE_ENABLE ) {
    uasort( 
        $p['search'],
        function( $a, $b ) {
            return ( $a['author_date_epoch'] < $b['author_date_epoch'] ? 1 : -1 ); // reverse
        }
    ); 
}

$dirs = array();
if( isset( $p['directories'] ) && is_array( $p['directories'] ) ) {

    $dirs = array_map(
        function( $a ) {
            return array( 
                'value' =>  $a,
                'label' =>  undirify( $a, false ) . '/'
            );
        },
        $p['directories']
    );
}

?>
<div class="todo todos container-fluid">
    <div class="todo todos display table-responsive">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span><?= plural( count( $p['search'] ), "document") ?> with TODOs or TBDs, <?= plural( array_sum( array_map( function( $a ) { return $a['count']; }, $p['search'] ) ), 'pending  item' ) ?></span>
            </div>
            <div class="panel-body">
                Documents with TODO or TBD text embedded in them (without a leading "\" as an escape character).
                <div>
                    <form action="todos.php" method="get" id="dir-lookup">
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="file" 
                                class="form-control" 
                                id="file"
                                value="<?= ( $p['file'] ? $p['file'] : "" ) ?>"
                                placeholder="Type to complete the name of an existing directory in which to search for TODOs" 
                            />
                            <span 
                                class="input-group-btn"
                            >
                                <input
                                    type="submit"
                                    class="btn btn-success"
                                />
                            </span>
                        </div>
                    </form>
                </div>
                <div>
                    Alternatively, you can view the 
                    <a href="todo_hierarchy.php">TODOs by directory hierarchy.</a>
                </div>
                <script type="text/javascript">
                    var todo = {
                        directories: <?= json_encode( $dirs ) ?>,
                        setup: function() {
                            $('#file').autocomplete( 
                                { 
                                    source: todo.directories,
                                    select: function( evt, ui ) {
                                        $('#file').val( ui.item.label );
                                        $('#dir-lookup').submit();
                                    }

                                } 
                            );
                        }
                    };
                
                    $(document).ready( todo.setup );
                </script>
            </div>
            <table class="table table-hover table-striped table-condensed">
                <tr>
                    <th>file</th>
                    <th>latest commit</th>
                    <th>match type</th>
                </tr>
                <? if( count( $p['search'] ) <= 0 ) { ?>
                        <tr>
                            <td colspan="2">No content matches</td>
                        </tr>
                <? } else { ?>
                    <? $total = 0; ?>
                    <?  foreach( $p['search'] as $file =>  &$s ) { ?>
                            <? $total += $s['count']; ?>
                            <tr class="heading">
                                <td>
                                    <?= linkify( '[[' . undirify( $file ) . ']]' ) ?>
                                </td>
                                <td>
                                    <a href="show_commit.php?commit=<?= $s['latest_commit'] ?>"><?= medium_time_diff( $s['author_date_epoch' ], time() ) ?></a>
                                </td>
                                <td>
                                    <? if( $s['type'] == 'both' ) { ?>
                                        <?= plural( $s['count'], 'line' ) ?> + filename
                                    <? } elseif( $s['type'] == 'contents match' ) { ?>
                                        <?= plural( $s['count'], 'line' ) ?> match
                                    <? } elseif( $s['type'] == 'external' ) { ?>
                                        <?= plural( $s['count'], 'line' ) ?> annotated
                                    <? } else { ?>
                                        File name match
                                    <? } ?>
                                </td>
                            </tr>
                            <? if( $s['type'] == "contents match" || $s['type'] == "both" || $s['type'] == "external" ) { ?>
                                <? $i = 0 ?>
                                <? foreach( $s['match'] as $match ) { ?>
                                    <tr>
                                        <td colspan="3">
                                            <div style="margin-left: 2em;">
                                                <samp><?= todoify( he( preg_replace( '/@@(.+?)@@/', '<span class="match">\1</span>', $match ) ) ) ?></samp>
                                            </div>
                                        </td>
                                    </tr>
                                    <? $i++ ?>
                                    <? if( $i >= 5 && $i < count( $s['match'] ) ) { ?>
                                    <tr>
                                        <td colspan="2">
                                            <div style="margin-left: 2em;">And <?= ( count( $s['match'] ) - $i  ) ?> more...</div>
                                        </td>
                                    </tr>
                                    <? break; ?>
                                    <? } ?>
                                <? } ?>
                            <? } ?>
                    <? } ?>
                    <tr>
                        <td>
                            Total
                        </td>
                        <td>
                            <?= plural( $total, "match", "es" ); ?>
                        </td>

                    </tr>
                <? } ?>
            </table>
        </div>
    </div>
</div>

