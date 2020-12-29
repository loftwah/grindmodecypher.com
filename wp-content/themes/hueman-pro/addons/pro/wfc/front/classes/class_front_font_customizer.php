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
    public $user_effects = 'not_set';

    function __construct () {
        //add_action( 'init'                              , array( $this, 'tc_add_style' ) );
        add_action( 'init'                              , array( $this , 'tc_maybe_enqueue_effect_stylesheet' ) , 0 );
        add_action( 'wp_head'                              , array( $this , 'tc_print_wfc_style' ) , 0 );

        add_action( 'wp_head'                           , array( $this , 'tc_write_gfonts'), 0 );
        add_action( 'wp_head'                           , array( $this , 'tc_write_font_dynstyle'), 0 );
        add_action( 'wp_head'                           , array( $this , 'tc_write_other_dynstyle'), 999 );

        add_action( 'wp_footer'                           , array( $this , 'tc_print_local_scripts') );

        //$this -> is_customizing = isset($_REQUEST['wp_customize']) ? 1 : 0;
    }//end of construct

    // @hook 'init'
    function tc_maybe_enqueue_effect_stylesheet() {
        // if effect(s) are used, load an additional stylesheet
        // always enqueue when customizing
        $user_effects = $this->get_effect_user_settings_localized_data_js();
        if ( TC_wfc::$instance -> tc_is_customizing() || ( is_array($user_effects) && !empty($user_effects) ) ) {
            wp_enqueue_style(
              'font-customizer-effects' ,
              sprintf('%1$s/front/assets/css/font_customizer%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
              array(),
              ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
              $media = 'all'
            );
        }
    }

    // hook : wp_head:0
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

    // hook : wp_head:0
    // When not customizing write the font very early in a separate stylesheet
    function tc_write_font_dynstyle() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        if ( !TC_wfc::$instance -> tc_is_customizing() )
            do_action( '__dyn_style' , 'fonts' );
    }

    // hook : wp_head;0
    function tc_write_other_dynstyle() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        do_action( '__dyn_style' , 'other' );
    }


     // @wp_head
    function tc_print_wfc_style() {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;
        // Nov 2020 => base css rules are now written inline ( see front/assets/css/font_customizer.css for comments about those rules )
        // and rules for effect are enqueued only when needed in front/assets/css/font_customizer.css
        ?>
        <style id="wfc-base-style" type="text/css">
             .wfc-reset-menu-item-first-letter .navbar .nav>li>a:first-letter {font-size: inherit;}.format-icon:before {color: #5A5A5A;}article .format-icon.tc-hide-icon:before, .safari article.format-video .format-icon.tc-hide-icon:before, .chrome article.format-video .format-icon.tc-hide-icon:before, .safari article.format-image .format-icon.tc-hide-icon:before, .chrome article.format-image .format-icon.tc-hide-icon:before, .safari article.format-gallery .format-icon.tc-hide-icon:before, .safari article.attachment .format-icon.tc-hide-icon:before, .chrome article.format-gallery .format-icon.tc-hide-icon:before, .chrome article.attachment .format-icon.tc-hide-icon:before {content: none!important;}h2#tc-comment-title.tc-hide-icon:before {content: none!important;}.archive .archive-header h1.format-icon.tc-hide-icon:before {content: none!important;}.tc-sidebar h3.widget-title.tc-hide-icon:before {content: none!important;}.footer-widgets h3.widget-title.tc-hide-icon:before {content: none!important;}.tc-hide-icon i, i.tc-hide-icon {display: none !important;}.carousel-control {font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;}.social-block a {font-size: 18px;}footer#footer .colophon .social-block a {font-size: 16px;}.social-block.widget_social a {font-size: 14px;}
        </style>
        <?php
    }

    // @wp_footer
    // replaces wp_localize because we don't need to indicate a dependency to any scripts for local data
    function tc_print_local_scripts() {
        $wfc_params = array(
            'effectsAndIconsSelectorCandidates' => $this -> get_effect_user_settings_localized_data_js(),
            'wfcOptions' => $this -> get_debug_options()
        );

        foreach ( (array) $wfc_params as $key => $value ) {
            if ( !is_scalar( $value ) ) {
              continue;
            }
            $wfc_params[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
        }

        printf('<script id="wfc-front-localized">%1$s</script>', "var wfcFrontParams = " . wp_json_encode( $wfc_params ) . ';' );
        ?>
          <script id="wfc-front-script">!function(){function a(){var a,b,c,d={};return a=navigator.userAgent.toLowerCase(),b=/(chrome)[ /]([\w.]+)/.exec(a)||/(webkit)[ /]([\w.]+)/.exec(a)||/(opera)(?:.*version|)[ /]([\w.]+)/.exec(a)||/(msie) ([\w.]+)/.exec(a)||a.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(a)||[],c={browser:b[1]||"",version:b[2]||"0"},c.browser&&(d[c.browser]=!0,d.version=c.version),d.chrome?d.webkit=!0:d.webkit&&(d.safari=!0),d}var b=wfcFrontParams.effectsAndIconsSelectorCandidates,c=a(),d="",e=0;for(var f in c)e>0||(d=f,e++);var g=document.querySelectorAll("body");g&&g[0]&&g[0].classList.add(d||"");for(var h in b){var i=b[h];if(i.static_effect){if("inset"==i.static_effect&&!0===c.mozilla)continue;var j=document.querySelectorAll(i.static_effect_selector);j&&j[0]&&j[0].classList.add("font-effect-"+i.static_effect)}}}();</script>
        <?php
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
        if ( 'not_set' !== $this->user_effects ) {
            return $this->user_effects;
        }

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
        $this->user_effects = $candidates;
        return $this->user_effects;
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
