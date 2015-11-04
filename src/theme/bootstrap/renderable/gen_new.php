<? renderable( $p ); ?>
<?

# usort ( $p['drafts'], "epoch_sort" );

$stash['css'][] = 'new.css';

$file = file_or( $p['file'], false );
$template = file_or( $p['template'], false );

if( $file !== false ) { $file = undirify( $file ); }
if( $template !== false ) { $template = undirify( $template ); }

?>

<div class="new container-fluid">
    <div class="new display">
        <div class="panel panel-default">
            <div class="panel-heading">
                Create a new document
            </div>
            <div class="panel-body">
                <form 
                    action="<?= $template === false ? 'edit.php' : 'template.php' ?>"
                    method="get"
                    class="form-group new"
                >
                        <?php if( $template !== false ) { ?>

                            <div class="input-group">
                                <label>
                                    Selected Template:
                                    <input 
                                        class="form-control" 
                                        type="text" 
                                        name="template" 
                                        value="<?= he( $template ) ?>"
                                        readonly="readonly"
                                    />
                                </label>
                            </div>
                        <? } ?>
                        <label>
                            New File Path:
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    name="file" 
                                    class="form-control" 
                                    id="new_file_path"
                                    <?= ( $file === false ? '' : 'data-original="' . he( $file ) . '"' ) ?>
                                    <?= ( $file === false ? '' : 'value="' . he( $file ) . '"' ) ?>
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

                        </label>
                    <span 
                        class="help-block"
                    >
                        Type the name of the file you would like to create. 
                        If you would like to create a file under a directory, separate 
                        the directory folders with a slash ("/"). If the directory
                        already exists, an autocomplete suggestion will be shown. Click
                        or use the arrows to select an existing directory.
                    </span>

                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var nuevo = {
        directories: <?= json_encode( $p['directories'] ) ?>,
        setup: function() {
            $('#new_file_path').autocomplete( { source: nuevo.directories } );

            $( 'form.new' ).submit(
                function( evt ) {

                    var template_file = $(this).find( '#new_file_path' );

                    if( template_file ) {
                        if( $( template_file ).val() == template_file.data( 'original' ) ) {

                            evt.stopPropagation();
                            alert( 'Please enter a new name for the file you wish to be created.' );
                            return false;
                        }
                    }
                }
            );
        }
    };

    $(document).ready( nuevo.setup );

</script>
