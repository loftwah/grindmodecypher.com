<?php
/**
* Plugin front end functions
* @author Nicolas GUILLAUME
* @since 1.0
*/
class TC_front_font_customizer {

    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;

    public $is_customizing;

    function __construct () {
        //add_action( 'init'                              , array( $this, 'tc_add_style' ) );
        add_action( 'init'                              , array( $this , 'tc_enqueue_plug_resources' ) , 0 );
        add_action( 'wp_head'                           , array( $this , 'tc_write_gfonts'), 0 );
        add_action( 'wp_head'                           , array( $this , 'tc_write_font_dynstyle'), 0 );
        add_action( 'wp_head'                           , array( $this , 'tc_write_other_dynstyle'), 999 );

        //$this -> is_customizing = isset($_REQUEST['wp_customize']) ? 1 : 0;
    }//end of construct



    function tc_write_gfonts() {
        $_opt_prefix              = TC_wfc::$instance -> plug_option_prefix;
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        if ( ! get_option("{$_opt_prefix}_gfonts") ) {
            TC_utils_wfc::$instance -> tc_update_front_end_gfonts();
        }
        $families   = str_replace( '|', '%7C', get_option("{$_opt_prefix}_gfonts") );
        if ( empty($families) )
            return;
        // May 2020 added param display=swap => Ensure text remains visible during webfont load
        printf('<link rel="stylesheet" id="tc-front-gfonts" href="%1$s">',
            "//fonts.googleapis.com/css?family={$families}&display=swap"
        );
    }

    // hook : wp_head
    // When not customizing write the font very early in a separate stylesheet
    function tc_write_font_dynstyle() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        if ( ! TC_wfc::$instance -> tc_is_customizing() )
            do_action( '__dyn_style' , 'fonts' );
    }

    // hook : wp_head
    function tc_write_other_dynstyle() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        do_action( '__dyn_style' , 'other' );
    }


    /* PLUGIN FRONT END FUNCTIONS */
    function tc_enqueue_plug_resources() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        wp_enqueue_style(
          'font-customizer-style' ,
          sprintf('%1$s/front/assets/css/font_customizer%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
          array(),
          ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
          $media = 'all'
        );

        //register and enqueue jQuery if necessary
        if ( ! wp_script_is( 'jquery', $list = 'registered') ) {
            wp_register_script('jquery', '//code.jquery.com/jquery-latest.min.js', array(), false, false );
        }
        if ( ! wp_script_is( 'jquery', $list = 'enqueued') ) {
          wp_enqueue_script( 'jquery');
        }

        //WFC front scripts
        wp_enqueue_script(
            'font-customizer-script' ,
            sprintf('%1$s/front/assets/js/font-customizer-front%2$s.js' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            array('jquery'),
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
            true
        );

        //localize font-customizer-script with settings fonts
        wp_localize_script(
          'font-customizer-script',
          'WfcFrontParams',
            array(
                'effectsAndIconsSelectorCandidates' => $this -> get_effect_user_settings_localized_data_js(),
                'wfcOptions' => $this -> get_debug_options()
            )
        );
    }

    //The saved options and $default_settings are formed like this :
    //[body] => Array
    //     (
    //         [selector] => body
    //         [subset] =>
    //         [font-family] => Helvetica Neue, Helvetica, Arial, sans-serif
    //         [font-weight] => normal
    //         [font-style] =>
    //         [color] => #5A5A5A
    //         [font-size] => 14px
    //         [line-height] => 20px
    //         [text-align] => inherit
    //         [text-decoration] => none
    //         [text-transform] => none
    //         [letter-spacing] => 0
    //         [static-effect] => none
    //         [important] =>
    //         [title] =>
    //     )

    // [site_title] => Array
    //     (
    //         [selector] => .tc-header .brand .site-title
    //         [subset] =>
    //         [font-family] => Helvetica Neue, Helvetica, Arial, sans-serif
    //         [font-weight] => bold
    //         [font-style] =>
    //         [color] => main
    //         [color-hover] => main
    //         [font-size] => 40px
    //         [line-height] => 38px
    //         [text-align] => inherit
    //         [text-decoration] => none!important
    //         [text-transform] => none
    //         [letter-spacing] => 0
    //         [static-effect] => none
    //         [important] =>
    //         [title] =>
    //     )
    //
    //@return array of effect or icon settings that needs front js treatments => add css classes for effect and hide icon
    function get_effect_user_settings_localized_data_js() {
        $candidates = array();
        foreach ( TC_wfc::$instance -> tc_get_saved_option() as $key => $data) {
            //Are we well formed ?
            if ( ! is_array( $data ) || ! array_key_exists( 'static-effect', $data ) || ! array_key_exists('selector', $data ) )
                return array();

            //Do we have an effect set ?
            if ( ! empty( $data['static-effect'] ) && 'none' != $data['static-effect'] ) {
                $candidates[ $key ] = array( 'static_effect' => $data['static-effect'] , 'static_effect_selector' => $data['selector'] );
            }
        }

        return $candidates;
    }

    //@return array of option when $_GET['wfc_debug'] is true
    function get_debug_options() {
        if ( ! isset( $_GET['wfc_debug'] ) )
            return;
        $theme_name = TC_wfc::$theme_name;
        $opt_name = TC_wfc::$opt_name;
        $plug_option_prefix = TC_wfc::$instance -> plug_option_prefix;
        $before_dec_2017_custom_selector_options = "{$plug_option_prefix}_customs_{$theme_name}";
        return array(
            $opt_name => get_option(  $opt_name ),
            'tc_font_customizer_plug' => get_option( 'tc_font_customizer_plug' ),
            $plug_option_prefix => get_option( $plug_option_prefix ),
            $before_dec_2017_custom_selector_options => get_option( $before_dec_2017_custom_selector_options )
        );
    }

} //end of class
