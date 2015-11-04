<? renderable( $p ); ?>

<div class="container-fluid">
    <div class="move-file">
        <? if( !$p['finished'] ) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="panel-title">
                            <h2><?= $p['message'] ?></h2>
                        </span>
                    </div>
                    <div class="panel-body">
                        <div class="move">
                            <form action="move.php" method="get" >
                                <table>
                                    <tr>
                                        <th>Original File Location:</th>
                                        <td>
                                            <? $original_dir = dirname( undirify( $p['file'], true ) ); ?>
                                            <input type="hidden" id="original_dir"              value="<?= ( $original_dir == "." ? "" : $original_dir ) ?>">
                                            <input type="hidden" id="original_file" name="file" value="<?= $p['file'] ?>">
                                            <span><?= ( linkify( '[[' . undirify( $p['file'] ) . ']]' ) ) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Choose a different existing directory:</th>
                                        <td>
                                            <select id="new_dir_select">
                                                <? $current_dir = dirname( $p['file'] ); ?>
                                                <option value=""><?= DEFAULT_FILE . "/" ?> (root)</option>
                                                <? foreach( $p['all_directories'] as $i => &$dir ) { ?>
                                                    <option value="<?= undirify( $dir['file'], true ) ?>" <?= ( $current_dir == $dir['file'] ? "selected" : "" ) ?>>
                                                        <?= str_repeat( "_", substr_count( $dir['file'], "/" ) ) . basename( undirify( $dir['file'], true ) ) . '/' ?>
                                                    </option>
                                                <? } ?>
                                            </select>
                                            [ <a href="javascript:move.reset()">reset to original</a> ]
                                        </td>
                                    </tr>
                                        <th>Or enter a new directory:</th>
                                        <td>
                                            <input type="text" id="new_dir" name="new_dir" value="<?= undirify( $p['new_dir'], true ) ?>" size="<?= ( strlen( undirify( $p['new_dir'], true ) ) + 5 ) ?>" style="width: 100%">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Or just choose a different name:</th>
                                        <td><input type="text" id="new_file" name="new_file" value="<?= $p['new_file'] ?>" style="width: 100%"></td>
                                    </tr>
                                    <tr>
                                        <th>New file location (after move):</th>
                                        <td><input type="text" id="new_path" value="<?= ltrim( implode( '/', array( undirify( $p['new_dir'], true ), $p['new_file'] ) ), '/' ) ?>" style="width: 100%" readonly></td>
                                    </tr>
                                    <? if( $p['counterpart_exists'] ) { ?>
                                    <tr>
                                        <th>Also move...</th>
                                        <td>
                                            <div>
                                                A <?= ( is_dirifile( $p['counterpart'] ) ? "directory" : "file" ) ?> appears to exist alongside the source file (<?= basename( $p['counterpart'] ) ?>). Move this too? (Otherwise, these files will potentially be without their counterpart)
                                            </div>
                                            <label for="yes">
                                                Yes
                                            </label>
                                            <input type="radio" name="move_counterpart" id="yes"  value="yes" checked>
                                            <label for="no">
                                                No
                                            </label>
                                            <input type="radio" name="move_counterpart" id="no" value="no" >
                                        </td>
                                    </tr>
                                    <? } ?>
                                    <? if( ALIAS_ENABLE ) { ?>
                                    <tr>
                                        <th>Leave Aliases...</th>
                                        <td>
                                            <div>
                                                If files are moving, this means that references to these files might broken. 
                                                You can optionally create aliases for the files that are moved. The newly
                                                created aliases will refer all pages referencing the old file locations to the
                                                new locations resulting from this move.
                                            </div>
                                            <label for="yes">
                                                Yes
                                            </label>
                                            <input type="radio" name="leave_alias" id="yes"  value="yes">
                                            <label for="no">
                                                No
                                            </label>
                                            <input type="radio" name="leave_alias" id="no" value="no" checked>
                                        </td>
                                    </tr>
                                    <? } ?>
                                    <tr>
                                        <td colspan="2">
                                            <input type="submit" name="submit" value="Move!">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
        <script type="text/javascript">
    
            var move = {
                reset: function() {
                    $('#new_dir_select').val(
                        $('#original_dir').val()                    
                    ).trigger( 'change' );
                }
            };
    
            $(document).ready( 
                function() {
    
                    var update_new_path = function( ) {
                        $('#new_path').val( 
                            [ 
                                $('#new_dir').val().replace( /\/+$/, '' ),
                                $('#new_file').val() 
                            ].join( '/' ).replace( /^\/+/, '' )
                        );
                    };
    
                    $('#new_dir_select').change(
                        function() {
                            $('#new_dir').val(
                                $(this).val()
                            );
    
                            update_new_path();
    
                        }
                    );
    
                    $('#new_file').keyup( update_new_path );
    
                    $('#new_dir').keyup( update_new_path );
    
                }
            );
        </script>
        <? } ?>
    </div>
