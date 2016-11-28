<?php renderable( $p ); ?>
<?php 
    
?>
<div class="view-notes-list">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">Notes on Commits</span>
        </div>
        <div class="panel-body">
            Notes that have been made against various commits.
        </div>
        <table class="table table-hover table-striped table-condensed">
            <thead>
                <tr>
                    <th>Commit</th>
                    <th style="white-space: nowrap;">Commit Date</th>
                    <th style="white-space: nowrap;">Notes</th>
                </tr>
            </thead>
            <tbody>
            <? if( count( $p['notes'] ) <= 0 ) { ?>
                <tr>
                    <td colspan="2">
                        No commits with notes.
                    </td>
                </tr>
            <? } else { ?>
                <? foreach( array_reverse( $p['notes'] ) as $k => &$notes  ) { ?>
                    <tr>
                        <td>
                            <a 
                                href="show_commit.php?commit=<?= urlencode( $notes['commit']['commit'] ) ?>"
                            >
                            <?= commit_excerpt( $notes['commit']['commit'] ) ?>
                            </a>
                        </td>
                        <td>
                            <?= short_time_diff( $notes['commit']['author_date_epoch'], time() ) ?>
                        </td>
                        <td>
                            <?= he( $notes['commit']['author_name'] ) ?>
                            <pre><code><?= he( $notes['commit']['body'] ) ?></code></pre>
                            <?php $i = 0; ?>
                            <?php foreach( preg_split( '/^####(.+)$/m', $notes['notes'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $c ) { ?>
                                <?php if( $i % 2 == 0 ) { ?>
                                    <?= he( $c ) ?>
                                <?php } else { ?>
                                    <pre><?= clickable_urls( trim( $c ) ) ?></pre>
                                <?php }
                                    $i++;
                                ?>
                            <?php } ?>
                        </td>
                    </tr>
                <? } ?>
            <? } ?>
            </tbody>
        </table>
    </div>
</div>
