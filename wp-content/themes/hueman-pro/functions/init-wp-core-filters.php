<?php
/* ------------------------------------------------------------------------- *
 *  WP Core Filters
/* ------------------------------------------------------------------------- */
/*  Add wmode transparent to media embeds
/* ------------------------------------ */
if ( !function_exists( 'hu_embed_wmode_transparent' ) ) {

  function hu_embed_wmode_transparent( $html, $url, $attr ) {
    if ( strpos( $html, "<embed src=" ) !== false )
       { return str_replace('</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque" ', $html); }
    elseif ( strpos ( $html, 'feature=oembed' ) !== false )
       { return str_replace( 'feature=oembed', 'feature=oembed&wmode=opaque', $html ); }
    else
       { return $html; }
  }

}
add_filter( 'embed_oembed_html', 'hu_embed_wmode_transparent', 10, 3 );


/*  Add responsive container to embeds
/* ------------------------------------ */
if ( !function_exists( 'hu_embed_html' ) ) {
  function hu_embed_html( $html, $url ) {
    if ( !file_exists( ABSPATH . WPINC . '/class-wp-oembed.php' ))
      return $html;
    require_once( ABSPATH . WPINC . '/class-wp-oembed.php' );
    $wp_oembed = _wp_oembed_get_object();
    $provider = $wp_oembed -> get_provider( $url, $args = '' );
    if ( !$provider || false === $data = $wp_oembed->fetch( $provider, $url, $args ) ) {
      return $html;
    }
    // Check that we have a valid $data object
    // for https://wordpress.org/support/topic/error-in-theme-6/
    if ( is_object($data) && isset($data->type) ) {
        $type = $data->type;
        switch( $type ) {
            case 'video' :
              $html = sprintf('<div class="video-container">%1$s</div>', $html );
            break;
        }
    }
    return $html;
  }
}
add_filter( 'embed_oembed_html', 'hu_embed_html', 10, 3 );


/*  Add responsive container to jetpack embeds
/* ------------------------------------ */
if ( !function_exists( 'hu_embed_html_jp' ) ) {

  function hu_embed_html_jp( $html ) {
    return '<div class="video-container">' . $html . '</div>';
  }

}
add_filter( 'video_embed_html', 'hu_embed_html_jp' );


/*  Add shortcode support to text widget
/* ------------------------------------ */
add_action( 'after_setup_theme', 'hu_widget_text_do_shortcode' );
function hu_widget_text_do_shortcode() {
    if ( !has_filter( 'widget_text', 'do_shortcode' ) ) {
        add_filter( 'widget_text', 'do_shortcode' );
    }
}

// WP 5.0.0 compat. until the bug is fixed
// this hook fires before the customize changeset is inserter / updated in database
// Removing the wp_targeted_link_rel callback from the 'content_save_pre' filter prevents corrupting the changeset JSON
// more details in this ticket : https://core.trac.wordpress.org/ticket/45292
add_action( 'customize_save_validation_before'       , 'hu_remove_callback_wp_targeted_link_rel' );
function hu_remove_callback_wp_targeted_link_rel() {
    if ( false !== has_filter( 'content_save_pre', 'wp_targeted_link_rel' ) ) {
        remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
    }
}