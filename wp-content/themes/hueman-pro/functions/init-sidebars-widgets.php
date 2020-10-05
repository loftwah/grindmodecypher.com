<?php
/*  Loads Custom Widgets
/* ------------------------------------ */
// June 2019 : Hueman widgets can now be overriden from a child theme
// @see https://github.com/presscustomizr/hueman/issues/798
locate_template( 'functions/widgets/alx-tabs.php', true );
locate_template( 'functions/widgets/alx-video.php', true );
locate_template( 'functions/widgets/alx-posts.php', true );


/*  Register sidebars
/* ------------------------------------ */
//@return the array of built-in widget zones
function hu_get_default_widget_zones() {
  return array(
    'primary' => array(
      'name' => __( 'Primary', 'hueman-pro' ),
      'id' => 'primary',
      'description' => __( "Full width widget zone. Located in the left sidebar in a 3 columns layout. Can be on the right of a 2 columns sidebar when content is on the left.", 'hueman-pro'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
    ),
    'secondary' => array(
      'name' => __( 'Secondary', 'hueman-pro' ),
      'id' => 'secondary',
      'description' => __( "Full width widget zone. Located in the right sidebar in a 3 columns layout.", 'hueman-pro'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    ),
    'footer-1' => array(
      'name' => __( 'Footer 1', 'hueman-pro'),
      'id' => 'footer-1',
      'description' => __( "Widgetized footer 1", 'hueman-pro'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    ),
    'footer-2' => array(
      'name' => __('Footer 2', 'hueman-pro' ),
      'id' => 'footer-2',
      'description' => __("Widgetized footer 2", 'hueman-pro' ),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    ),
    'footer-3' => array(
      'name' => __('Footer 3', 'hueman-pro' ),
      'id' => 'footer-3',
      'description' => __("Widgetized footer 3", 'hueman-pro' ),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    ),
    'footer-4' => array(
      'name' => __('Footer 4', 'hueman-pro' ),
      'id' => 'footer-4',
      'description' => __("Widgetized footer 4", 'hueman-pro' ),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">','after_title' => '</h3>'
    ),
    'header-ads' => array(
      'name' => __( 'Header (next to logo / title)', 'hueman-pro' ),
      'id' => 'header-ads',
      'description' => __( "The Header Widget Zone is located next to your logo or site title.", 'hueman-pro'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    ),
    'footer-ads' => array(
      'name' => __('Footer Full Width', 'hueman-pro'),
      'id' => 'footer-ads',
      'description' => __( "The Footer Widget Zone is located before the other footer widgets and takes 100% of the width. Very appropriate to display a Google Map or an advertisement banner.", 'hueman-pro'),
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>'
    )
  );
}

//@return an array of default widgets ids
function hu_get_widget_zone_ids() {
  $widgets = hu_get_default_widget_zones();
  return array_keys( $widgets );
}


//@return an array of widget option names
function hu_get_registered_widgets_option_names() {
  global $wp_registered_widgets;
  $opt_names = array();
  foreach ($wp_registered_widgets as $id => $data ) {
    if ( !isset($data['callback']) || !isset($data['callback'][0]) || !isset($data['callback'][0] -> option_name ) )
      continue;
    if ( !in_array( $data['callback'][0] -> option_name, $opt_names ) )
      array_push( $opt_names, $data['callback'][0] -> option_name );
  }
  return $opt_names;
}



//@return the array describing the previous correspondance between location => widget zone name
function hu_get_widget_zone_rosetta_stone() {
  return array(
    's1'          => 'primary',
    's2'          => 'secondary',
    'header-ads'  => 'header-ads',
    'footer-ads'  => 'footer-ads',
    'footer-1'    => 'footer-1',
    'footer-2'    => 'footer-2',
    'footer-3'    => 'footer-3',
    'footer-4'    => 'footer-4'
  );
}

//helper
//@return array()
//used both on front end and in the customizer
function hu_get_contexts_list() {
  return array(
    '_all_'             => __('All contexts', 'hueman-pro'),
    'home'              => __('Home', 'hueman-pro'),
    'blog-page'         => __('Blog Page', 'hueman-pro'),
    'page'              => __('Pages', 'hueman-pro'),
    'single'            => __('Single Posts', 'hueman-pro'),
    'archive'           => __('Archives', 'hueman-pro'),
    'archive-category'  => __('Categories', 'hueman-pro'),
    'search'            => __('Search Results', 'hueman-pro'),
    '404'               => __('404 Error Pages', 'hueman-pro')
  );
}


//the original mapping (s1 and s2) has to be kept since it is used in many places
//widget_zone_name => location, title
function hu_get_builtin_widget_zones_location() {
  return array(
    'primary'     => array( 's1' => __('Primary Sidebar (on the left in a 3 columns layout)', 'hueman-pro') ),
    'secondary'   => array( 's2' => __('Secondary Sidebar (on the right in a 3 columns layout)', 'hueman-pro') ),
    'footer-1'    => array( 'footer-1' => __('Footer 1', 'hueman-pro') ),
    'footer-2'    => array( 'footer-2' => __('Footer 2', 'hueman-pro') ),
    'footer-3'    => array( 'footer-3' => __('Footer 3', 'hueman-pro') ),
    'footer-4'    => array( 'footer-4' => __('Footer 4', 'hueman-pro') ),
    'header-ads'  => array( 'header-ads' => __('Header (next to logo / title)', 'hueman-pro') ),
    'footer-ads'  => array( 'footer-ads' => __('Footer Full Width', 'hueman-pro') )
  );
}


if ( !function_exists( 'hu_maybe_register_builtin_widget_zones' ) ) :
  function hu_maybe_register_builtin_widget_zones() {
    $_map = hu_get_default_widget_zones();
    foreach ( $_map as $zone_id => $data ) {
      register_sidebar( $data );
    }
  }
endif;
add_action( 'widgets_init', 'hu_maybe_register_builtin_widget_zones' );




/*  Register custom sidebars
/* ------------------------------------ */
if ( !function_exists( 'hu_maybe_register_custom_widget_zones' ) ) :
  function hu_maybe_register_custom_widget_zones() {
    $customized = array();

    if ( hu_is_customizing() && isset($_POST['customized']) ) {
      $customized = json_decode( wp_unslash( $_POST['customized'] ), true );

      if ( isset($customized['hu_theme_options[sidebar-areas]']) )
        $sidebars = $customized['hu_theme_options[sidebar-areas]'];
      else
        $sidebars = hu_get_option('sidebar-areas', array());
    }
    else {
      $sidebars = hu_get_option('sidebar-areas', array());
    }

    //at this point we need smthg really clean
    if ( !is_array($sidebars) || empty( $sidebars ) )
       return;

    $default_args = array(
      'name' => '',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>','before_title' => '<h3 class="widget-title">','after_title' => '</h3>'
    );

    $default_zones = hu_get_default_widget_zones();

    foreach( $sidebars as $sb ) {
      if ( !isset($sb['id']) || empty($sb['id']) )
        return;

      //is it a built-in one?
      //=> in this case it's been registered another way
      $_id = $sb['id'];
      if ( isset( $default_zones[$_id]) )
        continue;

      $args = wp_parse_args(
        array(
          'name' => isset($sb['title']) ? ''. esc_attr( $sb['title'] ).'' : '',
          'id' => ''. esc_attr( strtolower($sb['id']) ).''
        ),
        $default_args
      );

      register_sidebar( $args );
    }//for each
  }
endif;



//add_action( hu_is_customize_preview_frame() ? 'customize_preview_init' : 'widgets_init' , 'hu_maybe_register_custom_widget_zones' );
add_action( 'widgets_init' , 'hu_maybe_register_custom_widget_zones' );
//add_action( 'customize_preview_init' , 'hu_maybe_register_custom_widget_zones' );



//helper
//must be fired after 'wp' to have access to the $wp_query
//"real" because left and right sidebars are always registered
//@return array of locations
function hu_get_available_widget_loc() {
  $_available       = array();
  $_footer_widgets  = intval ( hu_get_option('footer-widgets') );
  $layout           = hu_get_layout_class();

  if ( hu_is_checked('header-ads') )
    $_available[] = 'header-ads';
  if ( hu_is_checked('footer-ads') )
    $_available[] = 'footer-ads';
  if ( $_footer_widgets >= 1 )
    $_available[] = 'footer-1';
  if ( $_footer_widgets >= 2 )
    $_available[] = 'footer-2';
  if ( $_footer_widgets >= 3 )
    $_available[] = 'footer-3';
  if ( $_footer_widgets >= 4 )
    $_available[] = 'footer-4';

  //for left and right sidebar, it depends on the $layout class computed with options and post_metas
  if ( $layout != 'col-1c' )
    $_available[] = 's1';
  if ( in_array( $layout, array('col-3cm', 'col-3cm', 'col-3cr' ) ) )
    $_available[] = 's2';

  return $_available;
}

