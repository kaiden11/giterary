<? renderable( $p ); ?>
<? $stash['css'][] = 'assoc.css'; ?>
<div class="assoc container-fluid display">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= plural( count( $p['associations'] ), "Association", "s" ) ?> for type '<?= $p['assoc_type'] ?>'
        </div>
        <div class="panel-body">
            <div>
                <label for="all_assoc">Show all assocations of type: </label>
                <select id="all_assoc">
                    <? foreach( $p['all_assoc_types'] as $act ) { ?>
                        <option value="assoc.php?assoc_type=<?= urlencode( $act ) ?>" <?= ( $p['assoc_type'] == $act ? "selected" : "" ) ?>><?= $act ?></option>
                    <? } ?>
                </select>
            </div>
            <div class="table-responsive">
                <table>
                    <tr>
                        <th>Association Type</th>
                        <th>Source</th>
                        <th>Target</th>
                    </tr>
                <? foreach( $p['associations'] as &$a ) { ?>
                    <tr>
                        <td><span class="<?= he( $a['type'] ) ?>"><?= he( $a['type'] ) ?></span></td>
                        <td><?= linkify( '[[' . undirify( $a['source'] ) . ']]' ) ?> [<a href="assoc.php?file=<?= urlencode( undirify( $a['source'] ) ) ?>">view associations</a>]</td>
                        <td><?= linkify( '[[' . undirify( $a['target'] ) . ']]' ) ?> [<a href="assoc.php?file=<?= urlencode( undirify( $a['target'] ) ) ?>">view associations</a>]</td>
                    </tr>
                <? } ?>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            $('select#all_assoc').change( 
                function() {
                    if( $( this ).val() != null && $(this).val() != "" ) {
                        window.location = $( this ).val();
                    }
                }
            );
    
        }
    );
</script>
