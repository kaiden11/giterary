<? 
renderable( $p );
?>
<div class="diff container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">
                Select file to diff for <?= commit_excerpt( $p['commit_before'] ) ?> ... <?= commit_excerpt( $p['commit_after'] ) ?>
            </span>
        </div>
        <div class="panel-body">
            <p>
                This diff contains more than one file. Click on the hyperlink below to show
                the diff for the file you are interested in (or Ctrl-click to open in a 
                new tab)
            </p>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Plain Diff</th>
                    <th>Formatted Diff</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $p['files'] as $f ) { ?>
                    <?php $uf = undirify( $f ); $df = dirify( $f ); ?>
                    <tr>
                        <td>
                            <?= funcify( '[[diff:file=' . $df . ',commit_before=' . $p['commit_before'] . ',commit_after=' . $p['commit_after'] . ',plain=yes|' . $uf . ']]'  ) ?>
                        </td>
                        <td>
                            <?= funcify( '[[diff:file=' . $df . ',commit_before=' . $p['commit_before'] . ',commit_after=' . $p['commit_after'] . ',plain=yes|Plain]]'  ) ?>
                        </td>
                        <td>
                            <?= funcify( '[[diff:file=' . $df . ',commit_before=' . $p['commit_before'] . ',commit_after=' . $p['commit_after'] . ',plain=no|Formatted]]'  ) ?>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>


