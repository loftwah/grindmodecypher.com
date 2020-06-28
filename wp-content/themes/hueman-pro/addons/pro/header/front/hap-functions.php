<?php
/* ------------------------------------------------------------------------- *
 *  TITLE AND SUBTITLE
/* ------------------------------------------------------------------------- */
//the h2 title
// function hu_get_hph_title( $title = null ) {
//   $title = is_null( $title ) ? apply_filters( 'hph_title', 'Hueman') : $title;

//   return empty( $title ) ? false : sprintf('<h2 class="hph-title display-1 thick %1$s">%2$s</h2>',
//       apply_filters( 'hph_title_size', 'very-big'),
//       $title
//   );
// }

// //the h3 subtitle
// function hu_get_hph_subtitle( $subtitle = null ) {
//   $subtitle = is_null( $subtitle ) ? apply_filters( 'hph_subtitle', 'Inspire and Empower' ) : $subtitle;

//   return empty( $subtitle ) ? false : sprintf('<h3 class="hph-subtitle semi-bold">%1$s</h3>',
//       $subtitle
//   );
// }

// //the cta button
// function hu_get_hph_cta( $cta_text = null ) {
//   $cta_text = is_null( $cta_text ) ? apply_filters('hph_cta_text', 'Download FREE') : $cta_text;

//   return empty( $cta_text ) ? false : apply_filters(
//       'hph_cta',
//       sprintf('<a href="#" target="_blank" class="hph-cta btn btn-fill btn-skin btn-large">%1$s</a>',
//           $cta_text
//       )
//   );
// }
function hu_can_have_default_slide_title() {
  return hu_is_real_home() || is_singular() || is_search() || is_author() || is_archive() || is_404();
}


//this is used on front end and when sending the query data to the customizer
//default $title is get_bloginfo('name')
add_filter( 'hph_title', 'hu_set_hph_title');
function hu_set_hph_title( $title ) {
  if ( hu_is_real_home() )
    return $title;
  elseif ( is_single() )
    $title = get_the_title();
  elseif( is_page() )
    $title = hu_get_page_title();//might include a sub_heading, handled with post metas. @todo => make sure it works
  elseif( is_search() ) {
    $title = hu_get_search_title();
  }
  elseif( is_author() ) {
    $title = hu_get_author_title();
  }
  elseif( is_category() || is_tag() ) {
    $title = hu_get_term_page_title();
  }
  elseif( is_day() || is_month() || is_year() ) {
    $title = hu_get_date_archive_title();
  }
  elseif ( is_404() )
    $title = hu_get_404_title();
  return $title;
}

//this is used on front end and when sending the query data to the customizer
//default $subtitle is get_bloginfo('description')
add_filter( 'hph_subtitle', 'hu_set_hph_subtitle');
function hu_set_hph_subtitle( $subtitle ) {
  if ( hu_is_real_home() )
    return $subtitle;
  return false;
}


/* ------------------------------------------------------------------------- *
 *  IMAGE
/* ------------------------------------------------------------------------- */
add_filter( 'hph_img_src', 'hu_set_hph_img_src' );
function hu_set_hph_img_src( $src ) {
  if ( ! is_singular() || ( is_singular() && ! has_post_thumbnail() ) )
    return $src;

  $thumb_id = get_post_thumbnail_id();
  $img_array = wp_get_attachment_image_src( $thumb_id, 'large');
  return $img_array[0];
}