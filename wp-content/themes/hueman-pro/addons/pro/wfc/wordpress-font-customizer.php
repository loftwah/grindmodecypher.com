<?php
/**
 * Plugin Name: WordPress Font Customizer
 * Plugin URI: https://presscustomizr.com/extension/wordpress-font-customizer
 * Description: Make beautiful Google font combinations and apply awesome CSS3 effects to any text of your website. Preview everything right from the WordPress customizer before publishing live. Cross browser compatible, fast and easy, the WordPress Font Customizer is the ultimate tool for typography lovers.
 * Version: 3.2.5
 * Author: Press Customizr
 * Author URI: https://presscustomizr.com
 * License: GPL2+
 * Text Domain: wordpress_font_customizer
 * Domain Path: /lang
 */

/**
* Fires the plugin
* @author Nicolas GUILLAUME
* @since 1.0
*/
if ( ! class_exists( 'TC_wfc' ) ) :
class TC_wfc {
      //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;
    public $version;
    public $plug_name;
    public $plug_id;
    public $plug_file;
    public $plug_version;
    public $plug_prefix;
    public $plug_lang;
    public $tc_default_selector_list;
    public static $theme_name;
    public $tc_property_list;
    //public $tc_selector_list;
    public static $opt_name;
    public $is_customizing;

    public static $_is_new_version = false;
    public static $_is_plugin;

    public static $_selector_list;//<= build from the sets/json according to the theme name. Build on first load.

