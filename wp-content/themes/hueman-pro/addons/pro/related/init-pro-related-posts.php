<?php
/**
* PRO RELATED POSTS CLASS
*/
final class PC_HAPRELPOSTS {
    static $instance;
    public $related_post_model;
    public $pro_related_posts_short_opt_name = 'pro_related_posts';

    function __construct () {
        self::$instance     =& $this;

        //Set the default models
        //=> they will be used both server side on front and js browser side in the customizer
        $this -> related_post_model = array(
            //hidden properties
            'id'            => '',
            'title'         => '',

            //design
            'enable'        => true,
            'col_number'    => 3,
            //'cell_height'   => 'normal',//can take normal, tall
            'display_heading' => true,
            'heading_text'   => __('You may also like...', 'hueman-pro'),
            'freescroll'    => true,
            'ajax_enabled'  => true,

            //post filters
            'post_number'   => 10,
            'order_by'      => 'rand',//can take rand, comment_count, date
            'related_by'    => 'categories'//can take : categories, tags, post_format, all
        );

        //Set hooks on 'template_redirect'. Skope is cached at this point.
        add_action( 'template_redirect', array( $this, 'set_hooks_and_model') );

        //Register pro settings
        //add customizer settings
        // REgistered dynamically since June 2018
        //add_filter( 'hu_content_single_sec'   , array( $this, 'ha_register_pro_settings' ) );

        //register customizer partials
        add_action( 'customize_register', array( $this, 'ha_register_partials' ) );

        add_action( 'customize_register' , array( $this , 'hu_alter_wp_customizer_settings' ), 2000, 1 );

        add_action( 'hu_ajax_ha_inject_pro_related', array( $this , 'hu_ajax_render_pro_related' ) );

        // Register dynamically
        add_action( 'after_setup_theme', array( $this , 'hu_load_related_posts_module' ), 20 );
    }//end of construct

    //@after_setup_theme:20
    function hu_load_related_posts_module() {
        // load the social links module
        require_once( dirname( __FILE__ ) . '/czr_related_posts_module/czr_related_posts_module.php' );
        $pro_related_posts_short_opt_name = $this -> pro_related_posts_short_opt_name;//'pro_related_posts'

        hu_register_related_posts_module(
            array(
                'setting_id' => 'hu_theme_options[' . $pro_related_posts_short_opt_name .']',

                'base_url_path' => HA_BASE_URL . 'addons/pro/related/czr_related_posts_module',
                'version' => HUEMAN_VER,

                'option_value' => hu_get_option( $pro_related_posts_short_opt_name ), // for dynamic registration
                'setting' => array(
                    'type' => 'option',
                    'default'  => array(),
                    //'transport' => hu_is_partial_refreshed_on() ? 'postMessage' : 'refresh',
                    // 'sanitize_callback' => 'hu_sanitize_callback__czr_social_module',
                    // 'validate_callback' => 'hu_validate_callback__czr_social_module'
                ),

                // 'section' => array(
                //     'id' => 'social_links_sec',
                //     'title' => __( 'Social links', 'hueman' ),
                //     'panel' => 'hu-general-panel',
                //     'priority' => 30
                // ),

                'control' => array(
                    'priority' => 10,
                    'label' => __('Better Related Posts', 'hueman-pro'),
                    'description' => __( 'Display related articles below the post' , 'hueman-pro'),
                    'type'  => 'czr_module',
                    'section' => 'content_single_sec',
                    'transport' => hu_is_partial_refreshed_on() ? 'postMessage' : 'refresh',
                )
            )
        );
    }

    /* ------------------------------------------------------------------------- *
     *  VIEW FRONT END
    /* ------------------------------------------------------------------------- */
    //hook : 'template_redirect'
    function set_hooks_and_model() {
        //filter the default condition to enable the related posts
        //which is '1' != hu_get_option( 'related-posts' )
        // filter is declared in single-tmpl.php
        add_filter( 'hu_is_related_posts_enabled', array( $this , 'ha_maybe_enable_related_posts'));

        //overrides the default template
        add_filter( 'hu_tmpl_related-posts'      , array( $this , 'ha_load_pro_related_posts_tmpl'));

        add_action( 'wp_enqueue_scripts', array( $this, 'ha_maybe_enqueue_flickity') );
    }

    //filter : hu_is_related_posts_enabled, declared in single-tmpl.php
    function ha_maybe_enable_related_posts( $is_enabled_in_free ) {
        $db_opts = hu_get_option( HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name );

        //Are we well formed ?
        $db_opts = ( ! is_array( $db_opts ) || ! array_key_exists('id', $db_opts ) ) ? array() : $db_opts;
        //if the advanced related has been set, then check if 'enable' is true
        //if not, fallback on hueman free condition
        return array_key_exists( 'enable', $db_opts ) ? esc_attr( hu_booleanize_checkbox_val( $db_opts['enable'] ) ) : $is_enabled_in_free;
    }


