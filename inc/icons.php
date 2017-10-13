<?php

/**
 * get an icon
 * $id      string  the name of the icon in the sprite
 * $atts    array   (optional) attributes
 * @return  string  svg code
 */
function get_svg_icon( $id, $atts = array() ) {
    if ( empty( $id ) ) {
        if ( WP_DEBUG == true ) {
            return 'Icon ID empty.';
        }

        return;
    }

    $atts = shortcode_atts(
        array(
            'class' => null,
            'title' => null
            // maybe we want to add desc (<desc>) for even more a11y
        ),
        $atts
    );
    $class = $atts['class'];
    $title = $atts['title'];

    // check if this ID will be in the sprite
    require TEMPLATEPATH . '/dist/sprite/sprite.php';
    if ( ! in_array( $id, $valid_icons ) ) {
        if ( WP_DEBUG == true ) {
            return 'Icon nicht vorhanden.';
        }

        return;
    }

    // will we add extra CSS classes?
    $att_class = ( empty( $class ) ) ? '' : ' ' . $class;

    // create a unique ID
    $att_id = uniqid( 'icon__title--' );

    // additional markup attributes for a11y
    // is this icon only decorative (hidden) or descriptive (title)
    $att_a11y = ( empty( $title ) ) ? ' aria-hidden="true" role="presentation"' : ' aria-labelledby="' . $att_id . '" role="img"';
    $title = ( empty( $title ) ) ? '' : '<title id="' . $att_id . '">' . $title . '</title>';

    // puzzle the URL together
    $url = './dist/sprite/sprite.svg#' . $id;

    // let's roll!
    return '<svg class="icon' . $att_class . '"' . $att_a11y . '>' . $title . '<use xlink:href="' . $url . '"></use></svg>';
}

/**
 * Output an icon
 */
function svg_icon( $id, $atts = array() ) {
    echo get_svg_icon( $id, $atts );
}

/**
 * Copied from WordPress for demonstration purposes
 */
function shortcode_atts( $pairs, $atts ) {
    $atts = ( array )$atts;
    $out = array();
    foreach ( $pairs as $name => $default ) {
        if ( array_key_exists($name, $atts) ) {
            $out[ $name ] = $atts[ $name ];
        }
        else{
            $out[ $name ] = $default;
        }
    }
    return $out;
}