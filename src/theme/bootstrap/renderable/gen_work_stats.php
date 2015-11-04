<? renderable( $p ); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <span class="panel-title">
            Work Stats for <?= linkify( '[[' . undirify( $p['file'] ) . '|' . basename( $p['file'] ) . ']]' ) ?>
        </span>
    </div>
    <div class="panel-body">
        Work stats for users for this file
    </div>
    <table class="table table-hover table-striped tabulizer">
        <thead>
            <tr>
                <th>
                    Author
                </th>
                <th>
                    Time Spent
                </th>
                <th>
                    Total Commits
                </th>
                <th>
                    Commits with Stats
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach( $p['work_stats'] as $author => &$stats ) { ?>
                <tr>
                    <td>
                        <?= he( $author ) ?>
                    </td>
                    <td>
                        <span title="<?= he( plural( $stats['seconds'], "second" ) ) ?> ">
                            <?= he( 
                                plural( 
                                    sprintf( "%.1f", $stats[ 'seconds' ] / 3600 ),
                                    "hour"
                                ) 
                            ) ?>

                        </span>
                    </td>
                    <td>
                        <?= he( $stats[ 'commits' ] ) ?>
                    </td>
                    <td>
                        <?= he( $stats[ 'commits_with_stats' ] ) ?>
                    </td>
                </tr>
            <? } ?>
        </tbody>
    </table>
</div>
