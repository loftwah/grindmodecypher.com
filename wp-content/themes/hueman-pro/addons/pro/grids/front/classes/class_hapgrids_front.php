<?php
/**
* FRONT END CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
class PC_HAPGRIDS_front {
      //Access any method or var of the class with classname::$instance -> var or method():
      static $instance;
      public $current_effect;
      public $model;

      function __construct () {
            self::$instance     =& $this;
            //TEST ONLY!!! : ADD INLINE JS AND CSS
            add_action( 'wp_head'                    , array( $this, 'hu_add_inline_css' ), 9999 );
            //END TEST ONLY

            //FILTER THE CLASSIC AND MASONRY GRID CLASSES (COLUMNS)
            add_filter( 'hu_classic_grid_wrapper_classes'    , array( $this, 'hu_set_grid_wrapper_class' ) );
            add_filter( 'hu_masonry_wrapper_classes'         , array( $this, 'hu_set_grid_wrapper_class' ) );
      }

      //FILTER THE GRID CLASSES
      function hu_set_grid_wrapper_class( $classes ) {
            $user_column  = hu_get_option( 'pro_grid_columns' );
            $classes[]    = "cols-{$user_column}";
            return $classes;
      }

      //hook : wp_head
      function hu_add_inline_css() {
      ?>
            <style id="grids-css" type="text/css">
                .post-list .grid-item {float: left; }
                .cols-1 .grid-item { width: 100%; }
                .cols-2 .grid-item { width: 50%; }
                .cols-3 .grid-item { width: 33.3%; }
                .cols-4 .grid-item { width: 25%; }
                @media only screen and (max-width: 719px) {
                      #grid-wrapper .grid-item{
                        width: 100%;
                      }
                }
            </style>
            <?php
      }
}