<? renderable( $p ); ?>
<? $stash['css'][] = 'assoc.css'; ?>
<? $stash['css'][] = 'table.css'; ?>
<? sort( $p['orphans'] ); ?>
<div class="assoc container-fluid display">
    <div class="panel panel-default">
        <div class="panel-heading">
            Orphans (Pages targeted by no others)
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="tabulizer">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Orphan Node</th>
                        </tr>
                    </thead>
                    <tbody>
                <? foreach( $p['orphans'] as &$a ) { ?>
                    <tr>
                        <td><?= linkify( '[[' . undirify( $a['path'] ) . ']]' ) ?></td>
                        <td>
                            <a 
                                href="index.php?file=<?= implode( "/", array( rtrim( ASSOC_DIR, "/" ), assoc_file_normalize( $a['path'] ) ) ) ?>"
                            >
                                Node
                            </a>
                        </td>

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