    function __construct () {
        self::$instance     =& $this;

        /* LICENSE AND UPDATES */
        // the name of your product. This should match the download name in EDD exactly
        $this -> plug_name    = 'WordPress Font Customizer';
        $this -> plug_id      = 15219;
        $this -> plug_file    = __FILE__; //main plugin root file.
        $this -> plug_prefix  = 'font_customizer';
            $this -> plug_version = '3.2.5';

        self::$_is_plugin     = ! did_action('plugins_loaded');

        //define the plug option key
        $this -> plug_option_prefix     = self::$_is_plugin ? 'tc_wfc' : 'tc_pro_wfc';

        //checks if is customizing : two context, admin and front (preview frame)
        $this -> is_customizing = $this -> tc_is_customizing();

        self::$theme_name = $this -> tc_get_theme_name();
        $theme_name       = self::$theme_name;

        // New option name since v3.0.0 customizer module implementation
        self::$opt_name = 'pc_wfc_' . self::$theme_name;

        //Are we in the customizr theme and is the modern style implemented in this version of the theme
        // in customizr-pro modern, will look like : pc_wfc_customizr-pro-modern
        if ( strpos( self::$theme_name, 'customizr' ) ) {
            if ( defined( 'CZR_IS_MODERN_STYLE' ) && CZR_IS_MODERN_STYLE ) {
                self::$opt_name = self::$opt_name . '-modern';
            } else {
                self::$opt_name = self::$opt_name . '-classical';
            }
        }

        //check if theme is customizr/hueman pro and plugin mode (did_action not triggered yet)
        if ( in_array( self::$theme_name, array( 'customizr-pro', 'hueman-pro' ) ) && self::$_is_plugin ) {
            $this->tc_deactivate_plugin();
            add_action( 'admin_notices', array( $this , 'presscustomizr_pro_admin_notice' ) );
            return;
        }


        /* die if addon mode and previewing a different theme */
        if ( ( ! in_array( self::$theme_name, array( 'customizr-pro', 'hueman-pro' ) ) ) && !self::$_is_plugin ) {
            return;
        }


        //Some actions to do on new install or update
        $plug_options = self::tc_get_plug_options();

        //USEFUL CONSTANTS
        if ( ! defined( 'TC_WFC_DIR_NAME' ) )      { define( 'TC_WFC_DIR_NAME' , basename( dirname( __FILE__ ) ) ); }


        if ( ! defined( 'TC_WFC_BASE_URL' ) ) {

            //plugin context
            if ( ! ( defined( 'TC_BASE_URL' ) || defined( 'HU_BASE_URL' ) ) ) {
                // 2 cases:
                // a) in hueman pro addons
                // b) standalone
                //case a)
                if ( method_exists( 'HU_AD',  'ha_is_pro_addons' ) && HU_AD()->ha_is_pro_addons() ) {
                    define( 'TC_WFC_BASE_URL' , HA_BASE_URL . 'addons/pro/' . TC_WFC_DIR_NAME );
                }
                //case b)
                else {
                    define( 'TC_WFC_BASE_URL' , plugins_url( TC_WFC_DIR_NAME ) );
                }
            } else { //addon context
                //a) in Customizr-PRO
                if ( defined( 'TC_BASE_URL' ) ) {
                    define( 'TC_WFC_BASE_URL' , sprintf('%s/%s' , TC_BASE_URL . 'addons' , TC_WFC_DIR_NAME ) );
                }
                //b) in Hueman-PRO
                else {
                    define( 'TC_WFC_BASE_URL' , sprintf('%s/%s' , HU_BASE_URL . 'addons/pro' , TC_WFC_DIR_NAME ) );
                }
            }
        }


        //adds plugin text domain
        add_action( 'plugins_loaded'                    , array( $this , 'tc_plugin_lang' ) );

        //Plugin mode only (Note for the future: get_stylesheet correctly returns the current stylesheet in the callbacks)
        //activation : delete the setting option
        register_activation_hook( __FILE__              , array( __CLASS__ , 'tc_wfc_clean_settings' ) );

        //check if Font Customizer WP.org is already install and enabled
        register_activation_hook( __FILE__              , array( __CLASS__   , 'tc_wfc_abort_if_font_customizer_enabled' ) );

        //add / register the following actions only in plugin context
        if ( ! did_action('plugins_loaded') ) {
              register_deactivation_hook( __FILE__            , array( __CLASS__ , 'tc_wfc_clean_settings' ) );

              //uninstall : clean database options
              register_uninstall_hook( __FILE__               , array( __CLASS__ , 'tc_wfc_clean_db' ) );
        }



        //LOAD FILES
        // 1) Loads utils now because TC_utils_wfc is used in hueman pro addons early to cache skope excluded settings
        // => @see HA_Skop_Option_Base::ha_cache_skope_excluded_settings() where we check if ( class_exists( 'TC_utils_wfc' ) )
        require_once ( dirname( __FILE__ ) . '/utils/classes/class_utils_wfc.php' );
        new TC_utils_wfc();

        //2) load other classes after setup theme, always, so that we have everything we need to know from the theme
        add_action( 'after_setup_theme'                       , array( $this, 'tc_wfc_load' ) );

        // Load the czr-base-fmk on after_setup_theme
        // check if not already loaded by another plugin, or the Customizr or Hueman theme first
        // The czr-base-fmk might be already loaded on 'after_setup_theme' priority 10 by a theme
        add_action( 'after_setup_theme'                       , array( $this, 'tc_wfc_load_czr_base_fmk' ), 50 );

        ////////////////////////////
        /// <NEW WFC >
        ////////////////////////////
        // Set the default models
        // => they will be used both server side on front and js browser side in the customizer
        // => must match the input declared in the customizer module template
        $this -> default_model = array_merge( array(
            //hidden properties
            'id'            => '',
            'title'         => '',
            'customized'    => array()
        ), $this -> tc_get_property_list() );

        // DEC 2017 => map old and new options + add entry "dec_2017_option_mapping_done" in plug option
        if ( isset( $_GET['wfc_debug'] ) || ( is_array( $plug_options ) && ! array_key_exists( 'dec_2017_option_mapping_done', $plug_options ) ) ) {
              $this -> do_dec_2017_option_mapping();
        }

        //plug_options is empty -> first install :
        // or
        //plug options is array, plug_version field exists and shows a different version than the current one
        //do:
        // => write versions
        // => store static variable : is_new_version
        if (  empty( $plug_options ) || ( is_array( $plug_options ) && isset( $plug_options[ 'tc_plugin_version' ] ) && 0 != version_compare( $plug_options[ 'tc_plugin_version' ], $this->plug_version ) ) ){
            // => writes versions
            self::tc_write_versions( $plug_options );
            // => store static variable : is_new_version
            self::$_is_new_version = true;
        }
    }//end of construct





