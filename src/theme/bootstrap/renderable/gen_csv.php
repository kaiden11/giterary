<? renderable( $p ) ?>
<?

$head = '';
$body = '<tbody>';

foreach( $p['contents'] as $i => $row ) {
    if( $i == 0 ) {
        $head .= '<thead>';
        $head .= '<tr class="header">';

        foreach( $row as $j => $heading ) {

            $heading = trim( $heading ); 
            $head .= th( "<span>$heading</span>" );

         }
        $head .= '</tr>';
        $head .= '</thead>';
    } else {
        $body .= "\n<tr>";

        foreach( $row as $j => $point ) {

            $body .= td( $point );

         }
        $body .= '</tr>';
    }
}

$body .= '</tbody>';

?>
<div id="csv-output">
    <table class="tabulizer <?= $p['show_search'] == true ? "show-search" : '' ?> <?= $p['sort'] ? '' : 'no-sort' ?>">
        <?= $head ?>
        <?= $body ?>
    </table>
</div>
