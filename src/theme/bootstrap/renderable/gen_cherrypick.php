<? 
renderable( $p );
$stash['css'][] = 'cherrypick.css';
# $stash['core_js'][]  = 'jquery.scrollTo-1.4.3.1-min.js';
$stash['core_js'][]   = 'jquery.scrollTo-2.1.2-min.js';
$stash['js'][]  = 'cherrypick.js';
?>
<nav class="navbar navbar-default navbar-fixed-bottom">
    <div class="container-fluid">
        <ul class="nav navbar-nav">
            <li>
                <button class="btn btn-default navbar-btn" onclick="cherrypick.clear();">Clear selections</button>
            </li>
            <li>
                <button class="btn btn-default navbar-btn" onclick="cherrypick.next_diff();">Next Difference</button>
            </li>
            <li>
                <button class="btn btn-default navbar-btn" onclick="cherrypick.toggle_subtractions();">Toggle All Subtractions</button>
            </li>
            <li>
                <button class="btn btn-default navbar-btn" onclick="cherrypick.toggle_additions();">Toggle All Additions</button>
            </li>
            <li>
                <button class="btn btn-primary navbar-btn" onclick="cherrypick.submit();">Put changes in editor</button>
            </li>
        </ul>
    </div>
</nav>

<div class="cherrypick container-fluid">
    <?if( $p['is_conflict'] ) { ?>
        <div class="row cherrypick display">
            <div class="col-md-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <span class="panel-title">PULL UP, PULL UP</span>
                    </div>
                    <div class="panel-body">
                        <p>
                            <strong>A conflict has been detected. Don't worry. This happens all the time.</strong>
                        </p>
                        <p>
                            Giterary has detected that the version you were editing against for this file is 
                            no longer the latest version in the repository. This isn't a problem, though. 
                            You have arrived at the "cherrypicking" interface, which, while normally used for 
                            picking and choosing pieces of a prior edit, is instead used here to pick and choose 
                            what to keep and what to leave behind in order to resolve your conflict and then
                            commit your current edit.
                        </p>
                        <p>
                            Click on the additions (blue) or subtractions (red) in the "Before" panel in order 
                            to arrive at a version you are happy with at right (the "After" panel). When done, 
                            click on "Put changes into editor" button and you will be put into the editor with 
                            the newly resolved contents to continue to work against. You will be able to edit 
                            further and commit from there.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <? } else { ?>
        <div class="row cherrypick display">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <span class="panel-title">Cherrypicking</span>
                    </div>
                    <div class="panel-body">
                        <p>
                            This is the "cherrypicking" screen. Here, you can review the differences between
                            different commits for a file, and select specific elements to keep or to remove.
                            You can click on the additions (blue) or subtractions (red) at left, and see the
                            "After" output change. Once you are happy with the output, you can select "Put
                            changes into editor" to put your new content into an editor so that you may either
                            edit further, or commit your newest changes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <? } ?>
    <div class="row cherrypick display">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">Before (Additions/Subtractions)</span>
                </div>
                <div class="panel-body">
                    <div id="cherrypick" class="cherrypick-diff">
                        <div>
                            <?= $p['diff'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">After (What will be put into the editor)</span>
                </div>
                <div class="panel-body">
                    <div id="cherrypick-preview" class="cherrypick-preview">
                </div>
            </div>
        </div>
    </div>
    <div style="display: none">
        <form id="edit" action="edit.php" method="post">
            <textarea id="cherrypick-output" name="edit_contents"></textarea>
            <input type="hidden" name="file" value="<?= $p['file'] ?>"/>
            <? /*<input type="hidden" name="submit" value="Preview"/> */ ?>
        </form>
    </div>
</div>