    //hook : 'after_setup_theme'
    //load classes after setup theme, always, so that we have everything we need to know from the theme
    function tc_wfc_load() {
        //here would be where we can safely cache  the selectors (as property of this class ) getting rid of the transient and the selectors option
        $_activation_classes = array(
            'TC_activation_key'             => array('/back/classes/activation-key/activation/class_activation_key.php', array( $this -> plug_id, $this -> plug_name , $this -> plug_prefix , $this -> plug_version )),
            'TC_plug_updater'               => array('/back/classes/activation-key/updates/class_plug_updater.php'),
            'TC_check_updates'              => array('/back/classes/activation-key/updates/class_check_updates.php', array( $this -> plug_id, $this -> plug_name , $this -> plug_prefix , $this -> plug_version, $this -> plug_file ))

        );

        $_standalone_classes = array(
            'TC_back_system_info'       => array('/back/classes/class_back_system_info.php'),
        );


        $_plug_core_classes = array(
            //the admin notices
            //'TC_wfc_admin_notices'   => array('/back/classes/class_font_customizer_admin_notices.php', array( $this -> plug_name , $this -> plug_prefix )),
            //'TC_utils_wfc'                  => array('/utils/classes/class_utils_wfc.php'),
            'TC_admin_font_customizer'      => array('/back/classes/class_admin_font_customizer.php'),
            'TC_front_font_customizer'      => array('/front/classes/class_front_font_customizer.php'),
            'TC_dyn_style'                  => array('/front/classes/class_dyn_style.php'),

        );//end of plug_classes array

        $plug_classes       =  self::$_is_plugin ? array_merge($_activation_classes, $_standalone_classes, $_plug_core_classes) : $_plug_core_classes;


        //loads and instanciates the plugin classes
        foreach ($plug_classes as $name => $params) {

            //don't load admin classes if not admin && not customizing
            if ( is_admin() && ! $this -> is_customizing ) {

                if ( false != strpos($params[0], 'front') )
                        continue;
            }

            if ( ! is_admin() && ! $this -> is_customizing ) {

                if ( false != strpos($params[0], 'back') )
                        continue;

            }

            if( !class_exists( $name ) && file_exists( dirname( __FILE__ ) . $params[0] ) )
                require_once ( dirname( __FILE__ ) . $params[0] );

            if( class_exists( $name ) ) {

            $args = isset( $params[1] ) ? $params[1] : null;

                if ( $name !=  'TC_plug_updater' ) {
                    new $name( $args );
                }

            }

        }

    }//end load

    //hook : 'after_setup_theme'
    function tc_wfc_load_czr_base_fmk() {
        // Load the czr_base_fmk only if not loaded yet
        global $czr_base_fmk_namespace;
        if ( empty( $czr_base_fmk_namespace ) ) {
            require_once(  dirname( __FILE__ ) . '/back/czr-base-fmk/czr-base-fmk.php' );
            \wfc_czr_fmk\CZR_Fmk_Base( array(
               'text_domain' => 'wordpress_font_customizer',
               'base_url' => TC_WFC_BASE_URL . '/back/czr-base-fmk'
            ));
        }
    }



    /**
     * @uses  wp_get_theme() the optional stylesheet parameter value takes into account the possible preview of a theme different than the one activated
    *
    * @return  theme name string
    */
    function tc_get_theme_name() {

        // $_REQUEST['theme'] is set both in live preview and when we're customizing a non active theme
        $stylesheet = $this -> is_customizing && isset($_REQUEST['theme']) ? $_REQUEST['theme'] : ''; //old wp versions
        $stylesheet = $this -> is_customizing && isset($_REQUEST['customize_theme']) ? $_REQUEST['customize_theme'] : $stylesheet;

        //gets the theme name (or parent if child)
        $tc_theme               = wp_get_theme($stylesheet);
        $theme_name             = $tc_theme -> parent() ? $tc_theme -> parent() -> Name : $tc_theme-> Name;
        return sanitize_file_name( strtolower($theme_name) );

    }


