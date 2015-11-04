<? renderable( $p ); ?>
<div class="assoc container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            Pages associated with <?= linkify( '[[' . undirify( $p['file'] ) . '|' . basename( $p['file'] ) . ']]' ) ?>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-condensed">
                    <tr>
                        <th colspan="4">
                            <h3>Targets (from <?= basename( undirify( $p['file'] ) ) ?> to others)</h3>
                        </th>
                    </tr>
                    <tr>
                        <th>Association Type</th>
                        <th>Path</th>
                        <th>Sequence</th>
                        <th>&nbsp;</th>
                    </tr>
                <?
                    # Sort targets by their sequence
                    usort(
                        $p['targets'],
                        function( $a, $b ) {
                            $r = strcmp( $a['type'], $b['type'] );

                            return ( 
                                $r == 0 
                                    ? $a['sequence'] - $b['sequence']
                                    : $r
                            );
                        }
                    );

                ?>
                <? foreach( $p['targets'] as &$a ) { ?>
                    <tr>
                        <td><a class="<?= he( $a['type'] ) ?>" href="assoc.php?assoc_type=<?= urlencode( $a['type'] ) ?>"><?= $a['type'] ?></a></td>
                        <td><?= linkify( '[[' . undirify( $a['path'] ) . '|' . undirify( $a['path'] ) .  ']]' ) ?></td>
                        <td><?= $a['sequence'] ?></td>
                        <td>[<a href="assoc.php?file=<?= urlencode( undirify( $a['path'] ) ) ?>">view associations</a>]</td>
                    </tr>
                <? } ?>
                    <tr>
                        <th colspan="3">
                            <h3>Sources (from others to <?= undirify( basename( $p['file'] ) ) ?>)</h3>
                        </th>
                    </tr>
                    <tr>
                        <th>Association Type</th>
                        <th>Path</th>
                        <th>&nbsp;</th>
                    </tr>
                <? foreach( $p['sources'] as &$a ) { ?>
                    <tr>
                        <td><a class="<?= he( $a['type'] ) ?>" href="assoc.php?assoc_type=<?= urlencode( $a['type'] ) ?>"><?= $a['type'] ?></a></td>
                        <td><?= linkify( '[[' . undirify( $a['path'] ) . '|' . undirify( $a['path'] ) .  ']]' ) ?></td>
                        <td>[<a href="assoc.php?file=<?= urlencode( undirify( $a['path'] ) ) ?>">view associations</a>]</td>
                    </tr>
                <? } ?>
                </table>
            </div>
        </div>
    </div>
</div>