    //hook : hu_tmpl_related_posts
    function ha_load_pro_related_posts_tmpl() {
        $_full_path = ha_locate_template( 'addons/pro/related/front/tmpl/related-posts.php' );
        if ( ! file_exists($_full_path) )
          return;
        return $_full_path;
    }


    // @wp_enqueue_scripts
    function ha_maybe_enqueue_flickity() {
        if ( !$this->ha_maybe_enable_related_posts( '1' != hu_get_option( 'related-posts' ) ) )
          return;
        wp_enqueue_script(
            'hph-flickity-js',
            sprintf('%1$saddons/pro/header/assets/front/vendors/flickity%2$s.js' , HU_AD() -> ha_get_base_url() , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            null,
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? HUEMAN_VER . time() : HUEMAN_VER,
            true
        );
    }

    /* ------------------------------------------------------------------------- *
     * REGISTER CZR MODULE
    /* ------------------------------------------------------------------------- */
    //hook : hu_content_single_sec
    // function ha_register_pro_settings( $settings ) {
    //     $pro_related_posts_short_opt_name = $this -> pro_related_posts_short_opt_name;//'pro_related_posts'
    //     $new_settings = array(
    //         "{$pro_related_posts_short_opt_name}" => array(
    //             'default'   => array(),//empty array by default
    //             'control'   => 'HU_Customize_Modules',
    //             'title'     => __("Related posts", 'hueman'),
    //             'label'     => __('Better Related Posts', 'hueman'),
    //             'description' => __( 'Display related articles below the post' , 'hueman'),
    //             'section'   => 'content_single_sec',
    //             'type'      => 'czr_module',
    //             'module_type' => 'czr_related_posts_module',
    //             'transport' => hu_is_partial_refreshed_on() ? 'postMessage' : 'refresh',
    //             //'transport' => 'refresh',
    //             'priority'  => 10
    //         )
    //     );
    //     return array_merge( $new_settings, $settings );
    // }

    //hook : customize_register
    function hu_alter_wp_customizer_settings( $wp_customize ) {
          if ( is_object( $wp_customize -> get_control( 'hu_theme_options[related-posts]' ) ) ) {
              $wp_customize -> get_control( 'hu_theme_options[related-posts]' ) -> active_callback = '__return_false';
          }
    }


    /* ------------------------------------------------------------------------- *
     *  CUSTOMIZER PARTIALS
    /* ------------------------------------------------------------------------- */
    //hook : customize_register
    function ha_register_partials( WP_Customize_Manager $wp_customize ) {
        //Bail if selective refresh is not available (old versions) or disabled (for skope for example)
        if ( ! isset( $wp_customize->selective_refresh ) || ! ( function_exists( 'hu_is_partial_refreshed_on' ) && hu_is_partial_refreshed_on() ) ) {
            return;
        }
        $pro_related_posts_short_opt_name = $this -> pro_related_posts_short_opt_name;//'pro_related_posts'

        $wp_customize->selective_refresh->add_partial( "{$pro_related_posts_short_opt_name}", array(
            'selector' => '#pro-related-posts',
            'container_inclusive' => true,//True means that we want to refresh the parent node as well as it’s children instead of just the children.
            'settings' => array( "hu_theme_options[{$pro_related_posts_short_opt_name}]" ),
            'render_callback' => array( $this, 'pro_header_partial_callback' )
            //'type' => 'my_partial'
        ) );
    }

    //callback for ha_register_partials
    function pro_header_partial_callback() {
        ha_locate_template( 'addons/pro/related/front/tmpl/related-posts.php', $load = true, $require_once = true );
    }


    /* ------------------------------------------------------------------------- *
     *  FRONT AJAX RENDER
    /* ------------------------------------------------------------------------- */
    //hook : 'hu_ajax_ha_inject_pro_related'
    //@see hu_ajax_response() in functions/init-front.php => replaces the WP ajax way, not available on front end
    //check_ajax_referer( 'hu-front-nonce', 'HuFrontNonce' ); is checked in hu_ajax_response
    function hu_ajax_render_pro_related() {
        ob_start();
          ha_locate_template( 'addons/pro/related/front/tmpl/related-posts-content.php', $load = true );
        $_html = ob_get_contents();
        if ($_html) ob_end_clean();

        wp_send_json_success( array('html' => $_html ) );
    }
} //end of class