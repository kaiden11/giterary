<?

function kvp( $k, $v ) {
    return he( $k ) . '="' . he( $v ) . '"';
}

function el( $data, $tag, $attr = array() ) {

    if( !is_array( $attr ) ) {
        $attr = array();
    }

    $attrs = '';

    foreach( $attr as $k => $v  ) {
        $attrs .= kvp( $k, $v );
    }

    return "<$tag $attrs>" . trim( $data ) . "</$tag>";

}

function th( $data, $clazz = null  ) {
    if( is_null( $clazz ) ) {
        return el( $data, "th" );
    } else {
        return el( $data, "th", array( "class" => $clazz ) );
    }

}

function td( $data, $clazz = null ) {
    if( is_null( $clazz ) ) {
        return el( $data, "td" );
    } else {
        return el( $data, "td", array( "class" => $clazz ) );
    }
}
?>
