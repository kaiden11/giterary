<?php
renderable( $p );

$stash['css'][] = 'pandoc.css';

?>
<div class="epub">
    <ul>
        <li>
            <h4>
                <span class="glyphicon glyphicon-download-alt"></span> 
                Download:
                    <a href="raw.php?file=<?= he( $p['file'] ) ?>">
                        <em>Pandoc Output</em>
                    </a>,
                    <a href="raw.php?file=<?= he( $p['file'] ) ?>&versioned=yes&download=yes">
                        <em>Timestamped Pandoc Output</em>
                    </a>
            </h4>
        </li>

        <li>
            <h4>
                Output Format: <em><?= he( $p['pan']['format'] ) ?></em>
            </h4>
        </li>


        <li>
            <?= plural( count( $p['pan']['variables'] ), "variable", "s" ) ?> (in order):
            <ol>
                <? foreach( $p['pan']['variables'] as $variable => $value ) { ?>
                    <li>
                        <code>
                            <?= "$variable = $value" ?>
                        </code>
                    </li>
                <? } ?>
            </ol>
        </li>
        <li>
            <?= plural( count( $p['pan']['includes'] ), "included header", "s" ) ?> (in order):
            <ol>
                <? foreach( $p['pan']['includes'] as $hdr ) { ?>
                    <li>
                        <?= linkify( '[[' . $hdr . ']]' ) ?><?= ( git_file_exists( $hdr ) ? '' : ' (<span style="color: red">WARNING: does not exist</span>)' ) ?>
                    </li>
                <? } ?>
            </ol>
        </li>


        <li>
            <?= plural( count( $p['pan']['files'] ), "document", "s" ) ?> (in order):
            <ol>
                <? foreach( $p['pan']['files'] as $file ) { ?>
                    <li>
                        <h5>
                            <?= linkify( 
                                '[[' . $file['file'] 
                                . '|' 
                                . funcify( 
                                    $file['title'], 
                                    $p['file'] ) 
                                . ']]' 
                            ) ?>
                            <? if( git_file_exists( $file['file'] ) ) { ?>
                                <?

                                    $hc = git_file_head_commit( $file['file'] );
                                    if( commit_or( $hc, false ) === false ) {
                                        $show = array();
                                    } else {
                                        $show = git_show( $hc  );
                                    }
                                    $now = time();

                                    $contents =  git_file_get_contents( $file['file'], $hc );
                                ?>
                                    ( 
                                        <a 
                                            href="show_commit.php?commit=<?= $hc ?>"
                                            title="Last Edited @ <?= commit_excerpt( $hc ) ?>"
                                        />
                                            <?= short_time_diff( $now, $show['author_date_epoch'] ) ?>
                                        </a>,
                                        <?= to_si( strlen( $contents ) ) ?> characters
                                    )
                            <? } else { ?>
                                (<span style="color: red">WARNING: Does not exist</span>)
                            <? } ?>
                            <code>
                                <?= ( $file['params'] ? json_encode( $file['params'] ) : "(no parameters)" ) ?>
                            </code>
                        </h5>
                    </li>
                <? } ?>
            </ol>
        </li>
    </ul>

</div>

