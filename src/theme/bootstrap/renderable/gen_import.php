<? 
renderable( $p );

# $stash['css'][] = 'simpler.v2.sequence.css';
# $stash['css'][] = 'simpler.v2.view.css';
# $stash['css'][] = 'simpler.v2.display.css';
$stash['css'][] = 'import.css';
# $stash['css'][] = 'giterary-codemirror.css';

$stash['js'][]  = 'to-markdown.js';
$stash['js'][]  = 'import.js';

$f = file_or( $p['file'], false );

?>
<div class="import">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Import</div>
                    </div>
                    <div class="panel-body">
                        <div class="import source">
                            <p>
                                Paste into the editable container below to convert your formatted
                                content into Markdown.
                            </p>
                            <div 
                                id="import-source" 
                                contentEditable="true"
                                class="form-control"
                            >
                                <em>This is example text.</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Target</div>
                    </div>
                    <div class="panel-body">
                        <div class="import target">
                            <form action="edit.php" method="post" class="form-group">
                                <div>   
                                    <label for="file">File:</label>
                                    <? if( $f === false ) { ?>
                                        <input class="form-control" type="text" id="file" name="file" value="<?= he( undirify( "Path/To/New/File" ) ) ?>" />
                                    <?  } else { ?>
                                        <input type="hidden" id="file" name="file" value="<?= he( undirify( $f ) ) ?>" />
                                        <?= linkify( '[[' . undirify( $f ) . ']]' ) ?>
                                    <? } ?>
                                </div>
                                <div>
                                    <input class="form-control btn btn-default" type="submit" value="Put converted content into editor" />
                                </div>
                                <div>
                                    <textarea 
                                        class="form-control" 
                                        id="import-target" 
                                        readonly="readonly" 
                                        name="edit_contents"
                                    ></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div?
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(
        function() {
            giterary_import.setup();
        }
    );
</script>
