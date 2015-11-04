<? renderable( $p ); ?>
<? $stash['css'][] = 'assoc.css'; ?>
<? $stash['css'][] = 'table.css'; ?>
<div class="assoc container-fluid display">
    <div class="panel panel-default">
        <div class="panel-heading">
            Wanted (Pages referenced, but do not exist)
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="tabulizer">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Wanted Target</th>
                        </tr>
                    </thead>
                    <tbody>
                <? foreach( $p['wanted'] as &$a ) { ?>
                    <tr>
                        <td><a href="assoc.php?assoc_type=<?= urlencode( undirify( $a['type'], true ) ) ?>"><?= undirify( $a['type'], true ) ?></a></td>
                        <td><?= linkify( '[[' . undirify( $a['source'] ) . ']]' ) ?></td>
                        <td><?= linkify( '[[' . undirify( $a['target'] ) . ']]' ) ?></td>
                    </tr>
                <? } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            $('.tabulizer').tabulizer();
        }
    );
</script>
