<? renderable( $p ); ?>
<? 
# $stash['css'][] = 'search.css';
# $stash['css'][] = 'table.css';

$ywd_fmt         = "Y-m (M)";
$earliest_epoch = PHP_INT_MAX;
$latest_epoch   = 0;

// Find earlie3st and latest epoch dates
foreach( $p['history'] as $f => &$history ) {

    foreach( $history as &$h ) {
        if( $h['epoch'] < $earliest_epoch ) {
            $earliest_epoch = $h['epoch'];
        }

        if( $h['epoch'] > $latest_epoch ) {
            $latest_epoch = $h['epoch'];
        }
    }
}

$start_dt   = new DateTime();
$end_dt     = new DateTime();

$start_dt->setTimestamp( $earliest_epoch );
$end_dt->setTimestamp( $latest_epoch );

$yw_counts = array();
while( $start_dt < $end_dt ) {

    $yw_counts[ $start_dt->format( $ywd_fmt ) ] = array();

    $start_dt->add( new DateInterval( 'P7D' ) );
}

foreach( $p['history'] as $f => &$history ) {

    foreach( $history as &$h ) {

        $yw = date( $ywd_fmt , $h['epoch'] );
        
        if( !isset( $yw_counts[ $yw ][ $f ] ) ) {
            $yw_counts[ $yw ][ $f ] = 0;
        }

        $yw_counts[ $yw ][ $f ]++;
    }
}

// We don't need any of the history at this point
unset( $p['history'] );

uksort( 
    $yw_counts,
    function( $a, $b ) {
        return strcmp( $a, $b );
    }
);

// Maximums
$max_yw             = 0;
$max_yw_sum         = 0;
$max_yw_chapter     = 0;

$chapters_counts    = array();
foreach( $yw_counts as $yw => $r ) { 
   
    $yw_sum = 0;
    foreach( $r as $f => $c ) {
        $yw_sum += $c;
        if( $c > $max_yw ) { $max_yw = $c; }

        if( !isset( $chapter_counts[ $f ] ) ) {
            $chapter_counts[ $f ] = 0;
        }

        $chapter_counts[ $f ] += $c;
    }

    if( $yw_sum > $max_yw_sum ) { $max_yw_sum = $yw_sum; }
}

foreach( $chapter_counts as $f => $c ) {
    if( $c > $max_yw_chapter ) { $max_yw_chapter = $c; }
}


?>
<style type="text/css">
    .nonbreak {
        white-space:    nowrap;
    }

    .filename {
        max-width:      5rem;
        white-space:    nowrap;
        overflow:       hidden;
        text-overflow:  ellipsis;
    }

    tr td .max, tr th .max {
        color: red;
    }


    @media screen {
        .scrollable {
            max-height: 400px; 
            overflow-y: scroll;
        }
    }
</style>
<div class="timeline container-fluid" >
    <div class="meta display">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span><?= he( $p['title'] ) ?></span>
            </div>
            <div class="panel-body">
                <div 
                    class="table-responsive scrollable"
                >
                    <?php if( count( $p['files'] ) <= 0 ) { ?>
                        No files submitted.
                    <?php } else { ?> 
                        <table class="table table-striped table-hover table-condensed table-bordered"> 
                            <thead>
                                <th
                                    class="nonbreak"
                                >Year-Month</th>
                                <?php foreach( $p['files'] as $f ) { ?>
                                    <th 
                                        title="<?= he( basename( $f ) ) ?>"
                                    >
                                        <div 
                                            class="filename"
                                        >
                                            <?= he( basename( $f ) ) ?>
                                        </div>
                                    </th>
                                <?php } ?>
                                <th>
                                    Sum
                                </th>
                            </thead>
                            <?php

                                $f_counts = array();
                            ?>
                            <tbody>
                                <?php foreach( $yw_counts as $yw => $r ) { ?>
                                    <?php
                                        $yw_c = 0;
                                    ?>
                                    <tr
                                    >
                                        <td
                                            class="nonbreak"

                                        >
                                            <span
                                                <?php /*title="a<?= he( (1900+$t['tm_year']) . '-' . ($t['tm_mon'] + 1 ) . '-' . $t['tm_mday'] )  ?>" */ ?>
                                            >
                                                <?= he( $yw ) ?>

                                            </span>
                                        </td>
                                        <?php foreach( $p['files'] as $f ) { ?>
                                            <?php 
                                                if( !isset( $f_counts[ $f ] ) ) {
                                                    $f_counts[ $f ] = 0;
                                                }
                                            ?>
                                            <td
                                                class=""
                                            >
                                                <?php if( isset( $r[ $f ] ) ) { ?>
                                                    <?php 
                                                        $yw_c += $r[ $f ]; 
                                                        $f_counts[ $f ] += $r[ $f ]; 
                                                    ?>
                                                    <span 
                                                        title="<?= he( undirify( $f ) . ': ' . plural( $r[$f], "commit" ) ) ?>"
                                                        class="glyphicon glyphicon-<?= he( $r[ $f ] > 5 ? "ok-sign" : "ok" ) ?> <?= ( $r[ $f ] == $max_yw ? 'max' : '' ) ?>"
                                                    >
                                                    </span>
                                                <?php } else { ?>
                                                    &nbsp;
                                                <?php } ?>
                                            </td>
                                        <?php } ?>
                                        <td class="text-right">
                                            <span 
                                                class="<?= ( $yw_c == $max_yw_sum ? 'max' : '' ) ?>"
                                            ><?= he( $yw_c ) ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <?php foreach( $p['files'] as $f ) { ?>
                                        <th 
                                            title="<?= he( basename( $f ) ) ?>"
                                        >
                                            <div 
                                                class="filename"
                                            >
                                                <?= he( basename( $f ) ) ?>
                                            </div>
                                        </th>
                                    <?php } ?>
                                    <th>
                                        &nbsp;
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        Totals:
                                    </th>
                                        <?php $total_commits = 0; ?>
                                        <?php foreach( $p['files'] as $f ) { ?>
                                            <?php $total_commits += $f_counts[ $f ]; ?>
                                            <th
                                                class="text-right"
                                            >
                                                <span
                                                    title="<?= he( undirify( $f ) ) ?>"
                                                    class="<?= ( $f_counts[ $f ] == $max_yw_chapter ? 'max' : '' ) ?>"
                                                >
                                                    <?= he( $f_counts[ $f ] ) ?>
                                                </span>
                                            </th>
                                        <?php } ?>
                                        <th 
                                            class="text-right"
                                        >
                                            <?= he( $total_commits ) ?>
                                        </th>
                                </tr>

                            </tfoot>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
