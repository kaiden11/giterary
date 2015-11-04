<? renderable( $p ); ?>
<? $stash['css'][] = 'assoc.css'; ?>
<? $stash['css'][] = 'table.css'; ?>
<? 
ksort( 
    $p['all_assoc_types']
); 
?>
<div class="assoc container-fluid display">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= plural( count( array_keys( $p['all_assoc_types'] ) ), "Unique Association Type", "s" ) ?>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="tabulizer">
                    <thead>
                        <tr>
                            <th>Association Type</th>
                            <th>Number of Associations</th>
                        </tr>
                    </thead>
                <? $i = 0; ?>
                    <tbody>
                <? foreach( $p['all_assoc_types'] as $type => $count ) { ?>
                    <tr>
                        <td><a href="assoc.php?assoc_type=<?= urlencode( $type ) ?>"><?= $type ?></a></td>
                        <td><?= plural( $count, "association", "s" ) ?></td>
                    </tr>
                    <? $i += $count; ?>
                    <? } ?>
                    </tbody>
                </table>
                <div class="total">
                    <span><?= plural( $i, "total association", "s" ) ?></span>
                </div>
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
