<?php renderable( $p ); ?>
<div class="view-todo-hierarchy">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">Directories with TODOs</span>
        </div>
        <div class="panel-body">
            <p>
                Directories and the number of pending items (TODOs, CBs) within them.
                Click on any directory to see the list of pending items in the files underneath that directory.
            </p>
            <p>
                Alternatively, you can view the 
                <a href="todos.php">TODOs by file</a>.
            </p>
        </div>

        <table class="table table-hover table-striped table-condensed">
            <thead>
                <tr>
                    <th>Directory (pending item count)</th>
                </tr>
            </thead>
            <tbody>
            <? if( count( $p['rollup'] ) <= 0 ) { ?>
                <tr>
                    <td colspan="2">
                        No directories with TODOs
                    </td>
                </tr>
                <? } else { ?>
                    <? foreach( $p['rollup'] as $d => $count ) { ?>
                    <tr>
                        <td style="width: 100%">
                            <a 
                                style="margin-left: <?= 2*substr_count( $d, '/' ) ?>rem;"
                                href="todos.php?file=<?= urlencode( $d ) ?>"
                            ><?= basename( undirify( $d, true ) ) . '/' ?> (<?= plural( $count, "pending item" ) ?>)</a>
                        </td>
                    </tr>
                    <? } ?>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>
