<? renderable( $p ); ?>
<? 
# $stash['css'][] = 'simpler.v2.online.css'; 

uasort(
    $p['statuses'],
    function( $a, $b ) {
        return $b['time'] - $a['time'];
    }
);

?>
<div class="view-online-list">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="panel-title">Users Online</span>
        </div>
        <div class="panel-body">
            Users and their latest activity
        </div>
        <table class="table table-hover table-striped table-condensed">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
            <? if( count( $p['statuses'] ) <= 0 ) { ?>
                <tr>
                    <td colspan="2">
                        Nobody online? Huh... that's odd.
                    </td>
                </tr>
                <? } else { ?>
                    <? foreach( $p['statuses'] as $user=>$status ) { ?>
                    <tr>
                        <td>
                            <a href="index.php?file=<?= urlencode( $user ) ?>"><?= $user ?></a>
                        </td>
                        <td>
                            <?= $status['page_title'] ?>
                        </td>
                        <td>
                            <?= html_short_time_diff( $status['time'], time() ) ?>
                        </td>
                    </tr>
                    <? } ?>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>
