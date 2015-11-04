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

?>
<div class="todo todos container-fluid">
    <div class="todo todos display table-responsive">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span><?= plural( count( $p['search'] ), "document") ?> with annotations</span>
            </div>
            <div class="panel-body">
                Documents with annotations embedded in them .
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
                    <?  foreach( $p['search'] as $file =>  &$s ) { ?>
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
                                                <code><?= todoify( he( preg_replace( '/@@(.+?)@@/', '<span class="match">\1</span>', $match ) ) ) ?></code>
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
                <? } ?>
            </table>
        </div>
    </div>
</div>

