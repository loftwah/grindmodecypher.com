<?php
/**
* PRO GRIDS CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HAPGRIDS {
      static $instance;
      public $front_class;//Will store the pro grids front instance
      public $masonry_class;
      public $grid_thumb_size_for_columns = 'not_set';//<= is set in ha_set_grids_thumb_size
      function __construct () {
            self::$instance     =& $this;
            add_action( 'after_setup_theme'       , array( $this,  'ha_add_masonry_img_size'  ), 100  );
            add_action( 'hu_hueman_loaded'   , array( $this,  'set_on_hueman_loaded_hooks' ) );
            add_action( 'hu_hueman_loaded'   , array( $this,  'load_grid_types_classes' ) );
            add_action( 'hu_hueman_loaded'   , array( $this,  'load_front_class' ) );
            // Register grids settings.
            // add customizer settings.
            add_filter( 'hu_content_blog_sec', array( $this,  'ha_register_pro_grids_settings' ) );
      }//end of construct

      //hook : 'after_setup_theme'
      //actions to do after_setup_theme
      function ha_add_masonry_img_size() {
            //ADD MASONRY IMAGE SIZE
            add_image_size( 'thumb-large-no-crop', 720, 9999, false );
            add_image_size( 'thumb-medium-no-crop', 520, 9999, false );
            add_image_size( 'thumb-standard-no-crop', 320, 9999, false );
      }

      //hook : 'hu_hueman_loaded'
      //set up hooks
      function set_on_hueman_loaded_hooks() {
            // filter blog standard option.
            add_filter( 'hu_opt_blog-standard'      , array( $this,  'ha_grids_is_blog_standard' ) );
            // filter grid columns option.
            add_filter( 'hu_opt_pro_grid_columns'   , array( $this,  'ha_set_grid_wrapper_columns' ) );
            // for the free classical grid template.
            add_filter( 'hu_grid_columns'           , array( $this,  'ha_set_classical_grid_columns' ) );
            // for masonry and classical grid template, change the thumb size if we have a grid with one column only.
            add_filter( 'hu_grid_thumb_size'        , array( $this,  'ha_set_grids_thumb_size' ), 9 );
            add_filter( 'hu_masonry_grid_thumb_size', array( $this,  'ha_set_grids_thumb_size' ), 9 );
      }

      //hook : 'hu_hueman_loaded'
      function load_grid_types_classes() {
            //LOAD PRO MASONRY CLASS
            require_once( HA_BASE_PATH . 'addons/pro/grids/masonry/init-pro-masonry.php' );
            /* ------------------------------------------------------------------------- *
            *  LOAD MASONRY
            /* ------------------------------------------------------------------------- */
            $this -> masonry_class = new PC_HAPMAS();
      }

      //hook : 'hu_hueman_loaded'
      //instantiates the front class once
      function load_front_class() {
            /* ------------------------------------------------------------------------- *
             *  LOAD FRONT
            /* ------------------------------------------------------------------------- */
            if ( is_object( $this -> front_class ) )
              return;
            require_once( HA_BASE_PATH . 'addons/pro/grids/front/classes/class_hapgrids_front.php' );
            $this -> front_class = new PC_HAPGRIDS_front();

      }

      //hook : 'hu_opt_blog-standard'
      function ha_grids_is_blog_standard() {
            return 'standard' == esc_attr( hu_get_option( 'pro_post_list_design' ) );
      }


      //hook: 'hu_grid_columns'
      //for the free classical grid template
      function ha_set_classical_grid_columns() {
            return esc_attr( hu_get_option( 'pro_grid_columns' ) );
      }


      //hook: 'hu_opt_pro_grid_columns'
      //FILTER THE GRID columns
      function ha_set_grid_wrapper_columns( $user_columns ) {
            $_user_columns = $user_columns  = $user_columns > 0 ? $user_columns : '3';
            //restrict the masonry columns depending on the user choosen layout
            $sb_layout    = hu_get_layout_class();
            $columns      = array( '4', '3', '2', '1' );
                                   // 4, 3, 2, 1
            $matrix       = array(
                  'col-1c'  => array( 1, 1, 1, 1 ),
                  'col-2cl' => array( 0, 1, 1, 1 ),
                  'col-2cr' => array( 0, 1, 1, 1 ),
                  'col-3cm' => array( 0, 0, 1, 1 ),
                  'col-3cl' => array( 0, 0, 1, 1 ),
                  'col-3cr' => array( 0, 0, 1, 1 )
            );

            if ( array_key_exists( $sb_layout, $matrix ) && in_array( $user_columns, $columns ) ) {
                  $match            = false;
                  $keep_searching   = false;
                  foreach ( $columns as $_index => $col ) {
                        if ( $match ) {
                              break;
                        }
                        if( $col == $user_columns ) {
                              if ( true == (bool)$matrix[$sb_layout][$_index] ) {
                                    $match = true;
                              } else {
                                    $keep_searching = true;
                              }
                        }
                        if ( $keep_searching ) {
                              if ( true == (bool)$matrix[$sb_layout][$_index] ) {
                                    $match = true;
                              }
                        }
                        $_user_columns = $col;
                  }
            }
            return $_user_columns;
      }

      //hook: 'hu_grid_thumb_size', 'hu_masonry_grid_thumb_size'
      function ha_set_grids_thumb_size( $size ) {
            if ( 'not_set' != $this->grid_thumb_size_for_columns ) {
                  return $this->grid_thumb_size_for_columns;
            }
            $is_masonry = false !== strpos( current_filter(), 'masonry');
            $cols = hu_get_option( 'pro_grid_columns' );
            if ( '1' === $cols ) {
                  // Let's determine which image size would be the best for the current user layout:
                  // grids will use the same logic used by the content-featured.
                  $map = array(
                        'col-1c'  => 'thumb-xxlarge',//1320
                        'col-2cl' => 'thumb-xlarge',//980
                        'col-2cr' => 'thumb-xlarge',//980
                        'col-3cm' => 'thumb-large',//720
                        'col-3cl' => 'thumb-large',//720
                        'col-3cr' => 'thumb-large'//720
                  );
                  $sb_layout = hu_get_layout_class();
                  $size = array_key_exists( $sb_layout, $map ) ? $map[ $sb_layout ] : $size;
            } else if ( '2' === $cols ) {
                  // Let's determine which image size would be the best for the current user layout:
                  // grids will use the same logic used by the content-featured.
                  $map = array(
                        'col-1c'  => $is_masonry ? 'thumb-large-no-crop' : 'thumb-large',//720
                        'col-2cl' => $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium', //520
                        'col-2cr' => $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium', //520
                        'col-3cm' => $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium', //520
                        'col-3cl' => $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium', //520
                        'col-3cr' => $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium' //520
                  );
                  $sb_layout = hu_get_layout_class();
                  $size = array_key_exists( $sb_layout, $map ) ? $map[ $sb_layout ] : $size;
            } else if ( '3' === $cols ) {
                  $size = $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium';
            } else if ( '4' === $cols ) {
                  $size = $is_masonry ? 'thumb-medium-no-crop' : 'thumb-medium';
            }
            $this->grid_thumb_size_for_columns = $size;
            return $this->grid_thumb_size_for_columns;
      }

      /**
      * Options
      **/
      //hook : hu_content_blog_sec
      function ha_register_pro_grids_settings( $settings ) {
          $masonry_settings = array(
              'pro_post_list_design'  =>  array(
                  'default'   => 'masonry-grid',
                  'control'   => 'HU_controls' ,
                  'title'     => __( 'Post list design', 'hueman-pro' ),
                  'label'     => __( 'Select post list design type' , 'hueman-pro' ),
                  'section'   => 'content_blog_sec' ,
                  'type'      => 'select' ,
                  'choices'   => array(
                      'standard'          => __( 'Standard list' , 'hueman-pro'),
                      'classic-grid'      => __( 'Classic grid' , 'hueman-pro'),
                      'masonry-grid'      => __( 'Masonry grid' , 'hueman-pro')
                  ),
                  //'active_callback' => 'hu_is_post_list',
                  'priority'        => 20,
                  'ubq_section'   => array(
                      'section' => 'static_front_page',
                      'priority' => '11'
                  )
              ),

              'pro_grid_columns'  =>  array(
                  'default'   => '2',
                  'control'   => 'HU_controls' ,
                  'label'     => __( 'Max number of columns' , 'hueman-pro' ),
                  'section'   => 'content_blog_sec' ,
                  'type'      => 'select' ,
                  'choices'   => array(
                      '1'      => __( '1', 'hueman-pro' ),
                      '2'      => __( '2' , 'hueman-pro' ),
                      '3'      => __( '3' , 'hueman-pro' ),
                      '4'      => __( '4' , 'hueman-pro' )
                  ),
                  'notice'    => __( 'Note : columns are limited to 3 for single sidebar layouts and to 2 for double sidebar layouts.', 'hueman-pro' ),
                  //'active_callback' => 'hu_is_post_list',
                  'priority'        => 22,
                  'ubq_section'   => array(
                      'section' => 'static_front_page',
                      'priority' => '11'
                  )
              ),
          );
          unset( $settings[ 'blog-standard' ] );
          return array_merge( $masonry_settings, $settings );
      }

} //end of class