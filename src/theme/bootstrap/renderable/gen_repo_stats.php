<? 
renderable( $p );
# $stash['css'][] = 'simpler.v2.sequence.css';
# $stash['css'][] = 'simpler.v2.stats.css';
# $stash['js'][] = 'stats.js';

function percent( $amount, $total ) {
    return sprintf( "%0.1f%%", ( ($total == 0 ? 0 : ( 100*($amount / $total) ) ) ) );
}

?>
<div class="stats">
    <nav class="navbar navbar-default navbar-fixed-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav nav">
                <li><a href="index.php?file=<?=   DEFAULT_FILE ?>"><?= DEFAULT_FILE ?></a></li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="panel-title">Repository Stats</span>
            </div>
            <div class="panel-body">
                <div class="stats display">
                    <div>Number of commits: <?= to_si( $p['rev_count'] ) ?></div>
                    <div>Number of files: <?= to_si( $p['file_count'] ) ?></div>
                    <div>Number of normal files: <?= to_si( $p['normal_count'] ) ?></div>
                    <div>Number of association files: <?= to_si( $p['assoc_count'] ) ?></div>
                    <div>Number of alias files: <?= to_si( $p['alias_count'] ) ?></div>
                    <div>First Commit: <?= $p['first_commit' ]['author_date'] ?> (<?= short_time_diff( $p['first_commit' ]['author_date_epoch'], time() ) ?>)</div>
                    <div>Last Commit: <?= $p['last_commit' ]['author_date'] ?> (<?= short_time_diff( $p['last_commit' ]['author_date_epoch'], time() ) ?>)</div>
                </div>
            </div>
        </div>
    </div>
</div>