    /**
     * Returns a boolean on the customizer's state
    *
    */
    function tc_is_customizing() {
        //checks if is customizing : two contexts, admin and front (preview frame)
        global $pagenow;
        $_is_ajaxing_from_customizer = isset( $_POST['customized'] ) || isset( $_POST['wp_customize'] );

        $is_customizing = false;
        // the check on $pagenow does NOT work on multisite install @see https://github.com/presscustomizr/nimble-builder/issues/240
        // That's why we also check with other global vars
        // @see wp-includes/theme.php, _wp_customize_include()
        $is_customize_php_page = ( is_admin() && 'customize.php' == basename( $_SERVER['PHP_SELF'] ) );
        $is_customize_admin_page_one = (
          $is_customize_php_page
          ||
          ( isset( $_REQUEST['wp_customize'] ) && 'on' == $_REQUEST['wp_customize'] )
          ||
          ( ! empty( $_GET['customize_changeset_uuid'] ) || ! empty( $_POST['customize_changeset_uuid'] ) )
        );
        $is_customize_admin_page_two = is_admin() && isset( $pagenow ) && 'customize.php' == $pagenow;

        if ( $is_customize_admin_page_one || $is_customize_admin_page_two ) {
            $is_customizing = true;
        //hu_is_customize_preview_frame() ?
        } else if ( is_customize_preview() || ( ! is_admin() && isset($_REQUEST['customize_messenger_channel']) ) ) {
            $is_customizing = true;
        // hu_doing_customizer_ajax()
        } else if ( $_is_ajaxing_from_customizer && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $is_customizing = true;
        }
        return $is_customizing;
    }



    //declares the translation domain
    function tc_plugin_lang() {
        //declares the plugin translation domain{
        load_plugin_textdomain( 'wordpress_font_customizer', false, basename( dirname( __FILE__ ) ) . '/lang' );
    }


    // IMPORTANT : The order and number of properties defined here MUST match with the static jcon config files for each theme, listing the predefined selectors
    // If this has to be changed, then the jsons must be updated.
    function tc_get_property_list() {
        //Default property list
        $tc_selector_properties     = array(
            'selector'        => '',
            'subset'          => null,
            'font-family'     => '',
            'font-weight'     => null,
            'font-style'      => null,
            'color'           => "#000000",
            'color-hover'     => "#000000",
            'font-size'       => 16,//"14px",
            'line-height'     => 24,//"20px",
            'text-align'      => null,
            'text-decoration' => null,
            'text-transform'  => null,
            'letter-spacing'  => 0,
            'static-effect'   => 'none',
            'important'       => false,
            'title'           => false
        );
        return apply_filters('tc_selector_properties' , $tc_selector_properties);
    }


    function _clean_selector_css( $_to_return ) {
        if ( ! is_array($_to_return) ) {
            $_to_return = html_entity_decode($_to_return);
        }
        else {
            foreach ( $_to_return as $selector => $data ) {
                    $_to_return[$selector]['selector'] = html_entity_decode($_to_return[$selector]['selector']);
            }
        }
        return $_to_return;
    }



    //This methods returns an array formed like :
    //Array
    // (
    //     [body] => Array
    //         (
    //             [selector] => body
    //             [subset] =>
    //             [font-family] => Helvetica Neue, Helvetica, Arial, sans-serif
    //             [font-weight] => normal
    //             [font-style] =>
    //             [color] => #5A5A5A
    //             [font-size] => 14px
    //             [line-height] => 20px
    //             [text-align] => inherit
    //             [text-decoration] => none
    //             [text-transform] => none
    //             [letter-spacing] => 0
    //             [static-effect] => none
    //             [important] =>
    //             [title] =>
    //         )

