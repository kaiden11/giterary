<? 
renderable( $p );
$stash['css'][]  = 'diff.css';
$stash['css'][]  = 'flags.css';
# $stash['core_js'][]   = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['core_js'][]   = 'jquery.scrollTo-2.1.2-min.js';
$stash['js'][]   = 'diff.js';

# $show_before = null;
$show_after = null;

# if( commit_or( $p['commit_before'], false ) !== false ) {
#     $show_before = git_show( $p['commit_before'] );
# }

if( commit_or( $p['commit_after'], false ) !== false ) {
    $show_after = git_show( $p['commit_after'] );
}

?>
<nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#diff-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a 
                class="first btn btn-default navbar-btn" 
                href="#" 
                title="Scroll to the next difference in the file"
            >
                <kbd>N</kbd>ext difference
            </a>
            <a 
                class="previous btn btn-default navbar-btn" 
                href="#" 
                title="Scroll to the previous difference in the file"
            >
                <kbd>P</kbd>revious difference
            </a>
        </div>
        <div class="collapse navbar-collapse" id="diff-collapse">
            <ul class="nav navbar-nav">
                <li id="removes-list-item">
                    <div 
                        class="btn-group"
                        data-toggle="buttons"
                    >
                        <label class="removes-checkbox btn btn-default navbar-btn <?= ( $p['subtractions'] ? 'active' : '' ) ?>">
                            <input 
                                type="checkbox" 
                                class="checkbox-enable-removes" 
                                id="checkbox-enable-removes" 
                                <?= ( $p['subtractions'] ? 'checked="checked"' : '' ) ?>
                            />
                            <kbd>S</kbd>ubtractions
                        </label>
                    </div>
                </li>
                <li id="adds-list-item">
                    <div
                        class="btn-group"
                        data-toggle="buttons"
                    >
                        <label class="adds-checkbox btn btn-default navbar-btn <?= ( $p['additions'] ? 'active' : '' ) ?>">
                            <input 
                                type="checkbox" 
                                class="checkbox-enable-adds" 
                                id="checkbox-enable-adds" 
                                <?= ( $p['additions'] ? 'checked="checked"' : '' ) ?>
                            />
                            <kbd>A</kbd>dditions
                        </label>
                    </div>
                </li>
                <li class="diff-count">
                    <span class="navbar-text" id="diff-count" >-</span>
                </li>

                <? if( !$p['plain'] ) { ?>
                    <li>
                        <a href="diff.php?file=<?= urlencode( $p['file'] ) ?>&commit_before=<?= $p['commit_before'] ?>&commit_after=<?= $p['commit_after'] ?>&plain=yes">Unformatted Diff</a>
                    </li>
                <? } else { ?>
                    <li>
                        <a href="diff.php?file=<?= urlencode( $p['file'] ) ?>&commit_before=<?= $p['commit_before'] ?>&commit_after=<?= $p['commit_after'] ?>&plain=no">Formatted Diff</a>
                    </li>

                <? } ?>

                <? if( is_logged_in() ) { ?>
                    <li>
                        <a href="cherrypick.php?file=<?= urlencode( $p['file'] ) ?>&commit_before=<?= $p['commit_before'] ?>&commit_after=<?= $p['commit_after'] ?>" title="Cherrypick these changes">Cherrypick</a>
                    </li>
                    <li>
                        <a 
                            class="edit-latest"
                            href="edit.php?file=<?= urlencode( $p['file'] ) ?>" 
                            title="Edit the latest version of this file"
                        >
                            <kbd>E</kbd>dit Latest Version
                        </a>
                    </li>
                <? } ?>
            </ul>
        </div>
    </div>
</nav>
<div class="diff container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">
                Diff for <?= linkify( '[[' . $p['file'] . ']]', array( 'minify' => true ) ) ?>
                <span class="commit-parameters">
                    (Commits: <a 
                        title="Commit Before"
                        class="wikilink"
                        href="show_commit.php?commit=<?= $p['commit_before'] ?>"
                    ><?= commit_excerpt( $p['commit_before'] ) ?></a>,
                    <a 
                        title="Commit After"
                        class="wikilink"
                        href="show_commit.php?commit=<?= $p['commit_after'] ?>"
                    ><?= commit_excerpt( $p['commit_after'] ) ?></a>,
                    <?= ( !is_null( $show_after ) ? '<code>' . he( excerpt( $show_after[ 'subject' ], 75 ) ) . '</code/>' : '' ) ?>
                    )
                </span>
            </span>
        </div>
        <div class="panel-body">
            <div 
                id="diff" 
                class="diff display adds removes <?= ( $p['plain'] ? "plain" : $p['extension'] ) ?>"
                data-file="<?= $p['file'] ?>"
                data-commit="<?= $p['commit_after'] ?>"
            >
                <div>
                    <?= $p['diff'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

