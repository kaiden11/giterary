<?php
renderable( $p );

$stash['css'][] = 'epub.css';

?>
<div class="epub">
    <ul>
        <li>
            <h2>
                Title: <em><?= he( $p['epub']['title'] ) ?></em>
            </h2>
        </li>
        <li>
            <h4>
                Download:
                    <a href="raw.php?as=epub&file=<?= he( $p['file'] ) ?>">
                        <span class="glyphicon glyphicon-download-alt"></span> <em>EPUB</em>
                    </a>
            </h4>
        </li>

        <li>
            <h4>
                Authors: <em><?= he( join( ", ", $p['epub']['authors'] ) ) ?></em>
            </h4>
        </li>
        <li>
            <h4>
                Cover:
            </h4>
            <div>
                <?if( !$p['epub']['cover'] || !git_file_exists( $p['epub']['cover'] ) ) { ?>
                    <?= ( $p['epub']['cover'] ? 'Not Found' : 'None' ) ?>
                <? } else { ?>
                    <?= funcify( '[[image:file=' . $p['epub']['cover'] . '|COVER]]' ) ?>
                <? } ?>
            </div>
        </li>

        <li>
            <h4>
                CSS
            </h4>
            <? if( !isset( $p['epub']['css'] ) || count( $p['epub']['css'] ) <= 0  ) { ?>
                <ul>
                    <li>None</li>
                </ul>
            <? } else { ?>
                <ul>
                    <? foreach( $p['epub']['css'] as $c ) { ?>
                        <li><?= linkify( '[[' . $c . ']]' ) ?></li>
                    <? } ?>
                </ul>

            <? } ?>
        </li>
        <li>
            <?= plural( count( $p['epub']['files'] ), "document", "s" ) ?> (in order):
            <ol>
                <? foreach( $p['epub']['files'] as $file ) { ?>
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
                        </h5>
                    </li>
                <? } ?>
            </ol>
        </li>
    </ul>
    <?
    // Remove any files that do not exist
    for( $i = 0; $i < count( $p['epub']['files'] ); $i++ ) { 
        if( !git_file_exists( $p['epub']['files'][$i]['file'] ) ) {
            unset( $p['epub']['files'][$i] );
        }
    }
    ?>

    <ul>
        <li>
            META-INF/container.xml
            <pre><code><?= he( render( 'gen_epub_container_xml', $p['epub'] ) ) ?></code></pre>
        </li>
        <li>
            OEBPS/content.opf
            <pre><code><?= he( render( 'gen_epub_content_opf', $p['epub'] ) ) ?></code></pre>
        </li>
        <li>
            OEBPS/toc.ncx
            <pre><code><?= he( render( 'gen_epub_toc_ncx', $p['epub'] ) ) ?></code></pre>
        </li>
        <?if( $p['epub']['cover'] && git_file_exists( $p['epub']['cover']  ) ) { ?>
        <li>
            OEBPS/cover.xhtml
            <pre><code><?= he( render( 'gen_epub_cover', $p['epub'] ) ) ?></code></pre>
        </li>



        <? } ?>
        <li>
            OEBPS/toc.ncx
            <pre><code><?= he( render( 'gen_epub_toc_ncx', $p['epub'] ) ) ?></code></pre>
        </li>

        <? if( $p['epub']['files'] && count( $p['epub']['files'] ) ) { ?>
            <? $test = $p['epub']['files'][ array_rand( $p['epub']['files'] ) ]; ?>
            <li>
                Test Chapter
                <pre><code><?= 
                    he( 
                        _epub_display( 
                            $p['epub'], 
                            $test, 
                            git_file_get_contents( $test['file'] )
                        )
                    ) 
                ?></code></pre>
            </li>
        <? } ?>
        <li>
            Allowed Chapter Tags:
            <pre><code><?= he( $p['allowed_tags'] ) ?></code></pre>

        </li>

        <li>
            Removed Chapter Tags:
            <pre><code><?= he( $p['removed_tags'] ) ?></code></pre>

        </li>




    </ul>



</div>

