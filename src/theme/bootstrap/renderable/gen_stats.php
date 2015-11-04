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
                <? if( is_logged_in() ) { ?>
                <li><a href="index.php?file=<?=   $p['file'] ?>">Read</a></li>
                <li><a href="edit.php?file=<?=    $p['file'] ?>">Edit</a></li>
                <li><a href="move.php?file=<?=    $p['file']  ?>">Move</a></li>
                <li><a href="delete.php?file=<?=  $p['file']  ?>">Delete</a></li>
                <? } ?>
                <li><a href="history.php?file=<?= $p['file'] ?>">Revision History</a></li>
                <? if( $c != 'HEAD' ) { ?>
                <li><a href="index.php?file=<?=   $p['file'] ?>">Head Version</a></li>
                <? } ?>
                <li><a href="index.php?file=<?=   $p['file'] . "."  . DIRIFY_SUFFIX  ?>">Directory</a></li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="panel-title">Stats for <?= linkify( '[[' . $p['file'] . ']]' ) ?></span>
            </div>
            <div class="panel-body">
                <div class="stats display">
                    <? $s = &$p['stats'] ?>
                    <div> Character count: <?= to_si( $s['character_count'] ) ?></div>
                    <div> Total words: <?= to_si( $s['total_words'] ) ?> </div>
                    <div> Page Count: <?= to_si( $s['page_count'] ) ?> </div>
                    <div> Dictionary words: <?= to_si( $s['dictionary_words'] ) ?> (<?= percent( $s['dictionary_words'], $s['total_words'] ) ?>) </div>
                    <div> Common words: <?= to_si( $s['common_words'] ) ?> (<?= percent( $s['common_words'], $s['total_words'] ) ?>) </div>
                    <div> Unique words: <?= to_si( $s['distinct_words'] ) ?> (<?= percent( $s['distinct_words'], $s['total_words'] ) ?>) </div>
                    <div> Conjunctions: <?= to_si( $s['conjunctions'] ) ?> (<?= percent( $s['conjunctions'], $s['total_words'] ) ?>) </div>
                    <div> Numerics: <?= to_si( $s['numbers'] ) ?> (<?= percent( $s['numbers'], $s['total_words'] ) ?>)</div>
                    <div> Past Tense (was,wasn't,did,didn't,etc.): <?= to_si( $s['past_tense'] ) ?> (<?= percent( $s['past_tense'], $s['total_words'] ) ?>)</div>
                    <div> Present Tense (is,isn't,are,aren't,etc.): <?= to_si( $s['present_tense'] ) ?> (<?= percent( $s['present_tense'], $s['total_words'] ) ?>)</div>
                    <div> Filter Words (think,feel,seem,etc.): <?= to_si( $s['filter_words'] ) ?> (<?= percent( $s['filter_words'], $s['total_words'] ) ?>)</div>
                    <div> Gerund (-ing): <?= $s['gerund'] ?> (<?= percent( $s['gerund'], $s['total_words'] ) ?>)</div>
                    <div>
                        Top Word Usage
                        <ul>
                        <? foreach( $s['word_counts'] as $word => $count ) { ?>
                            <li><?= $word ?>: <?= to_si( $count ) ?></li>
                        <? } ?>
                            <li>bustle: <?= ( isset( $s['word_counts']['bustle'] ) ? to_si( $s['word_counts']['bustle'] ) : 0 )  ?></li>
                        </ul>
                    <div>
                </div>
            </div>
        </div>
    </div>
</div>