    //     [site_title] => Array
    //         (
    //             [selector] => .tc-header .brand .site-title
    //             [subset] =>
    //             [font-family] => Helvetica Neue, Helvetica, Arial, sans-serif
    //             [font-weight] => bold
    //             [font-style] =>
    //             [color] => main
    //             [color-hover] => main
    //             [font-size] => 40px
    //             [line-height] => 38px
    //             [text-align] => inherit
    //             [text-decoration] => none!important
    //             [text-transform] => none
    //             [letter-spacing] => 0
    //             [static-effect] => none
    //             [important] =>
    //             [title] =>
    //         )
    //
    // Until June 2017, the selector list was saved in a 24 hours transient
    // Now, it is always parsed from the json on first load, and stored as a property of this class.
    function tc_get_selector_list() {
        $theme_name = $json_theme_name = self::$theme_name;
        $path       = dirname( __FILE__).'/sets/';

        if ( defined( 'CZR_IS_MODERN_STYLE' ) && CZR_IS_MODERN_STYLE ) {
            $json_theme_name = $theme_name . '_modern';
        }

        //Did we already parse the json and store it ?
        if ( isset( self::$_selector_list ) && is_array( self::$_selector_list ) && ! empty( self::$_selector_list ) )
            return self::$_selector_list;

        $default_selector_settings       = file_exists("{$path}{$json_theme_name}.json") ? @file_get_contents( "{$path}{$json_theme_name}.json" ) : @file_get_contents( "{$path}default.json" );
        if ( $default_selector_settings === false ) {
            $default_selector_settings = ! wp_remote_fopen( sprintf( "%s/sets/{$json_theme_name}.json" , TC_WFC_BASE_URL ) ) ? wp_remote_fopen( sprintf( "%s/sets/default.json" , TC_WFC_BASE_URL ) ) : wp_remote_fopen( sprintf( "%s/sets/{$json_theme_name}.json" , TC_WFC_BASE_URL ) );
        }

        $default_selector_settings    = json_decode( $default_selector_settings , true );
        $default_selector_settings    = isset( $default_selector_settings['default'] ) ? $default_selector_settings['default'] : $default_selector_settings;

        // $property_list                = $this -> tc_get_property_list();
        // $property_list_keys           = array_keys($property_list);

        //<@new_wfc>
        // Get the selector titles declared in utils
        // array(
        //  'body'          => __( 'Default website font' , 'wordpress_font_customizer'),
        //  'site_title'    => __( 'Site title' , 'wordpress_font_customizer'),
        //  ...
        // )

        $selector_title_map = TC_utils_wfc::$instance -> tc_get_selector_title_map();
        //</@new_wfc>

        $selector_list = array();
        $property_list = $this -> tc_get_property_list();

        foreach ( $default_selector_settings as $id => $selector ) {
            $selector_list[ $id ] = $property_list;
            $selector_list[ $id ][ 'id' ] = $id;
            $selector_list[ $id ][ 'title' ] = ( is_array( $selector_title_map ) && array_key_exists( $id, $selector_title_map ) ) ? $selector_title_map[ $id ] : '';
            $selector_list[ $id ][ 'selector' ] = $selector;
        }


        // _clean_selector_css uses html_entity_decode for selector => fixes characters (unrecognized expression) issue in javascript
        $_to_return  = apply_filters("tc_default_selectors_{$theme_name}" , $selector_list);
        $_to_return  = $this -> _clean_selector_css( $_to_return );

        //Let's cache it
        self::$_selector_list = $_to_return;

        return $_to_return;
    }


    function tc_get_saved_option() {
        $saved_options = get_option( TC_wfc::$opt_name );
        if ( empty( $saved_options ) )
            return array();

        $_to_return = array();
        foreach( $saved_options as $selector => $settings ) {
            if ( is_string( $settings ) ) {
                $_to_return[ $selector ] = (array)json_decode($saved_options[$selector]);
            } else {
               $_to_return[ $selector ] = $settings;
            }
        }
        return $_to_return;
    }




    //@todo : include custom selector options ?
    public static function tc_wfc_clean_db() {
        $theme_name = self::$theme_name;

        //OPTIONS
        $options = array(
              "tc_font_customizer_last_modified",
              "tc_font_customizer_plug",
              "tc_font_customizer_selectors_{$theme_name}"
        );
        foreach ($options as $value) {
              delete_option($value);
        }

        //TRANSIENT
        delete_transient('tc_gfonts');
        delete_transient('czr_gfonts_nov_2018');
    }


    public static function tc_wfc_clean_settings() {
        $theme_name = self::$theme_name;
        delete_option("tc_font_customizer_selectors_{$theme_name}");
    }

    //write current and previous version => used for system infos
    public static function tc_write_versions( $plug_options = null ) {
        //Gets options
        $plug_options = ! is_array( $plug_options ) ? self::$tc_get_plug_options() : $plug_options;

        //Adds Upgraded From Option
        if ( isset($plug_options['tc_plugin_version']) ) {
              $plug_options['tc_upgraded_from'] = $plug_options['tc_plugin_version'];
        }
        //Sets new version
        $plug_options['tc_plugin_version'] = TC_wfc::$instance -> plug_version;
        //Updates
        update_option( TC_wfc::$instance -> plug_option_prefix , $plug_options );
    }


    //get current version
    public static function tc_get_plug_options() {

        //Gets options
        return get_option(TC_wfc::$instance -> plug_option_prefix) ? get_option(TC_wfc::$instance -> plug_option_prefix) : array();

    }


