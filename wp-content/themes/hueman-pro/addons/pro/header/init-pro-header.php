<?php
/**
* PRO HEADER CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HAPH {
    static $instance;
    public $default_slide_model;
    public $default_slider_option_model;
    public $pro_header_slider_short_opt_name = 'pro_slider_header_bg';
    public $pro_header_image_short_opt_name = 'pro_image_header_bg';

    public $front_class;//Will store the pro header front instance

    function __construct () {
        self::$instance     =& $this;

        //Set the default models
        //=> they will be used both server side on front and js browser side in the customizer
        $this -> default_slide_model = array(
            //hidden properties
            'id'                => '',
            'is_default'        => false,//is the slide the default contextual one ?
             // 'slide-video-bg'    => '',
            'slide-src'         => '',//<= this property is not stored in database, added when pre-processing the model for the front end tmpl
            'title'             => '',

            //slide background
            'slide-background'  => '',//a WP attachment id


            //slide caption
            'slide-title'       => '',
            'slide-link-title'  => 0,
            'slide-heading-tag' => 0,
            'slide-subtitle'    => '',
            'slide-cta'         => '',
            'slide-link'        => '',
            'slide-link-target' => 0,
            'slide-custom-link' => ''
            // 'slide-use-custom-skin' => false,
            // 'slide-skin'        => 'dark',
            // 'slide-skin-color'  => '#00000',
            // 'slide-opacity'     => 65,
            // 'slide-text-color'  => '#fffff',
        );
        $this -> default_slider_option_model = array(
            'is_mod_opt'        => true,
            'module_id'         => '',

            //design
            'slider-height'     => 90,
            'slider-min-height' => 500,
            'skin'              => 'dark',
            'skin-opacity'      => 65,
            // 'skin-custom-color' => '#00000',
            // 'text-custom-color' => '#fffff',
            'default-bg-img'    => '',
            'default-bg-color'  => '#00000',

            //content
            'caption-vertical-pos' => 0,
            'fixed-content'     => 0,
            'fixed-title'       => '',
            'fixed-subtitle'    => '',
            'fixed-cta'         => '',
            'fixed-link'        => '',
            'fixed-link-target' => 0,
            'fixed-custom-link' => '',
            'use-hone-title-tag-globally' => 0,//added january 2020 for https://github.com/presscustomizr/hueman-pro-addons/issues/199
            'title-max-length'  => 70,
            'subtitle-max-length' => 100,
            'font-ratio'        => 0,
            'use-contextual-data' => 1,
            'post-metas'       => 1,
            'display-cats'     => 1,
            'display-comments'     => 1,
            'display-auth-date'     => 1,

            //effects and performances
            'autoplay'          => false,
            'slider-speed'      => 5,
            'pause-on-hover'    => true,
            'lazyload'          => 1,
            'freescroll'        => 0,
            'parallax'          => 1,
            'parallax-speed'    => 55
        );


        //LOAD PRO HEADER FUNCTION AND FRONT CLASS
        require_once( HA_BASE_PATH . 'addons/pro/header/front/classes/class_hap_front.php' );
        add_action( 'hu_hueman_loaded', array( $this, 'maybe_instantiate_front_class_and_load_functions' ) );


        // Register dynamically
        add_action( 'after_setup_theme', array( $this , 'hu_load_header_slider_module' ), 40 );

        // add the body-background to the collection of filtrable candidates
        add_filter( 'ctx_filtrable_candidates_before_setting_up_option_filters', array( $this , 'hu_ad_add_pro_header_slider_options_to_filtrable_candidates' ) );

        //Register pro settings
        //add customizer settings
        //add_filter( 'hu_header_image_sec'   , array( $this, 'ha_register_pro_header_settings' ) );
        //add_action( 'customize_register' , array( $this , 'hu_alter_wp_customizer_settings' ), 2000, 1 );

        //register customizer partials
        //add_action( 'customize_register', array( $this, 'ha_pro_header_register_partials' ) );

        //The customizer slider ajax action
        //add_action( 'hu_hueman_loaded', array( $this,  'ha_hook_slider_ajax' ) );
    }//end of construct

    // add the pro header option to the collection of filtrable candidates
    function hu_ad_add_pro_header_slider_options_to_filtrable_candidates( $filtrable_candidates) {
        if ( ! ctx_we_can_contextualize_not_wp_core_options() )
          return $filtrable_candidates;

        if ( !empty( $filtrable_candidates[ 'multidim_options' ] ) ) {
            if ( !empty( $filtrable_candidates[ 'multidim_options' ][ 'hu_theme_options'] ) ) {
                $filtrable_candidates[ 'multidim_options' ][ 'hu_theme_options'][] = $this->pro_header_slider_short_opt_name;
                $filtrable_candidates[ 'multidim_options' ][ 'hu_theme_options'][] = 'display-a-pro-header-slider';
            }
        }

        return $filtrable_candidates;
    }

    /* ------------------------------------------------------------------------- *
     *  LOAD CONTEXTUALIZED PRO HEADER SLIDER
    /* ------------------------------------------------------------------------- */
    // This function only registers the module and print the js tmpl we need.
    // The setting and control registration is done in js.
    //
    // @after_setup_theme:40
    // Note : fired @priority 40 because we need the contextualizer, loaded @after_setup_theme:30
    function hu_load_header_slider_module() {
        if ( ! ctx_we_can_contextualize_not_wp_core_options() )
          return;
        require_once( dirname( __FILE__ ) . '/czr_slide_module/czr_slide_module.php' );
        hu_register_header_slider_module(
            array(
                'setting_id' => 'bulk_setting_id',//<= the setting id will be generated in js when dynamically registering

                'base_url_path' => HA_BASE_URL . 'addons/pro/header/czr_slide_module',
                'version' => HUEMAN_VER,
            )
        );
    }



    //hook : 'hu_hueman_loaded'
    //instantiates the front class once
    function maybe_instantiate_front_class_and_load_functions() {
        /* ------------------------------------------------------------------------- *
         *  LOAD FRONT
        /* ------------------------------------------------------------------------- */
        // if ( is_object( $this -> front_class ) )
        //   return;

         //Load functions
        require_once( HA_BASE_PATH . 'addons/pro/header/front/hap-functions.php' );
        $this -> front_class = new PC_HAP_front();
    }













    //hook : hu_header_design_sec
    // function ha_register_pro_header_settings( $settings ) {
    //     $pro_header_slider_short_opt_name = $this -> pro_header_slider_short_opt_name;//'pro_slider_header_bg'
    //     //$pro_header_image_short_opt_name = $this -> pro_header_image_short_opt_name;//'pro_image_header_bg'
    //     $ph_settings = array(
    //       'pro_header_type' => array(
    //           'default'   => 'classical',
    //           'control'   => 'HU_controls',
    //           'label'     => __('Header style', 'hueman'),
    //           'section'   => 'header_image_sec',
    //           'type'      => 'select',
    //           'choices' => array(
    //               'classical'   => __( 'Classical header' , 'hueman' ),
    //               //'static-img'  => __( 'Static Image', 'hueman' ),
    //               'slider'      => __( 'Full height slider background' , 'hueman' )
    //           ),
    //           'priority'  => 5,
    //           'notice'    => __( 'Select the full height slider background to start building your header slider.' , 'hueman' )
    //       ),
    //       // "{$pro_header_slider_short_opt_name}" => array(
    //       //     'default'   => array(),//empty array by default
    //       //     'control'   => 'HU_Customize_Modules',
    //       //     'label'     => __('Large Header Slider', 'hueman'),
    //       //     'section'   => 'header_image_sec',
    //       //     'type'      => 'czr_module',
    //       //     'module_type' => 'czr_slide_module',
    //       //     'transport' => 'refresh',//( function_exists( 'hu_is_partial_refreshed_on' ) && hu_is_partial_refreshed_on() ) ? 'postMessage' : 'refresh',
    //       //     'priority'  => 10
    //       // )
    //       // "{$pro_header_image_short_opt_name}" => array(
    //       //     'default'   => array(),//empty array by default
    //       //     'control'   => 'HU_Customize_Modules',
    //       //     'label'     => __('Large Header Static Image', 'hueman'),
    //       //     'section'   => 'header_design_sec',
    //       //     'type'      => 'czr_module',
    //       //     'module_type' => 'czr_header_img_module',
    //       //     'transport' => hu_is_partial_refreshed_on() ? 'postMessage' : 'refresh',
    //       //     'priority'  => 10
    //       // )

    //     );

    //     return array_merge( $ph_settings, $settings );
    // }

    //hook : customize_register
    // function hu_alter_wp_customizer_settings( $wp_customize ) {
    //       if ( is_object( $wp_customize -> get_section( 'header_image_sec' ) ) ) {
    //           $wp_customize -> get_section( 'header_image_sec' ) -> title = __( 'Header Image and Slider', 'hueman' );
    //       }
    //       if ( is_object( $wp_customize -> get_panel( 'hu-header-panel' ) ) ) {
    //           $wp_customize -> get_panel( 'hu-header-panel' ) -> czr_subtitle = __( 'Header Slider, Image, Menu, Widget', 'hueman' );
    //       }
    // }





















    /* ------------------------------------------------------------------------- *
     *  CUSTOMIZER PARTIALS
    /* ------------------------------------------------------------------------- */
    //hook : customize_register
    // function ha_pro_header_register_partials( WP_Customize_Manager $wp_customize ) {
    //     // if ( 'slider' != hu_get_option( 'pro_header_type' ) )
    //     //     return;

    //     //Bail if selective refresh is not available (old versions) or disabled (for skope for example)
    //     if ( ! isset( $wp_customize->selective_refresh ) || ! ( function_exists( 'hu_is_partial_refreshed_on' ) && hu_is_partial_refreshed_on() ) ) {
    //         return;
    //     }
    //     $pro_header_slider_short_opt_name = $this -> pro_header_slider_short_opt_name;//'pro_slider_header_bg'

    //     $wp_customize->selective_refresh->add_partial( "{$pro_header_slider_short_opt_name}", array(
    //         'selector' => '#ha-large-header',
    //         'container_inclusive' => false,//True means that we want to refresh the parent node as well as it’s children instead of just the children.
    //         'settings' => array( "hu_theme_options[{$pro_header_slider_short_opt_name}]" ),
    //         'render_callback' => array( $this, 'pro_header_partial_callback' )
    //         //'type' => 'my_partial'
    //     ) );
    // }


    // function pro_header_partial_callback() {
    //     if ( class_exists( 'PC_HAP_front' ) ) {
    //       PC_HAP_front::$instance -> hu_print_pro_header_slider_tmpl();
    //     }
    // }


    /* ------------------------------------------------------------------------- *
     *  CUSTOMIZER AJAX ACTIONS - DEPRECATED
    /* ------------------------------------------------------------------------- */
    //The customizer slider ajax action
    //hook : 'hu_hueman_loaded'
    // function ha_hook_slider_ajax() {
    //     if ( ! is_admin() )
    //       return;
    //     add_action( 'wp_ajax_czr_slider_mod_get_default_item', array( $this, 'ha_ajax_get_slider_mod_get_default_item' ) );
    // }

    // //hook : 'wp_ajax_czr_slider_mod_get_default_item'
    // function ha_ajax_get_slider_mod_get_default_item() {
    //     global $wp_customize;
    //     if ( ! is_user_logged_in() ) {
    //         wp_send_json_error( 'unauthenticated' );
    //     }
    //     if ( ! current_user_can( 'edit_theme_options' ) ) {
    //       wp_send_json_error('user_cant_edit_theme_options');
    //     }
    //     if ( ! $wp_customize->is_preview() ) {
    //         wp_send_json_error( 'not_preview' );
    //     } else if ( ! current_user_can( 'customize' ) ) {
    //         status_header( 403 );
    //         wp_send_json_error( 'customize_not_allowed' );
    //     } else if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    //         status_header( 405 );
    //         wp_send_json_error( 'bad_method' );
    //     }
    //     $action = 'save-customize_' . $wp_customize->get_stylesheet();
    //     if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
    //         wp_send_json_error( 'invalid_nonce' );
    //     }
    //     wp_send_json_success( array( 'items' => get_post_thumbnail_id() ) );
    // }
} //end of class