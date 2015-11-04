<? renderable( $p ); ?>
<?
function epoch_sort( $a, $b ) {

    return ( $a['epoch'] < $b['epoch'] ? 1 : ( $a['epoch'] > $b['epoch'] ? -1 : 0 ) );

}

usort ( $p['drafts'], "epoch_sort" );

$stash['css'][] = 'drafts.css';

$found_committable_draft = false;

?>

<div class="drafts container-fluid">
    <div class="drafts display">
        <div class="panel panel-default">
            <div class="panel-heading">
                Your Drafts
            </div>
            <div class="panel-body">
                Drafts are automatically created when editing a file, and
                automatically deleted when committing a file. If you began
                editing a file, then stopped before committing, your draft,
                and its contents should appear here.
            </div>
            <form action="draft_commit.php" method="post" >
                <table class="table table-hover table-striped table-condensed"> 
                    <thead>
                        <tr>
                            <th>Against Commit</th>
                            <th>Age</th>
                            <th>File</th>
                            <th>Size (characters)</th>
                            <th>Working Time</th>
                            <th>Action</th>
                            <th>Select to commit (<a href="#" id="all">all</a>, <a href="#" id="none">none</a>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <? if( count( $p['drafts'] ) <= 0 ) { ?>
                        <tr>
                            <td colspan="7">
                                No drafts. This is fine: drafts are automatically
                                saved periodically when you are editing a file, and
                                are removed when you save your changes.
                            </td>
                        </tr>
                        <? } else { ?>
                            <? foreach( $p['drafts'] as $draft ) { ?>
                            <tr>
                                <td><?= ( commit_or( $draft['commit'], false ) ? '<a href="show_commit.php?commit=' . $draft['commit'] . '">' . commit_excerpt( $draft['commit'] ) . '</a>' : 'unknown' ) ?></td>
                                <td><?= short_time_diff( $draft['epoch'], time() ) ?></td>
                                <td><?= linkify( '[[' . $draft['filename'] . ']]' ) ?></td>
                                <td><?= to_si( $draft['length'] ) ?></td>
                                <td><?= short_time_diff( $draft['work_time'], 0 ) ?></td>
                                <td>
                                    <a title="Bring up draft in editor" href="edit.php?draft=<?= urlencode( basename( $draft['filepath'] ) ) ?>">Edit</a>
                                    |
                                    <a title="Bring up differences between this draft and the head" href="cherrypick.php?draft=<?= urlencode( basename( $draft['filepath'] ) ) ?>">Diff/Cherrypick</a>
                                    |

                                    <a title="Delete this drat" href="delete_draft.php?draft=<?= urlencode( basename( $draft['filepath'] ) ) ?>">Delete</a>
                                </td>
                                <? if( $draft['is_committable'] == true ) { ?>
                                    <? $found_committable_draft = true; ?>
                                    <td class="right">
                                        <input class="commit-draft" type="checkbox" name="draft_to_commit[]" value="<?= urlencode( basename( $draft['filepath'] ) ) ?>" />
                                    </td>
                                <? } else { ?>
                                    <td class="right">
                                        &nbsp;
                                    </td>
                                <? } ?>
                            </tr>
                            <? } ?>
                        <? } ?>
                    </tbody>
                    <tfoot>
                        <? if( $found_committable_draft === true ) { ?>

                        <tr>
                            <td colspan="7" class="right">
                                <label for="submit">Commit the selected drafts above</label>
                                <input id="submit" type="submit" value="Commit!" />
                            </td>
                        </tr>
                        <? } ?>
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    var drafts = {
        setup: function() {
            $('#all').click( function() {
                $('input.commit-draft').prop( 'checked', true );
            } );

            $('#none').click( function() {
                $('input.commit-draft').prop( 'checked', false );
            } );
        }
    };

    $(document).ready( drafts.setup );

</script>
