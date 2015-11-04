<? 
renderable( $p );

$stash['css'][] = 'partition.css';
$stash['js'][]  = 'partition.js';
$new_collection_name = undirify( $p['file'] . '.collection' ); 

?>
<nav class="navbar navbar-default navbar-fixed-bottom">
    <div class="container-fluid">
        <ul class="nav navbar-nav">
            <li>
                <a href="javascript:void( partition.clear() )">Clear partitions</a>
            </li>
        </ul>
    </div>
</nav>
<div class="partition container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">Partitionining <?= linkify( '[[' . $p['file'] . ']]' ) ?></span>
                </div>
                <div class="panel-body">
                    <div class="partition display">
                        <div id="partition" class="partition source">
                            <?= $p['partitioning'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="partition-preview panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">Partition Results</span>
                </div>
                <div class="panel-body">
                    <div class="partition-preview-meta">
                        <label for="collection_name">New Collection Name</label>
                        <input id="collection_name" type="text" value="<?= $new_collection_name ?>" size="<?= strlen( $new_collection_name ) + 1 ?>" onkeyup="javascript:partition.update_collection_name( this )">
                        <input type="button" value="Submit" onclick="javascript:partition.submit()" />
                    </div>
                    <div id="partition-preview-output" class="partition-preview-output"></div>
                </div>
            </div>
        </div>
    </div>
    <div style="display:none">
        <form id="edit" action="make_partitions.php" method="post">
            <input id="json" type="hidden" name="json" value="" />
        </form>
    </div>
</div>
<script>
    $(document).ready( 
        function() {
            partition.setup( 
                ( <?= json_encode( $p['contents'] ) ?> ),
                ( <?= json_encode( $p['file'] ) ?> ),
                ( <?= json_encode( $new_collection_name ) ?> ) 
            );
        }
    );

</script>
