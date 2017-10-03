<? 
renderable( $p );
$stash['css'][]  = 'diff.css';
$stash['css'][]  = 'flags.css';
# $stash['core_js'][]   = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['core_js'][]   = 'jquery.scrollTo-2.1.2-min.js';
$stash['js'][]   = 'diff.js';
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
        </div>
        <div class="collapse navbar-collapse" id="diff-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <a class="first" href="#" title="Scroll to the next difference in the file"><kbd>N</kbd>ext difference</a>
                </li>
                <li>
                    <a class="previous" href="#" title="Scroll to the previous difference in the file"><kbd>P</kbd>revious difference</a>
                </li>
                <li id="removes-list-item">

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
                        <a href="file_diff.php?file_a=<?= urlencode( $p['file_a'] ) ?>&file_b=<?= urlencode( $p['file_b'] ) ?>&plain=yes">Unformatted Diff</a>
                    </li>
                <? } else { ?>
                    <li>
                        <a href="file_diff.php?file_a=<?= urlencode( $p['file_a'] ) ?>&file_b=<?= urlencode( $p['file_b'] ) ?>&plain=no">Formatted Diff</a>
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
                Diff between 
                <?= linkify( '[[' . $p['file_a'] . ']]', array( 'minify' => true ) ) ?>
                and
                <?= linkify( '[[' . $p['file_b'] . ']]', array( 'minify' => true ) ) ?>
            </span>
        </div>
        <div class="panel-body">
            <div 
                id="diff" 
                class="diff display adds removes <?= ( $p['plain'] ? "plain" : $p['extension'] ) ?>"
                <? /*
                data-file="<?= $p['file_a'] ?>"
                data-commit="<?= $p['commit_after'] ?>"
                */ ?>
            >
                <div>
                    <?= $p['diff'] ?>
                </div>
            </div>
        </div>
    </div>
</div>

