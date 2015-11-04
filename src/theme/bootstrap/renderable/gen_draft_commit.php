<? renderable( $p ); 

$stash['css'][] = 'simpler.v2.drafts.css';

?>
<div class="drafts commit">
    <div class="drafts display">
        <? if( $p['confirm'] === true ) { ?>
            Drafts have been committed.
        <? } else { ?>
            <form action="draft_commit.php" method="post" >
                <? foreach( $p['drafts_to_process'] as $name => &$d ) { ?>
                    <input type="hidden" name="draft_to_commit[]" value="<?= he( $name ) ?>" />
                <? } ?> 
                <div>
                    Committing <?= plural( count( $p['drafts_to_process'] ), "draft" ) ?> against the following files:
                    <ul>
                        <? foreach( $p['drafts_to_process'] as $name => &$d ) { ?>
                            <li><?= linkify( '[[' . undirify( $d['filename'] ) . '|' . undirify( $d['filename'] ) . ']]' ) ?></li>
                        <? } ?>
                    </ul>
                </div>
                <div>
                    <label for="commit_notes">Combined notes for the above drafts:</label>
                    <textarea name="commit_notes" id="commit_notes"><?= he( $p['commit_notes'] ) ?></textarea>
                </div>
                <div class="undue-spacing">&nbsp;</div>
                <div class="confirmation">
                    <div>
                        <span>Are you sure?</span>
                    </div>
                    <div>
                        <table>
                            <tr>
                                <td>
                                    <input type="checkbox" id="confirm" name="confirm" value="yes" />
                                </td>
                                <td>
                                    <label for="confirm">Yes! Sounds great.</label>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <a href="drafts.php">No! Take me back to my drafts.</a>
                                </td>
                        </table>
                    </div>
                </div>
                <div class="undue-spacing">&nbsp;</div>
                <div>
                    <input type="submit" value="Commit!" />
                </div>
            </form>
        <? } ?>
    </div>
</div>
