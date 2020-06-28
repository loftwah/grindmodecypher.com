<?php
/**
* PRO MASONRY CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HAPMAS {
      static $instance;
      public $front_class;//Will store the pro masonry front instance

      function __construct () {
            self::$instance     =& $this;
            add_action( 'contextualizer_options_filters_setup'    , array( $this,  'maybe_instantiate_front_class_and_load_functions'  ) );
      }//end of construct


      //hook : 'contextualizer_options_filters_setup'
      //instantiates the front class once
      function maybe_instantiate_front_class_and_load_functions() {
            if ( 'masonry-grid' == esc_attr( hu_get_option( 'pro_post_list_design' ) ) && hu_is_post_list() ) {
                  //LOAD PRO MASONRY FUNCTION AND FRONT CLASS
                  require_once( HA_BASE_PATH . 'addons/pro/grids/masonry/front/classes/class_hapmas_front.php' );
                  /* ------------------------------------------------------------------------- *
                  *  LOAD FRONT
                  /* ------------------------------------------------------------------------- */
                  $this -> front_class = new PC_HAPMAS_front();
            }
      }
} //end of class