<?php renderable( $p ); ?>
<?php 

$current_file = $p['current_file'];
$is_preview = $p['is_preview'];
$func = $p['func'];
$params = $p['params'];
$display = $p['display'];

$a = argify( $params );

$temp_file = undirify( file_or( $a['file'] ? he( $a['file'] ) : 'NewFile', null, $current_file ) );

?>
<div>
<?= ( !$is_preview ? '<form action="template.php" method="GET" class="template">' : '' ) ?>
<input 
    class="template-file"
    type="text" 
    <?= ( $is_preview ? 'name=""' : 'name="file"' ) ?>
    value="<?= he( $temp_file ) ?>" 
    data-original="<?= he( $temp_file ) ?>"
    title="From <?= he( $a['template'] ) ?>"
    size="<?= ( strlen( $temp_file ) + 5 ) ?>"
>
<?php if( !isset( $a['template'] ) || $a['template'] == "" ) {
    
        $matched_templates = git_tags( array( 'template' ) );
    
        if( count( $matched_templates ) <= 0 ) {
            $replacement = 'No templates available.';
            return $replacement;
        }
        ?>
<select name="template">
    <option value=""><?= he( 'Choose a template...' )?></option>
    <?php foreach( $matched_templates as $temp => $dummy ) { ?>
        <option value="<?= he( $temp ) ?>"><?= he( minify( undirify( dirname( $temp ), true ) ) . '/' . basename( $temp ) ) ?></option>
    <?php } ?>
</select>
<?php } else { ?>
<input 
    type="hidden" 
    name="template" 
    value="<?= he( file_or( $a['template'], null, $current_file ) ) ?>"
>
<?php } ?>
<input 
    type="submit" 
    value="<?= ( $display ? $display : 'Create from template!' ) ?>"
>
<?= ( !$is_preview ? '</form>' : '' ) ?>
</div>
