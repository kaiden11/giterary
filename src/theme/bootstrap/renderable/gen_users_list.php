<? renderable( $p ); ?>
<div class="view-users-list">
    <table> 
        <tr>
            <th>User</th>
            <th>Number of Commits</th>
            <th>Status</th>
        </tr>
        <? if( count( $p['users'] ) <= 0 ) { ?>
        <tr>
            <td colspan="2">
                No users? Huh... that's odd.
            </td>
        </tr>
        <? } else { ?>
            <? foreach( $p['users'] as $user=>$commits ) { ?>
            <tr>
                <td>
                    <a href="history.php?author=<?= urlencode( $user ) ?>"><?= $user ?></a>
                </td>
                <td>
                    <?= $commits ?>
                </td>
                <td>
                    <?= (
                        isset( $p['statuses'][$user] ) 
                            ?   $p['statuses'][$user]['page_title']
                            :   ''

                    ) ?>
                </td>
            </tr>
            <? } ?>
        <? } ?>
    </table>
</div>
