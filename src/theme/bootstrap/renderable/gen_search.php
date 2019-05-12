<? renderable( $p ); ?>
<? $stash['css'][] = 'search.css'; ?>
<? ksort( $p['search'] ); ?>
<div class="search container-fluid">
    <div class="row">
        <div class="col-md-12">
            <fieldset>
                <legend>
                    Searching for &quot;<?= $p['term'] ?>&quot;
                    <?php if( isset( $p['directory'] ) && $p['directory'] ) { ?>
                        in <a href="index.php?file=<?= urlencode( $p['directory'] ) ?>">
                            <?= basename( $p['directory'] ) ?>
                        </a>
                    <?php } ?>
                </legend>

                <div class="search display">
                    <table class="table table-hover table-striped table-condensed">
                        <tr>
                            <th>file</th>
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
                                            <? if( $s['type'] == 'both' ) { ?>
                                                
                                                <?= plural( $s['count'], 'line' ) ?> + filename
                                            <? } elseif( $s['type'] == 'contents match' ) { ?>
                                                <?= plural( $s['count'], 'line' ) ?> match
                                            <? } else { ?>
                                                File name match
                                            <? } ?>
                                        </td>
                                    </tr>
                                    <? if( $s['type'] == "contents match" || $s['type'] == "both" ) { ?>
                                        <? $i = 0 ?>
                                        <? foreach( $s['match'] as $match ) { ?>
                                            <tr>
                                                <td colspan="2">
                                                    <div style="margin-left: 2em;">
                                                        <code><?= preg_replace( '/@@(.+?)@@/', '<span class="match">\1</span>', he( $match ) ) ?></code>
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
            </fieldset>
        </div>
    </div>
</div>
    
