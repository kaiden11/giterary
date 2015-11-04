<? renderable( $p ); ?>
<?

function _card_sort( $a, $b ) {
    return $a['line'] - $b['line'];
}

?>
<? if( !isset( $p['cards'] ) || !is_array( $p['cards'] ) || count( $p['cards'] ) <= 0 ) { ?>
    <span>No storyboard elements.</span>
<? } else { ?>
    <?

        $enabled_cards = array();
        $disabled_cards = array();

        $enabled_cards = array_filter(
            $p['cards'],
            function( $a ) {
                return !$a['disabled'];
            }
        );

        $disabled_cards = array_filter(
            $p['cards'],
            function( $a ) {
                return $a['disabled'];
            }
        );


        usort( 
            $enabled_cards,
            '_card_sort'
        );

        usort( 
            $disabled_cards,
            '_card_sort'
        );

        $cards = array( 'enabled' => &$enabled_cards, 'disabled' => &$disabled_cards );

    ?>
    <? foreach( $cards as $type => &$c ) { ?>
        <? if( $type == 'disabled' && count( $c ) <= 0 ) { continue; } // Skip discards if they're empty.  ?>
        <div class="<?= ( $type == "disabled" 
            ? "col-md-3" 
            : (
                // Show storyboard cards as wider if there are no
                // discarded cards.
                count( $cards[ 'disabled' ] ) <= 0
                    ?   "col-md-12"
                    :   "col-md-9"
            )
        ) ?>">
            <fieldset>
                <legend><?= ( $type == "enabled" ? "Cards" : "Discards" ) ?></legend>
                <div class="cards <?= $type ?>">
                    <? foreach( $c as $line => &$card ) { ?>
                        <div 
                            id="line_<?= $card['line'] ?>"
                            class="card col-md-2 <?= ( $card['questioned'] ? "questioned" : '' ) ?> <?= ( $card['disabled'] ? "disabled" : "" ) ?>"
                            data-disabled="<?= $card['disabled'] ?>"
                            data-questioned="<?= $card['questioned'] ?>"
                            data-line="<?= $card['line'] ?>"
                        >
                            <div 
                                class="panel <?= ( $card['disabled'] ? "panel-danger" : "panel-default" ) ?> line-<?= $card['line'] ?>"
                            >
                                <div class="panel-heading">
                                    <? 
                                        if( !$card['subject'] ) {
                                            $card['subject'] = excerpt( $card['description'], 100 );
                                        } 
                                    ?>
                                        <span 
                                            class="panel-title"
                                            title="<?= he( $card['subject'] ) ?>"
                                        >
                                            <?= $card['subject'] ?>
                                        </span>
                                </div>
                                <div class="panel-body">
                                    <?= $card['description'] ?>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </fieldset>
        </div>
    <? } ?>
<? } ?>
