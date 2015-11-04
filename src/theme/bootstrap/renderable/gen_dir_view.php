<? 
renderable( $p )
# $stash['css'][]  = 'simpler.v2.sequence.css';
# $stash['css'][]  = 'simpler.v2.dir_view.css';
# $stash['js'][]   = 'dir_view.js'; 
?>
<div class="dir-view container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">Directory: <?= linkify( '[[' . $p['file'] . ']]' ) ?></span>
        </div>
        <div class="panel-body">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <ul class="nav navbar-nav">
                        <li>
                            <a
                                href="index.php?file=<?= ( strpos( $p['file'], '/' ) !== false ? dirname( $p['file'] ) : dirify( DEFAULT_FILE, true ) ) ?>"
                            >
                                Up
                            </a>
                        </li>

                        <? if( is_logged_in() ) { ?>
                            <? $f = undirify( $f ); ?>
                            <li>
                                <a
                                    href="move.php?file=<?= $p['file'] ?>"
                                >
                                    Move
                                </a>
                            </li>
                            <li>
                                <a 
                                    href="delete.php?file=<?= $p['file']  ?>"
                                >
                                    Delete
                                </a>
                            </li>
                        <? } ?>
                        <li>
                            <a
                                href="history.php?file=<?= $p['file'] ?>"
                            >
                                Revision History
                            </a>
                        </li>
                        <li>
                            <?= linkify( '[[' . undirify( $p['file'], true ) . '|File Counterpart]]' ) ?>
                        </li>
                        <li>
                           <a href="todos.php?file=<?= urlencode( undirify( $p['file'] ) ) ?>">TODOs</a>
                        </li>
                        <li>
                            <a href="annotations.php?file=<?= urlencode( undirify( $p['file'] ) ) ?>">Annotations</a>
                        <li>
                    </ul>
                    <form class="navbar-form" action="tags.php" method="get">
                        <div class="form-group">
                            <input type="hidden" name="file" value="<?= undirify( $p['file'] ) ?>">
                            <input class="form-control" type="text" name="tag" id="tag-search" value="">
                            <button class="btn btn-default" type="submit">
                                Search for a tag
                            </button>
                        </div>
                    </form>
                </div>
            </nav>
            <div class="dir-view display">
            
                <div class="view">
                    <div class="ls_tree">
                        Directory Contents:
                        <table class="table table-hover table-striped table-condensed">
                            <tr>
                                <th>Type</th>
                                <th>File</th>
                                <th class="commit">Commit Age</th>
                                <th class="commit">Commit Notes</th>
                                <th class="commit">Commit Author</th>
                            </tr>
                        <?
                            usort( 
                                $p['ls_tree'],
                                function( $a, $b ) {
                                    return (
                                        $a['type'] == $b['type'] 
                                        ?
                                            ( 
                                                strcmp( $a['file'], $b['file'] )
                                            )
                                        :
                                            (
                                                // Tree (directory) types go first
                                                $a['type'] == 'tree' ? -1 : 1 
                                            )
                                    );
                                }
                            );
                        ?>
                        <? foreach( $p['ls_tree'] as $file ) { ?>
                            <tr>
                                <td><?= $file['type'] ?></td>
                                <td>
                                    <?= linkify( 
                                        '[[' .  undirify( $file['file'] ) . ']]',
                                        array( 
                                            'minify'    =>  true
                                        )
                                    ) ?>
                                </td>
                                <td class="commit"><span title="<?= $file['head_commit']['author_date'] ?>"><?= medium_time_diff( $file['head_commit']['author_date_epoch'] ) ?></span></td>
                                <td class="commit message">
                                    <div>
                                        <a href="show_commit.php?commit=<?= $file['head_commit']['commit'] ?>" title="Show details on this commit." >

                                        </a>
                                        <pre class="pre-scrollable"><?= $file['head_commit']['subject'] ?></pre>
                                    </div>
                                </td>
                                <td class="commit">
                                    <div>
                                        <span>
                                            <a href="history.php?author=<?= urlencode( $file['head_commit']['author_name'] ) ?>"><?= $file['head_commit']['author_name'] ?></a>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <? } ?>
                            <tr>
                                <td>&nbsp;</td>
                                <form action="index.php" method="get">
                                <td>
                                    <input name="file" type="text" value="<?= undirify( $p['file'], true ) . "/NewFile" ?>">
                                </td>
                                <td colspan="4">
                                    <input type="submit" value="Start editing a new file">
                                </td>
                                </form>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <form action="import.php" method="get">
                                <td>
                                    <input name="file" type="text" value="<?= undirify( $p['file'], true ) . "/NewFile" ?>">
                                </td>
                                <td colspan="4">
                                    <input type="submit" value="Convert/import a new file">
                                </td>
                                </form>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>
                                <td colspan="4">
                                    <form enctype="multipart/form-data" action="upload.php" method="POST">
                                        <input type="hidden" name="directory" value="<?= undirify( $p['file'], true ) ?>" />
                                        <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
                                        <input name="file_upload" type="file" />
                                        <input type="submit" value="Upload File" />
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