     // in register_activation_hook( __FILE__              , array( __CLASS__   , 'tc_wfc_abort_if_font_customizer_enabled' ) );
    public static function tc_wfc_abort_if_font_customizer_enabled() {
        if ( class_exists('TC_font_customizer') )
            //add_action( 'admin_notices', array(TC_wfc::$instance , 'my_admin_notice' ));
            wp_die( sprintf('The <strong>Font Customizer</strong> plugin has to be disabled before enabling the WordPress Font Customizer.</br><a href="%1$s">&laquo; Back to plugin\'s page</a>' , admin_url() . 'plugins.php' ) );

    }


    function tc_deactivate_plugin() {
        //In front: requires a page refresh or whatever navigation to occurr to actually see the disabling effect
        if ( ! function_exists( 'deactivate_plugins' ) ) {
          include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
          deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

  //hook : 'admin_notices'
    function presscustomizr_pro_admin_notice() {
        ?>
        <div class="error">
            <p>
              <?php
              printf( __( 'The <strong>%s</strong> plugin has been disabled since it is included in this theme.' , 'tc_unlimited_fp' ),
                $this -> plug_name
              );
              ?>
            </p>
        </div>
        <?php
    }


  // Before dec 2017 option structure
  // array (
  //     [body] => {"font-family":"[gfont]Alegreya+Sans:italic","font-weight":"400","font-style":"italic"}
  //     [single_page_title] => {"font-family":"[gfont]Alegreya+Sans:300","font-weight":"300","font-style":"normal"}
  //     [footer_credits] => {"font-size":"15px","font-family":"[gfont]Baumans:regular","font-weight":"400","font-style":"normal"}
  // )
  //
  // After dec 2017
  //  (
  //         [id] => body
  //         [title] => Default website font
  //         [customized] => Array
  //             (
  //                 [0] => subset
  //                 [1] => font-family
  //                 [2] => font-weight
  //                 [3] => font-size
  //                 [4] => selector
  //             )

  //         [selector] => body
  //         [subset] => all-subsets
  //         [font-family] => [gfont]Alegreya+Sans:300
  //         [font-weight] => 400
  //         [font-style] =>
  //         [color] => #000000
  //         [color-hover] => #000000
  //         [font-size] => 16
  //         [line-height] => 24
  //         [text-align] => inherit
  //         [text-decoration] => none
  //         [text-transform] => none
  //         [letter-spacing] => 0
  //         [static-effect] => none
  //         [important] =>
  //     )

    // [1] => Array()
    // ...
    //
    function do_dec_2017_option_mapping() {
        $raw_before_dec_2017_options = get_option( 'tc_font_customizer_plug' );

        // error_log('<predefined_selectors_list>');
        // error_log( print_r($this -> tc_get_selector_list(), true ) );
        // error_log('</predefined_selectors_list>');

        // error_log('<raw_before_dec_2017_options>');
        // error_log( print_r($raw_before_dec_2017_options, true ) );
        // error_log('</raw_before_dec_2017_options>');

        $theme_name   = self::$theme_name;
        $_opt_prefix  = $this -> plug_option_prefix;
        $raw_before_dec_2017_custom_selector_options = get_option("{$_opt_prefix}_customs_{$theme_name}");

        // error_log('<raw_before_dec_2017_custom_selector_options>');
        // error_log( print_r($raw_before_dec_2017_custom_selector_options, true ) );
        // error_log('</raw_before_dec_2017_custom_selector_options>');

        // Stop here if there are no previous option to map ?
        if ( ! is_array( $raw_before_dec_2017_options ) || empty( $raw_before_dec_2017_options ) )
            return;

        // Pre-process the custom selector options, if any
        $before_dec_2017_custom_selector_options = array();

        if ( is_array( $raw_before_dec_2017_custom_selector_options ) ) {
            // make sure we json_decode the $before_dec_2017_custom_selector_options
            foreach( $raw_before_dec_2017_custom_selector_options as $item_id => $maybe_json_data ) {
                //Skip if the id is not well formed
                if ( ! is_string( $item_id ) || empty( $item_id ) )
                    continue;

                // normalize data
                $data = $maybe_json_data;
                if ( is_string( $maybe_json_data ) ) {
                    $data = (array)json_decode( $maybe_json_data );
                }
                //shall we continue ?
                if ( ! is_array( $data ) || empty( $data ) )
                    continue;

                //populate
                $before_dec_2017_custom_selector_options[$item_id] = $data;
            }
        }

        $new_options = array();

        // Populates the new options
        foreach( $raw_before_dec_2017_options as $item_id => $maybe_json_data ) {
            //Skip if the id is not well formed
            if ( ! is_string( $item_id ) || empty( $item_id ) )
                continue;

            // normalize data
            $data = $maybe_json_data;
            if ( is_string( $maybe_json_data ) ) {
                $data = (array)json_decode( $maybe_json_data );
            }
            //shall we continue ?
            if ( ! is_array( $data ) || empty( $data ) )
                continue;

            // populate the new options
            $new_options[] = $this -> _write_item_properties( $item_id, $data, $before_dec_2017_custom_selector_options );
        }//foreach

        // write the new options
        update_option( TC_wfc::$opt_name, $new_options );

        // FLAG
        // Gets options
        $plug_options = self::tc_get_plug_options();
        $plug_options = is_array( $plug_options ) ? $plug_options : array();

        // error_log('<OLD OPTIONS>');
        // error_log( print_r($plug_options, true ) );
        // error_log('</OLD OPTIONS>');

        // Add mapped flag
        $plug_options['dec_2017_option_mapping_done'] = true;

        // Update
        update_option( TC_wfc::$instance -> plug_option_prefix , $plug_options );

        // error_log('<MAPPED OPTIONS>');
        // error_log( print_r($new_options, true ) );
        // error_log('</MAPPED OPTIONS>');
    }


    function _write_item_properties( $item_id, $data, $before_dec_2017_custom_selector_options ) {

        $predefined_selectors_list = $this -> tc_get_selector_list(); //<= user added custom selectors are included in this list

        $default_model = $this -> default_model;
        $item_candidate = $default_model;

        foreach ( $data as $property => $value ) {
            //only writes the authorized properties, "zones", "not, "icon" are not part of the model anymore
            if ( ! array_key_exists( $property, $default_model ) )
                continue;
            // Assign the previous value by default
            $item_candidate[ $property ] = $value;

            // populates the "customized" property list
            $item_candidate[ 'customized' ][] = $property;

            // use the current item id as id and grab the title
            $item_candidate[ 'id' ] = $item_id;

            //specific treatments
            switch( $property ) {
                case 'font-size' :
                case 'line-height' :
                case 'letter-spacing' :
                    // remove 'px' or 'em', and keep only the numbers
                    if ( is_string( $value ) ) {
                        $value = preg_replace('/[^0-9]/','', $value );
                    }
                    //if we still don't have a valid value at this point, let's fall back on the default model
                    if ( empty( $value ) || ! is_numeric( floatval( $value ) ) ) {
                        $value = array_key_exists( $property, $default_model ) ? $default_model[ $property ] : 0;
                    }
                    // set it
                    $item_candidate[ $property ] = $value;
                break;
            }

            // if we have a match in the pre-defined list, let's grab from this entry :
            // 1) the title
            // 2) the selector
            $selector_candidate = array_key_exists( 'selector', $data ) ? $data['selector'] : '';
            $title_candidate = $item_id;
            if ( array_key_exists( $item_id, $predefined_selectors_list ) ) {
                if ( isset( $predefined_selectors_list[$item_id]['title'] ) ) {
                    $title_candidate = $predefined_selectors_list[$item_id]['title'];
                } else {
                    $title_candidate = $item_id;
                }

                if ( isset( $predefined_selectors_list[$item_id]['selector'] ) ) {
                    $selector_candidate = $predefined_selectors_list[$item_id]['selector'];
                }
            } else {
                $title_candidate = __( 'Custom', 'wordpress_font_customizer' );
                // if this is a custom selector, the css selector is stored in the $before_dec_2017_custom_selector_options
                if ( array_key_exists( $item_id, $before_dec_2017_custom_selector_options ) && array_key_exists( 'selector', $before_dec_2017_custom_selector_options[$item_id] ) ) {
                    $selector_candidate = $before_dec_2017_custom_selector_options[$item_id]['selector'];
                }
            }

            $item_candidate[ 'title' ] = $title_candidate;
            $item_candidate[ 'selector' ] = $selector_candidate;
        }//foreach

        return $item_candidate;
    }//_write_item_properties()
}//end of class

//Creates a new instance of front and admin
new TC_wfc;

endif;