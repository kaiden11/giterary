<? renderable( $p ) ?>
<? $stash['css'][] = 'simpler.v2.show.css'; ?>

<div class="commit container-fluid">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>Commit: <?= $p['show']['commit'] ?></span>
            </div>
            <div class="panel-body">
                <div class="commit display">
                    <table>
                        <tr><td>Commit:</td>
                        <td>
                            <a href="view.php?commit=<?= $p['show']['commit'] ?>"><?= $p['show']['commit'] ?></a>
                        </td>
                        </tr>
                        <tr>
                            <td>Parent Commit:</td>
                            <td>
                                <? if( $p['show']['parent_commit'] ) { ?>
                                <a href="show_commit.php?commit=<?= $p['show']['parent_commit'] ?>"><?= $p['show']['parent_commit'] ?></a>
                                [<a href="diff.php?commit_before=<?= $p['show']['parent_commit'] ?>&commit_after=<?= $p['show']['commit'] ?>">formatted diff</a>]
                                [<a href="diff.php?commit_before=<?= $p['show']['parent_commit'] ?>&commit_after=<?= $p['show']['commit'] ?>&plain=yes">plain diff</a>]
                                <? } else { ?>
                                    (no parent commit)
                                <? } ?>
                            </td>
                        </tr>
                        <tr><td>Author Name:</td><td><?= $p['show']['author_name'] ?></td></tr>
                        <tr><td>Author Email:</td><td><?= $p['show']['author_email'] ?></td></tr>
                        <tr><td>Author Date:</td><td><?= $p['show']['author_date'] ?></td></tr>
                        <tr><td>Subject:</td><td><?= $p['show']['subject'] ?></td></tr>
                        <tr><td colspan="2">Files</td></tr>
                        <tr><td colspan="2">
                            <ul>
                        <? foreach( $p['show']['file_list'] as &$file ) { ?>
                                <li><a href="view.php?commit=<?= $p['show']['commit'] ?>&file=<?= undirify( $file ) ?>"><?= undirify( $file ) ?></a></li>
                        <? } ?>
                        </td></tr>
                    </table>
                    <? if( is_logged_in() ) { ?>
                        <div>
                            [<?= funcify( "[[revert:commit=" . $p['show']['commit'] . "|Revert this commit]]" ) ?>]
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>Commit Contents</span>
            </div>
            <div class="panel-body">
                <pre
                    class="pre-scrollable"
                ><?= clickable_urls( $p['show']['body'] ) ?></pre>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>Notes:</span>
            </div>
            <div class="panel-body">
                <? $note_ref_display = array(
                    WORD_COUNT_NOTES_REF    =>  "Word Count",
                    WORKING_TIME_NOTES_REF  =>  "Working Time",
                    COMMIT_RESPONSE_REF     =>  "Responses"
                ); ?>
                <? foreach( $p['notes'] as $note_ref => &$note ) { ?>
                    <? if( $note == "" ) { continue; } ?>
                    <div>
                        <div>
                            <?= ( $note_ref_display[$note_ref] ? $note_ref_display[ $note_ref ] : $note_ref ) ?>
                        </div>
                        <div>
                            <pre 
                                class="pre-scrollable"
                            ><?= clickable_urls( $note ) ?></pre>
                        </div>
                    </div>
                <? } ?>
                <? if( is_logged_in() ){ ?>
                <div>
                    <form action="commit_respond.php" method="post">
                        <input type="hidden" name="commit" value="<?= $p['show']['commit'] ?>" />
                        <div>
                            <h4>Provide a response to this commit</h4>
                        </div>
                        <div>
                            <textarea 
                                id="response" 
                                name="response"
                                class="form-control"
                            ></textarea>
                        </div>
                        <div>
                            <input type="submit" value="Submit" />
                        </div>
                    </form>
                </div>
                <? } ?>
                </div>
            </div>
        </div>
</div>
