<?php
/**
* Defines the Hueman theme settings map
* On live context, used to generate the default option values
*
*
* @package      Hueman
* @since        3.0.0
* @author       Nicolas GUILLAUME <nicolas@presscustomizr.com>
* @copyright    Copyright (c) 2016, Nicolas GUILLAUME
* @link         http://presscustomizr.com/hueman
* @license      http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if ( !class_exists( 'HU_utils_settings_map' ) ) :
  class HU_utils_settings_map {
    static $instance;
    private $is_wp_version_before_4_0;
    public $customizer_map = array();

    function __construct () {
      self::$instance =& $this;
      //declare a private property to check wp version >= 4.0
      global $wp_version;
      $this -> is_wp_version_before_4_0 = ( !version_compare( $wp_version, '4.0', '>=' ) ) ? true : false;
    }//end of construct



    /**
    * Defines sections, settings and function of customizer and return and array
    * Also used to get the default options array, in this case $get_default = true and we DISABLE the __get_option (=>infinite loop)
    *
    * @package Hueman
    * @since Hueman 3.0
    */
    public function hu_get_customizer_map( $get_default = null, $what = null ) {
      if ( !empty( $this -> customizer_map ) ) {
        $_customizr_map = $this -> customizer_map;
      }
      else {
        //POPULATE THE MAP WITH DEFAULT HUEMAN SETTINGS
        add_filter( 'hu_add_panel_map'        , array( $this, 'hu_popul_panels_map'));
        add_filter( 'hu_remove_section_map'   , array( $this, 'hu_popul_remove_section_map'));
        //theme switcher's enabled when user opened the customizer from the theme's page
        //add_filter( 'hu_remove_section_map'   , array( $this, 'hu_set_theme_switcher_visibility'));
        add_filter( 'hu_add_section_map'      , array( $this, 'hu_popul_section_map' ));
        //add controls to the map
        add_filter( 'hu_add_setting_control_map' , array( $this , 'hu_popul_setting_control_map' ), 10, 2 );
        //$this -> hu_populate_setting_control_map();

        //CACHE THE GLOBAL CUSTOMIZER MAP
        $_customizr_map = array_merge(
            array( 'add_panel'           => apply_filters( 'hu_add_panel_map', array() ) ),
            array( 'remove_section'      => apply_filters( 'hu_remove_section_map', array() ) ),
            array( 'add_section'         => apply_filters( 'hu_add_section_map', array() ) ),
            array( 'add_setting_control' => apply_filters( 'hu_add_setting_control_map', array(), $get_default ) )
        );
        $this -> customizer_map = $_customizr_map;
      }
      if ( is_null($what) )
        return apply_filters( 'hu_customizer_map', $_customizr_map );
      else {
        $_to_return = $_customizr_map;
        switch ( $what ) {
            case 'add_panel':
              $_to_return = $_customizr_map['add_panel'];
            break;
            case 'remove_section':
              $_to_return = $_customizr_map['remove_section'];
            break;
            case 'add_section':
              $_to_return = $_customizr_map['add_section'];
            break;
            case 'add_setting_control':
              $_to_return = $_customizr_map['add_setting_control'];
            break;
        }
        return $_to_return;
      }
    }



    /**
    * Populate the control map
    * hook : 'hu_add_setting_control_map'
    * => loops on a callback list, each callback is a section setting group
    * @return array()
    *
    * @package Hueman
    * @since Hueman 3.3+
    */
    function hu_popul_setting_control_map( $_map, $get_default = null ) {
      $_new_map = array();
      $_settings_sections = array(
          //GENERAL
          'hu_site_identity_sec',
          'hu_general_design_sec',
          'hu_comments_sec',
          //'hu_smoothscroll_sec',//<=Removed in march 2020
          'hu_mobiles_sec',
          'hu_search_sec',
          'hu_social_links_sec',
          'hu_performance_sec',
          'hu_admin_sec',

          //HEADER
          'hu_header_design_sec',
          'hu_header_image_sec',
          'hu_header_widget_sec',
          'hu_header_menus_sec',

          //CONTENT
          //'hu_content_home_sec',
          'hu_content_blog_sec',
          'hu_content_page_sec',
          'hu_content_single_sec',
          'hu_content_thumbnail_sec',
          'hu_content_layout_sec',
          'hu_sidebars_design_sec',

          //FOOTER
          'hu_footer_design_sec'
      );

      foreach ( $_settings_sections as $_section_cb ) {
          if ( !method_exists( $this , $_section_cb ) )
            continue;
          //applies a filter to each section settings map => allows plugins (hueman addons for ex.) to add/remove settings
          //each section map takes one boolean param : $get_default
          $_section_map = apply_filters(
            $_section_cb,
            call_user_func_array( array( $this, $_section_cb ), array( $get_default ) )
          );

          if ( !is_array( $_section_map) )
            continue;

          $_new_map = array_merge( $_new_map, $_section_map );
      }//foreach

      return array_merge( $_map, $_new_map );
    }


    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : GENERAL
    *******************************************************************************************************
    ******************************************************************************************************/

    /*-----------------------------------------------------------------------------------------------------
                                   SITE IDENTITY
    ------------------------------------------------------------------------------------------------------*/
    //the title_tagline section holds the default WP setting for the Site Title and the Tagline
    //This section has been previously removed from its initial location and added back in the General Settings panel
    //Important Note :
    //IF WP VERSION >= 4.3 AND SITE_ICON SETTING EXISTS
    //=> The following FAV ICON CONTROL is removed (@see class-czr-init.php)
    function hu_site_identity_sec() {
      global $wp_version;
      return array(
          'favicon'  => array(
                'control'   =>  'HU_Customize_Upload_Control' ,
                'label'     =>  __( 'Favicon Upload (supported formats : .ico, .png, .gif)' , 'hueman-pro' ),
                'title'     => __( 'FAVICON' , 'hueman-pro'),
                'section'   => 'title_tagline',//<= this is a default WP section, not created for the Hueman theme
                'type'      => 'czr_upload',
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'skoped' => false
          ),
          'display-header-title' => array(
                'default'   => 1,
                'priority'  => 4,
                'control'   => 'HU_controls',
                'label'     => __( 'Display the site title in the header' , 'hueman-pro' ),
                'section'   => 'title_tagline',
                'type'      => 'nimblecheck',
                'notice'    => __( 'The site title is displayed when there is no logo uploaded', 'hueman-pro' ),
                'ubq_section'   => array(
                    'section' => 'header_design_sec',
                    'priority' => '0'
                )
          ),
          'display-header-logo' => array(
                'default'   => 0,
                'priority'  => 5,
                'control'   => 'HU_controls',
                'label'     => __( 'Display a logo in the header' , 'hueman-pro' ),
                'section'   => 'title_tagline',
                'type'      => 'nimblecheck',
                'notice'    => sprintf( '%3$s <strong><a href="%1$s" title="%3$s">%2$s</a><strong>',
                    "javascript:wp.customize.section('title_tagline').focus();",
                    __("here" , "hueman-pro"),
                    __("Set your logo below or", "hueman-pro")
                ),
                'ubq_section'   => array(
                    'section' => 'header_design_sec',
                    'priority' => '0'
                )
          ),
          'mobile-header-logo'  => array(
                'control'   =>  version_compare( $wp_version, '4.3', '>=' ) ? 'HU_Customize_Cropped_Image_Control' : 'HU_Customize_Upload_Control',
                'label'     =>  __( 'Use a specific logo for mobile devices' , 'hueman-pro' ),
                'title'     => __( 'Logo for mobiles', 'hueman-pro' ),
                'section'   => 'title_tagline' ,
                'priority'  => 50,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                //we can define suggested cropping area and allow it to be flexible (def 150x150 and not flexible)
                'width'     => 120,
                'height'    => 45,
                'flex_width' => true,
                'flex_height' => true,
                //to keep the selected cropped size
                'dst_width'  => false,
                'dst_height'  => false,
                'notice'    => __('Upload your custom logo image. Supported formats : .jpg, .png, .gif, svg, svgz' , 'hueman-pro')
          ),
          'logo-max-height'  =>  array(
                'default'       => 60,
                'priority'      => 7,
                'control'       => 'HU_controls' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'         => sprintf( '%1$s : %2$s %3$s' , __('Desktop devices', 'hueman-pro' ), __( "Header Logo Image Max-height" , 'hueman-pro' ) , __('(in pixels)', 'hueman-pro') ),
                'section'       => 'title_tagline',
                'type'          => 'number' ,
                'step'          => 1,
                'min'           => 20,
                'transport'     => 'postMessage'
          ),
          // added january 2020 for https://github.com/presscustomizr/hueman/issues/844
          'wrap_in_h_one' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('On home page wrap the site title or logo in an H1 tag', 'hueman-pro'),
                'section'   => 'title_tagline',
                'type'      => 'nimblecheck',
                'notice'    => __( 'For best SEO results, make sure your pages always include at least one H1 tag.' , 'hueman-pro' ),
                'ubq_section'   => array(
                    'section' => 'header_design_sec',
                    'priority' => '0'
                )
          ),
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   GLOBAL DESIGN OPTIONS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_general_design_sec( $get_default = null ) {
      return array(
          'font' => array(
                'default'   => 'titillium-web',
                'control'   => 'HU_controls',
                'label'     => __('Font', 'hueman-pro'),
                'section'   => 'general_design_sec',
                'type'      => 'select',
                'choices'    => hu_get_fonts( array( 'all' => true, 'request' => 'title' ) ),
                'transport'     => 'postMessage',
                'notice'    => __( 'Select a font for your website' , 'hueman-pro' )
          ),
          'body-font-size'      => array(
                'default'       => 16,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'         => __( 'Set your website default font size in pixels.' , 'hueman-pro' ),
                'control'       => 'HU_controls',
                'section'       => 'general_design_sec',
                'type'          => 'number' ,
                'step'          => 1,
                'min'           => 0,
                'transport'     => 'postMessage',
                'notice'        => __( "This option sets the default font size applied to any text element of your website, when no font size is already applied." , 'hueman-pro' )
          ),
          'container-width'  =>  array(
                'default'       => 1380,
                'control'       => 'HU_controls' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'         => __( "Website Max-width" , 'hueman-pro' ),
                'section'       => 'general_design_sec' ,
                'type'          => 'number' ,
                'step'          => 1,
                'min'           => 1024,
                //'transport'     => 'postMessage',
                'notice'        => __('Max-width of the container. If you use 2 sidebars, your container should be at least 1200px.<br /><i>Note: For 720px content (default) use <strong>1380px</strong> for 2 sidebars and <strong>1120px</strong> for 1 sidebar. If you use a combination of both, try something inbetween.</i>', 'hueman-pro')//@todo sprintf and split translations
          ),
          'boxed' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __('Boxed Layout', 'hueman-pro'),
                'section'   => 'general_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Use a boxed layout' , 'hueman-pro' )
          ),
          'sidebar-padding' => array(
                'default'   => '30',
                'control'   => 'HU_controls',
                'label'     => __("Sidebar Width", 'hueman-pro'),
                'section'   => 'general_design_sec',
                'type'      => 'select',//@todo create a radio type
                'choices' => array(
                  '30'          => __( '30px padding for widgets' , 'hueman-pro' ),
                  '20'          => __( '20px padding for widgets' , 'hueman-pro' ),
                ),
                'notice'    => __( 'Change left and right sidebars padding' , 'hueman-pro'),
                'ubq_section'   => array(
                    'section' => 'sidebars_design_sec',
                    'priority' => '50'
                )
          ),
          'color-1' => array(
                'default'     => hu_user_started_before_version( '3.3.8' ) ? '#3b8dbd' : '#16cfc1',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Primary Color' , 'hueman-pro' ),
                'section'     => 'general_design_sec',
                'type'        =>  'wp_color_alpha' ,
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color'
                //'transport'   => 'postMessage'
          ),
          'color-2' => array(
                'default'     =>  hu_user_started_before_version( '3.3.8' ) ? '#82b965' : '#efb93f',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Secondary Color' , 'hueman-pro' ),
                'section'     => 'general_design_sec',
                'type'        =>  'wp_color_alpha' ,
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color'
                //'transport'   => 'postMessage'
          ),
          // Since June 2018, this setting is registered dynamically
          // We leave it in the map only for building the default options
          'body-background' => array(
                'registered_dynamically' => true,
                //'default'     => array(),
                'default'       => array( 'background-color' => '#eaeaea' ),
                'control'     => 'HU_Customize_Modules',
                'label'       => __( 'Body Background' , 'hueman-pro' ),
                'description' => __('Set the website background color', 'hueman-pro'),
                'section'     => 'general_design_sec',
                'type'        => 'czr_module',
                'module_type' => 'czr_background'
                //'type'        => 'color',
                // 'sanitize_callback'    => array( $this, 'hu_sanitize_body_bg' ),@todo
                // 'sanitize_js_callback' => array( $this, 'hu_sanitize_js_body_bg' ),@todo
                //'transport'   => 'postMessage',
                //'notice'        => __('Set background color and/or upload your own background image.', 'hueman')
          ),
          'image-border-radius'  =>  array(
                'default'       => 0,
                'control'       => 'HU_controls' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'         => __( "Image Border Radius" , 'hueman-pro' ),
                'section'       => 'general_design_sec' ,
                'type'          => 'number' ,
                'step'          => 1,
                'min'           => 0,
                //'transport'     => 'postMessage',
                'notice'        => __('Give your thumbnails and layout images rounded corners', 'hueman-pro')
          ),
          'links_underlined'  =>  array(
                'default'       => 1,
                'control'       => 'HU_controls' ,
                'label'         => __( "Links underlined within content" , "hueman-pro" ),
                'section'       => 'general_design_sec' ,
                'type'          => 'nimblecheck'
          ),
          'ext_link_style'  =>  array(
                'default'       => 0,
                'control'       => 'HU_controls' ,
                'label'         => __( "Display an icon next to external links" , "hueman-pro" ),
                'section'       => 'general_design_sec' ,
                'type'          => 'nimblecheck' ,
                'notice'    => __( 'This will be applied to the links included in post or page content only.' , 'hueman-pro' ),
                //'transport'     => 'postMessage'
          ),

          'ext_link_target'  =>  array(
                'default'       => 0,
                'control'       => 'HU_controls' ,
                'label'         => __( "Open external links in a new tab" , "hueman-pro" ),
                'section'       => 'general_design_sec' ,
                'type'          => 'nimblecheck' ,
                'notice'    => __( 'This will be applied to the links included in post or page content only.' , 'hueman-pro' ),
                //'transport'     => 'postMessage'
          )
      );
    }


    /*-----------------------------------------------------------------------------------------------------
                                   SOCIAL LINKS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_social_links_sec() {
      return array(
          // 'social-links' => array(
          //       'default'   => array(),
          //       'control'   => 'HU_controls',
          //       'label'     => __('Responsive Layout', 'hueman'),
          //       'description' => __('Create and organize your social links' , 'hueman'),
          //       'section'   => 'social_links_sec',
          //       'type'      => 'dynamic'//@todo create dynamic type
          // )
          // Since June 2018, this setting is registered dynamically
          // We leave it in the map only for building the default options
          'social-links' => array(
                'registered_dynamically' => true,
                'default'   => array(),//empty array by default
                'control'   => 'HU_Customize_Modules',
                'label'     => __('Create and organize your social links', 'hueman-pro'),
                'section'   => 'social_links_sec',
                'type'      => 'czr_module',
                'module_type' => 'czr_social_module',
                'transport' => hu_is_partial_refreshed_on() ? 'postMessage' : 'refresh',
                'priority'  => 10,
                'skoped' => false,
          )
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   COMMENTS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_comments_sec() {
      return array(
          'post-comments' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Posts Comments', 'hueman-pro'),
                'section'   => 'comments_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Comments on posts' , 'hueman-pro' ),
                //'active_callback' => 'hu_is_single'
          ),
          'page-comments' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __('Pages Comments', 'hueman-pro'),
                'section'   => 'comments_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Comments on pages' , 'hueman-pro' ),
                //'active_callback' => 'hu_is_page'
          )
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   SMOOTH SCROLL SECTION
    ------------------------------------------------------------------------------------------------------*/
    // Removed in march 2020
    // function hu_smoothscroll_sec() {
    //   return array(
    //       'smoothscroll' => array(
    //             'default'   => 1,
    //             'control'   => 'HU_controls',
    //             'label'     => __('Enable Smooth Scrolling', 'hueman'),
    //             'section'   => 'smoothscroll_sec',
    //             'type'      => 'nimblecheck',
    //             'notice'    => __( "This option enables a smoother page scroll." , 'hueman' )

    //       )
    //   );
    // }


    /*-----------------------------------------------------------------------------------------------------
                                   MOBILE DEVICES SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_mobiles_sec() {
      return array(
          'responsive' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Enable the Mobile Friendly (or Responsive) layout', 'hueman-pro'),
                'section'   => 'mobiles_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( "Hueman is a mobile friendly WordPress theme out of the box. This means that it will adapt and render nicely on any devices : desktops, laptops, tablets, smartphones. <br/>If you uncheck this box, this adaptive (or reponsive) behaviour will not be working anymore. In most of the cases, you won't need to disable this option, and it is not recommended." , 'hueman-pro' )
          ),
          'fittext' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __('Make font sizes flexible. Enable this option to achieve scalable headlines that fill the width of a parent element.', 'hueman-pro'),
                'section'   => 'mobiles_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( "This option is good if you want to display a perfect font-size for your headings on any mobile devices. Note : it might override the css rules previously set in your custom stylesheet." , 'hueman-pro' )
          ),
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   SEARCH RESULTS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_search_sec() {
      return array(
          'attachments-in-search' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __('Include images in search results', 'hueman-pro'),
                'section'   => 'search_sec',
                'type'      => 'nimblecheck'
          )
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   PERFORMANCE SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_performance_sec() {
      return array(
          'minified-css' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Use a minified stylesheet', 'hueman-pro'),
                'section'   => 'performance_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( "Unchecking this option is not recommended. Minifying css stylesheets improves performance for your website overall by decreasing the load time." , 'hueman-pro' )
          ),
          'structured-data' => array(
                'default'   => hu_user_started_before_version( '3.1.1' ) ? 0 : 1,
                'control'   => 'HU_controls',
                'label'     => __('Use Structured Data Markup for your posts', 'hueman-pro'),
                'section'   => 'performance_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( '"Structured data markup" is a standard way to annotate your content so machines can understand it. Implementing it will help your website rank higher in search engines.' , 'hueman-pro' )
          ),
          'smart_load_img'  =>  array(
                'default'       => 0,
                'control'     =>  'HU_controls',
                'label'       => __( 'Load images on scroll' , 'hueman-pro' ),
                'section'     => 'performance_sec',
                'type'        => 'nimblecheck',
                'notice'      => __('Check this option to delay the loading of non visible images. Images below the viewport will be loaded dynamically on scroll. This can really boost speed performances by reducing the weight of long pages that include many images.' , 'hueman-pro')
          ),
          'js-mobile-detect'  =>  array(
                'default'       => 0,
                'control'     =>  'HU_controls',
                'label'       => __( 'Mobile device detection' , 'hueman-pro' ),
                'section'     => 'performance_sec',
                'type'        => 'nimblecheck',
                'notice'      => __('When checked, this option loads a small javascript file ( 30 kb ) to detect if your site is being displayed by a mobile device like a phone or a tablet. It is recommended to check this option if you are using a cache plugin.' , 'hueman-pro')
          ),

          // added for https://github.com/presscustomizr/hueman/issues/863
          'defer_front_script'  =>  array(
                'default'       => 0,
                'control'     =>  'HU_controls',
                'label'       => __( 'Defer loading javascript files to avoid render blocking issues' , 'hueman-pro' ),
                'section'     => 'performance_sec',
                'type'        => 'nimblecheck'
          ),
          // To be implemented for https://github.com/presscustomizr/hueman/issues/881
          // 'use_fa_icons'  =>  array(
          //       'default'       => 1,
          //       'control'     =>  'HU_controls',
          //       'label'       => __( 'Use Font Awesome icons' , 'hueman' ),
          //       'section'     => 'performance_sec',
          //       'type'        => 'nimblecheck'
          // ),
          'defer_font_awesome'  =>  array(
                'default'       => 0,
                'control'     =>  'HU_controls',
                'label'       => __( 'Defer loading Font Awesome icons' , 'hueman-pro' ),
                'section'     => 'performance_sec',
                'type'        => 'nimblecheck'
          )

      );
    }


     /*-----------------------------------------------------------------------------------------------------
                                   ADMIN SETTINGS
    ------------------------------------------------------------------------------------------------------*/
    function hu_admin_sec() {
      return array(
          'about-page' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Display the "About Hueman" page in the "Appearance" admin menu', 'hueman-pro'),
                'section'   => 'admin_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'This page is intended to provide information about the Hueman theme : changelog, release note, documentation link. It also display information about your current install that can be useful if you need to report an issue.' , 'hueman-pro' )
          ),
          'help-button' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Display a Help button in the admin bar', 'hueman-pro'),
                'section'   => 'admin_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'This button links to the "About Hueman" page.' , 'hueman-pro' )
          )
      );
    }



    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : HEADER
    *******************************************************************************************************
    ******************************************************************************************************/
    /*-----------------------------------------------------------------------------------------------------
                                   HEADER DESIGN SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_header_design_sec() {
      return array(
          'site-description' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Display your site's description (tagline)", 'hueman-pro'),
                'section'   => 'header_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'The description that appears next to your logo' , 'hueman-pro' ),
                'ubq_section'   => array(
                    'section' => 'title_tagline',
                    'priority' => '15'
                )
          ),
          'user-header-bg-color-important' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Apply your custom background colors in priority for the topbar and the mobile menu' , 'hueman-pro' ),
                'section'   => 'header_design_sec',
                'type'      => 'nimblecheck',
                //'active_callback' => 'hu_is_pro',
                'notice' => sprintf( __('This can be used to ensure your background colors are applied when designing a header with a background image in %1$s', 'hueman-pro'),
                    sprintf('<a href="https://docs.presscustomizr.com/article/284-pro-designing-header-background-and-slider-with-hueman-pro" target="_blank">%1$s</a>',
                        __('Hueman Pro', 'hueman-pro')
                    )
                )
          ),
          'color-topbar' => array(
                'default'     => hu_user_started_before_version( '3.3.8' ) ? '#26272b' : '#121d30',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Topbar Background' , 'hueman-pro' ),
                'section'     => 'header_design_sec',
                'type'        =>  'wp_color_alpha',
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color'
                //'transport'   => 'postMessage'
          ),
          'color-header' => array(
                'default'     => hu_user_started_before_version( '3.3.8' ) ? '#33363b' : '#454e5c',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Header Background' , 'hueman-pro' ),
                'section'     => 'header_design_sec',
                'type'        =>  'wp_color_alpha',
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'transport'   => ( ( defined( 'HU_IS_PRO_ADDONS' ) && HU_IS_PRO_ADDONS ) || ( defined('HU_IS_PRO') && HU_IS_PRO  ) ) ? 'refresh' : 'postMessage'
          ),
          'color-header-menu' => array(
                'default'     => hu_user_started_before_version( '3.3.8' ) ? '#33363b' : '#454e5c',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Header Menu Background' , 'hueman-pro' ),
                'section'     => 'header_design_sec',
                'type'        =>  'wp_color_alpha',
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'transport'   => ( ( defined( 'HU_IS_PRO_ADDONS' ) && HU_IS_PRO_ADDONS ) || ( defined('HU_IS_PRO') && HU_IS_PRO  ) ) ? 'refresh' : 'postMessage'
          ),
          'color-mobile-menu' => array(
                'default'     => hu_user_started_before_version( '3.3.8' ) ? '#33363b' : '#454e5c',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Mobile Menu Background' , 'hueman-pro' ),
                'section'     => 'header_design_sec',
                'type'        =>  'wp_color_alpha',
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                //'transport'   => 'postMessage'
          ),
          'transparent-fixed-topnav' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __( 'Apply a semi-transparent filter to the topbar and mobile menu on scroll' , 'hueman-pro' ),
                'section'   => 'header_design_sec',
                'type'      => 'nimblecheck',
          ),
        );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   HEADER IMAGE SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_header_image_sec() {
      return array(
          'use-header-image' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Use a header banner image' , 'hueman-pro' ),
                'section'   => 'header_image_sec',
                'type'      => 'nimblecheck',
                'notice'    => __('Upload a header image (supported formats : .jpg, .png, .gif, svg, svgz). This will disable header title/logo, site description, header ads widget' , 'hueman-pro')
          ),

          // april 2020 : 2 options added for https://github.com/presscustomizr/hueman/issues/877
          'header-img-full-width' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __( 'Image fills 100% of the width' , 'hueman-pro' ),
                'section'   => 'header_image_sec',
                'type'      => 'nimblecheck'
          ),
          'header-img-natural-height' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Use the original image height' , 'hueman-pro' ),
                'section'   => 'header_image_sec',
                'type'      => 'nimblecheck'
          ),
          // nov 2020 :  https://github.com/presscustomizr/hueman/issues/931
          'header-img-link-home' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __( 'Link the banner image to your home page' , 'hueman-pro' ),
                'section'   => 'header_image_sec',
                'type'      => 'nimblecheck'
          ),
          // 'header-img-height' => array(
          //       'default'   => 400,
          //       'control'   => 'HU_controls',
          //       'label'     => __( 'Set image\'s max height' , 'hueman' ),
          //       'section'   => 'header_image_sec',
          //       'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
          //       'type'      => 'number' ,
          //       'step'      => 1,
          //       'min'       => 0,
          // ),
          'logo-title-on-header-image' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Display your logo or site title, and tagline on top of the header image' , 'hueman-pro' ),
                'section'   => 'header_image_sec',
                'type'      => 'nimblecheck',
                'notice'    => sprintf( '%3$s <strong><a href="%1$s" title="%3$s">%2$s</a><strong>',
                    "javascript:wp.customize.section('title_tagline').focus();",
                    __("here" , "hueman-pro"),
                    __("Set your logo, title and tagline", "hueman-pro")
                ),
          ),
      );
    }

    /*-----------------------------------------------------------------------------------------------------
                                   Advertisement Widget SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_header_widget_sec() {
      return array(
          'header-ads' => array(
                'default'   => hu_user_started_before_version( '3.2.4' ) ? 1 : 0,
                'control'   => 'HU_controls',
                'label'     => __("Display a widget in your header", 'hueman-pro'),
                'section'   => 'header_widget_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Header widget area, perfect to insert advertisements. Note : this feature is not available when a header image is being displayed.' , 'hueman-pro')
          ),
          'header-ads-desktop' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Display the header widget zone on desktop devices", 'hueman-pro'),
                'section'   => 'header_widget_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'This will display your widget zone on devices with a width greater than 720 pixels : laptops and desktops.' , 'hueman-pro')
          ),
          'header-ads-mobile' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Display the header widget zone on mobile devices", 'hueman-pro'),
                'section'   => 'header_widget_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'This will display your widget zone on devices with a width smaller than 720 pixels : tablets, smartphones.' , 'hueman-pro')
          )

      );
    }


    /*-----------------------------------------------------------------------------------------------------
                                   Advertisement Widget SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_header_menus_sec() {
      $nav_section_desc = "<br/>" . sprintf( __("You can create menus and set their locations %s." , "hueman-pro"),
          sprintf( '%1$s<strong><a class="jump-to-menu-panel" href="#" title="%3$s">%2$s</a><strong>',
              sprintf( '<script>%1$s</script>',
                  "jQuery( function($) {
                      $('.jump-to-menu-panel').on('click', function() {
                          wp.customize.section('menu_locations').expanded( false );
                          wp.customize.panel('nav_menus').focus();
                      });
                  });"
              ),// "javascript:wp.customize.panel('nav_menus').focus();"
              __("in the menu panel" , "hueman-pro"),
              __("create/edit menus", "hueman-pro")
          )
      );

      $header_nav_notice =  sprintf( '%1$s %2$s', __( 'The Hueman theme supports up to two menu locations in your header.', 'hueman-pro' ), $nav_section_desc );
      return array(
          'default-menu-header' => array(
                'default'   => 0,//hu_user_started_before_version( '3.3.8' ) ? 0 : 1,
                'control'   => 'HU_controls',
                'label'     => __("Topbar menu", 'hueman-pro') . ' : ' . __("Use a default page menu if no menu has been assigned.", 'hueman-pro'),
                'section'   => 'header_menus_sec',
                'type'      => 'nimblecheck',
                'notice'    => $header_nav_notice,
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '90'
                )
          ),
          'header-desktop-sticky' => array(
                'default'   => 'stick_up',
                'control'   => 'HU_controls',
                'title'     => sprintf( '%1$s %2$s', __( 'Menus settings for', 'hueman-pro' ) , __('Desktop devices', 'hueman-pro' ) ),
                'label'     => sprintf( '%1$s : %2$s', __('Desktop devices', 'hueman-pro' ) , __('top menu visibility on scroll', 'hueman-pro') ),
                'section'   => 'header_menus_sec',
                'type'      => 'select',
                'choices'   => array(
                    'no_stick'      => __( 'Not visible when scrolling the page', 'hueman-pro'),
                    'stick_up'      => __( 'Reveal on scroll up', 'hueman-pro'),
                    'stick_always'  => __( 'Always visible', 'hueman-pro')
                ),
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '120'
                )
          ),

          'desktop-search' => array(
                'default'   => 'topbar',
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Desktop devices', 'hueman-pro' ) , __('display a search field', 'hueman-pro') ),
                'section'   => 'header_menus_sec',
                'type'      => 'select',
                'choices'   => array(
                    'no_search' => __( 'No search field', 'hueman-pro'),
                    'topbar'    => __( 'Display a search field in the top menu', 'hueman-pro'),
                    'header'    => __( 'Display a search field in the header menu', 'hueman-pro')
                ),
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '120'
                )
          ),
          'header_mobile_menu_layout' => array(
                'default'   => hu_user_started_before_version( '3.3.8' ) ? 'main_menu' : 'top_menu',
                'control'   => 'HU_controls',
                'title'     => sprintf( '%1$s %2$s', __( 'Menus settings for', 'hueman-pro' ) , __('Mobile devices', 'hueman-pro' ) ),
                'label'     => __( 'Select the menu(s) to use for mobile devices', 'hueman-pro'),
                'section'   => 'header_menus_sec',
                'type'      => 'select',
                'choices'   => array(
                    'main_menu' => __('Header Menu', 'hueman-pro'),
                    'top_menu'  => __('Topbar Menu', 'hueman-pro'),
                    'mobile_menu' => __('Specific Mobile Menu', 'hueman-pro'),
                    'both_menus' => __( 'Topbar and header menus, logo centered', 'hueman-pro')
                ),
                'notice'    => sprintf( '%1$s<br/>%2$s <br/>%3$s',
                    __( 'When your visitors are using a smartphone or a tablet, the header becomes a thin bar on top, where the menu is revealed when clicking on the hamburger button. This option let you choose which menu will be displayed.' , 'hueman-pro' ),
                    __( 'If the selected menu location has no menu assigned, the theme will try to assign another menu in this order : topbar, mobile, header.' , 'hueman-pro' ),
                    $nav_section_desc
                ),
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '100'
                )
          ),
          'header-mobile-sticky' => array(
                'default'   => 'stick_up',
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Mobile devices', 'hueman-pro' ) , __('top menu visibility on scroll', 'hueman-pro') ),
                'section'   => 'header_menus_sec',
                'type'      => 'select',
                'choices'   => array(
                    'no_stick'      => __( 'Not visible when scrolling the page', 'hueman-pro'),
                    'stick_up'      => __( 'Reveal on scroll up', 'hueman-pro'),
                    'stick_always'  => __( 'Always visible', 'hueman-pro')
                ),
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '130'
                )
          ),
          'header_mobile_btn' => array(
                'default'   => 'animated',
                'control'   => 'HU_controls',
                'label'     => __( 'Style of the mobile menu button', 'hueman-pro'),
                'section'   => 'header_menus_sec',
                'type'      => 'select',
                'choices'   => array(
                    'animated' => __('Animated', 'hueman-pro'),
                    'simple'  => __('Simple', 'hueman-pro'),
                ),
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '110'
                )
          ),
          'mobile-submenu-click' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Mobile devices', 'hueman-pro' ) , __( 'Expand submenus on click', 'hueman-pro') ),
                'section'   => 'header_menus_sec',
                'type'      => 'nimblecheck',
                'ubq_section' => array(
                      'section' => 'footer_design_sec',
                      'priority' => '18'
                )
          ),
          'mobile-search' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Mobile devices', 'hueman-pro' ) , __('display a search field', 'hueman-pro') ),
                'section'   => 'header_menus_sec',
                'type'      => 'nimblecheck',
                'ubq_section'   => array(
                    'section' => 'menu_locations',
                    'priority' => '120'
                )
          ),
      );
    }



    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : MAIN CONTENT
    *******************************************************************************************************
    ******************************************************************************************************/
    /*-----------------------------------------------------------------------------------------------------
                                   FRONT PAGE CONTENT
    ------------------------------------------------------------------------------------------------------*/
    // function hu_content_home_sec() {
    //   return array(
    //       'layout-page' => array(
    //             'default'   => 'inherit',
    //             'control'   => 'HU_Customize_Layout_Control',
    //             'label'     => __('Default Page', 'hueman'),
    //             'section'   => 'content_layout_sec',
    //             'type'      => 'czr_layouts',//@todo create a radio-image type
    //             'choices'   => $this -> hu_get_content_layout_choices(),
    //             'notice'    => __('[ <strong>is_page</strong> ] Default page layout - If a page has a set layout, it will override this.' , 'hueman')
    //       )
    //   );
    // }

    /*-----------------------------------------------------------------------------------------------------
                                   CONTENT LAYOUT SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_content_layout_sec() {
      $layout_text = __('Columns layout for', 'hueman-pro');
      return array(
          'layout-global' => array(
                'default'   => 'col-3cm',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => __('Global Layout', 'hueman-pro'),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices( 'global' ),
                'notice'    => __('Other layouts will override this option if they are set' , 'hueman-pro')
          ),
          'force-layout-global' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Force the global layout", 'hueman-pro'),
                'section'   => 'content_layout_sec',
                'type'      => 'nimblecheck',
                'notice'    => __('The global layout will be applied on every pages, even when a specific layout is set.' , 'hueman-pro')
          ),
          'layout-home' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Home', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_home</strong> ] Posts homepage layout' , 'hueman-pro'),
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '0'
                )
          ),
          'layout-single' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Single', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_single</strong> ] Single post layout - If a post has a set layout, it will override this.' , 'hueman-pro'),
                'ubq_section'   => array(
                    'section' => 'content_single_sec',
                    'priority' => '0'
                )
          ),
          'layout-archive' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Archive', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_archive</strong> ] Category, date, tag and author archive layout' , 'hueman-pro')
          ),
          'layout-archive-category' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Archive - Category', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_category</strong> ] Category archive layout' , 'hueman-pro')
          ),
          'layout-search' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Search', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_search</strong> ] Search page layout' , 'hueman-pro')
          ),
          'layout-404' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Error 404', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_404</strong> ] Error 404 page layout' , 'hueman-pro')
          ),
          'layout-page' => array(
                'default'   => 'inherit',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => sprintf('%1$s : %2$s', $layout_text, __('Default Page', 'hueman-pro') ),
                'section'   => 'content_layout_sec',
                'type'      => 'czr_layouts',//@todo create a radio-image type
                'choices'   => $this -> hu_get_content_layout_choices(),
                'notice'    => __('[ <strong>is_page</strong> ] Default page layout - If a page has a set layout, it will override this.' , 'hueman-pro')
          ),
      );
    }


    /*-----------------------------------------------------------------------------------------------------
                                   BLOG CONTENT SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_content_blog_sec() {
      return array(
          'blog-heading-enabled' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'title'     => __( 'Blog Heading', 'hueman-pro' ),
                'label'     => __("Display a custom heading for your blog.", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                //'active_callback' => 'is_home',
                'priority'   => 5,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '30'
                )
          ),
          'blog-heading' => array(
                'default'   => get_bloginfo('name'),
                'control'   => 'HU_controls',
                'label'     => __( 'Blog Heading', 'hueman-pro'),
                'type'      => 'text',
                'section'   => 'content_blog_sec',
                'notice'    => __( 'Your blog heading. Html is allowed. Note : write a blank space to hide the default content.', 'hueman-pro'),
                'sanitize_callback' => array( $this, 'hu_sanitize_html_text_input' ),
                //'active_callback' => 'is_home',
                'priority'   => 10,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '40'
                )
          ),
          'blog-subheading' => array(
                'default'   => __( 'Blog', 'hueman-pro'),
                'control'   => 'HU_controls',
                'label'     => __( 'Blog Sub-Heading', 'hueman-pro'),
                'type'      => 'text',
                'section'   => 'content_blog_sec',
                'notice'    => __( 'Your blog sub-heading. Html is allowed. Note : write a blank space to hide the default content.', 'hueman-pro'),
                'sanitize_callback' => array( $this, 'hu_sanitize_html_text_input' ),
                //'active_callback' => 'is_home',
                'priority'   => 15,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '50'
                )
          ),
          'blog-restrict-by-cat' => array(
            'default'   => array(),
            'type'   => 'czr_multiple_picker',
            'label'     => __( 'Apply a category filter to your home / blog posts', 'hueman-pro' ),
            'section'   => 'content_blog_sec',
            'control'   => 'HU_Customize_Multipicker_Categories_Control',
            'priority'   => 18,
            'notice' => sprintf( '%1$s <a href="%2$s" target="_blank">%3$s<span style="font-size: 17px;" class="dashicons dashicons-external"></span></a><br>%4$s' ,
                              __( 'Click inside the above field and pick post categories you want to display. No filter will be applied when empty.', 'hueman-pro' ),
                              esc_url('codex.wordpress.org/Posts_Categories_SubPanel'),
                              __('Learn more about post categories in WordPress' , 'hueman-pro' ),
                              sprintf( '<strong>%1$s</strong> %2$s',
                                    __( 'Note for Pro users:', 'hueman-pro'),
                                    __( 'The category filter will not be applied when using the <strong>Classic grid</strong> post list design if the <strong>infinite scroll</strong> option is active.', 'hueman-pro' )
                              )
            ),
            'ubq_section'   => array(
                  'section' => 'static_front_page',
                  'priority' => '55'
            )
          ),
          'blog-standard' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'title'     => __( 'Post List Design', 'hueman-pro' ),
                'label'     => __("Display your blog posts as a standard list.", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'While the default blog design is a grid of posts, you can check this option and display one post per row, whith the thumbnail beside the text.' , 'hueman-pro'),
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 20,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '60'
                )
          ),
          // added for https://github.com/presscustomizr/hueman/issues/859
          'blog-standard-full-content' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Display your blog posts in full content", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 22,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '62'
                )
          ),
          'blog-standard-show-thumb' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Display the post thumbnail", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 23,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '63'
                )
          ),
          'blog-use-original-image-size'  =>  array(
                'default'   => 0,
                'control'   => 'HU_controls' ,
                'type'      => 'nimblecheck',
                'label'     => __( "Display featured images in their original dimensions in post lists" , 'hueman-pro' ),
                'section'   => 'content_blog_sec' ,
                //'transport' => 'postMessage',
                'notice'    => __( 'When checked, the post featured image are displayed in their original size, instead of the optimized image sizes of the theme. Make sure your original images are not too large, it could slow down your website.', 'hueman-pro'),
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 25,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '65'
                )
          ),
          'excerpt-length'  =>  array(
                'default'   => 34,
                'control'   => 'HU_controls' ,
                'title'     => __( 'Post Summary', 'hueman-pro' ),
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'     => __( "Excerpt Length" , 'hueman-pro' ),
                'section'   => 'content_blog_sec' ,
                'type'      => 'number' ,
                'step'      => 1,
                'min'       => 0,
                //'transport' => 'postMessage',
                'notice'    => sprintf( __( "The WordPress Excerpt is the summary or description of a post. By default, it will be the first words of a post, but you can write a %s if you want. You can set the number of words you want to display with this option." , "hueman-pro" ),
                      sprintf('<a href="%1$s" title="%2$s" target="_blank">%2$s <span class="dashicons dashicons-external" style="font-size: inherit;display: inherit;"></span></a>', esc_url('codex.wordpress.org/Excerpt#How_to_add_excerpts_to_posts'), __('custom excerpt', 'hueman-pro') )
                ),
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 25,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '70'
                )
          ),
          'archive-title-with-icon' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __( 'Display the archive type and an icon next to the archive headings', 'hueman-pro' ),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'In WordPress, archives are the pages listing posts by category, tag, author and date.' , 'hueman-pro'),
                //'active_callback' => 'hu_is_post_list',
                'priority'   => 150
          ),
          'featured-posts-enabled' => array(
                'default'   => 1,
                'title'       => __( 'Featured posts', 'hueman-pro' ),
                'control'   => 'HU_controls',
                'label'     => __("Feature posts on top of your blog", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Check this box to display a selection of posts with a slideshow, on top of your blog.' , 'hueman-pro'),
                //'active_callback' => 'is_home',
                'priority'   => 30,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '80'
                )
          ),
          'featured-category' => array(
                'default'   => "0",
                'control'   => 'HU_controls',
                'label'     => __("Select a category to feature", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'select',//@todo create a simple cat picker with select type. => evolve to multipicker? Retrocompat ?
                'choices'   => $this -> hu_get_the_cat_list(),
                'notice'    => __( 'If no specific category is selected, the featured posts block will display your latest post(s) from all categories.' , 'hueman-pro'),
                //'active_callback' => 'is_home',
                'priority'   => 35,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '90'
                )
          ),
          'featured-posts-count'  =>  array(
                'default'   => 1,
                'control'   => 'HU_controls' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'     => __( "Featured Post Count" , 'hueman-pro' ),
                'section'   => 'content_blog_sec' ,
                'type'      => 'number' ,
                'step'      => 1,
                'min'       => 0,
                //'transport' => 'postMessage',
                'notice'    => __( "Max number of featured posts to display. <br /><i>Set to 1 and it will show it without any slider script</i><br /><i>Set it to 0 to disable</i>" , "hueman-pro" ),//@todo sprintf split translation
                //'active_callback' => 'is_home',
                'priority'   => 40,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '100'
                )
          ),
          'featured-posts-full-content' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Display the full post content", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'By default, your featured posts display the first words of their content ( the "excerpt"). Check this box to display the full content.' , 'hueman-pro'),
                //'active_callback' => 'is_home',
                'priority'   => 45,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '110'
                )
          ),
          'featured-slideshow' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Animate your featured posts with a slideshow", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Enables the automatic animation of the featured posts carousel.' , 'hueman-pro'),
                //'active_callback' => 'is_home',
                'priority'   => 50,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '120'
                )
          ),
          'featured-slideshow-speed'  =>  array(
                'default'   => 5000,
                'control'   => 'HU_controls' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                'label'     => __( "Featured Slideshow Speed" , 'hueman-pro' ),
                'section'   => 'content_blog_sec' ,
                'type'      => 'number' ,
                'step'      => 500,
                'min'       => 500,
                'transport' => 'postMessage',
                'notice'    => __( "Speed of the automatic slideshow animation" , "hueman-pro" ),
                //'active_callback' => 'is_home',
                'priority'   => 55,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '130'
                )
          ),
          'featured-posts-include' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Display the featured posts also in the list of posts", 'hueman-pro'),
                'section'   => 'content_blog_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'If this box is checked, your featured posts will be displayed both in the featured slider and in the post list below. Usually not recommended because a given post might appear two times on the same page.' , 'hueman-pro'),
                //'active_callback' => 'is_home',
                'priority'   => 60,
                'ubq_section'   => array(
                    'section' => 'static_front_page',
                    'priority' => '140'
                )
          ),
          'post-list-meta-category' => array(
            'default'   => 1,
            'control'   => 'HU_controls',
            'title'     => __('Post Metas', 'hueman-pro'),
            'label'     => __('Display categories', 'hueman-pro'),
            'section'   => 'content_blog_sec',
            'type'      => 'nimblecheck',
            'priority'  => 70,
          ),
          'post-list-meta-date' => array(
            'default'   => 1,
            'control'   => 'HU_controls',
            'label'     => __('Display post date', 'hueman-pro'),
            'section'   => 'content_blog_sec',
            'type'      => 'nimblecheck',
            'priority'  => 71,
          ),
          'post-list-meta-author' => array(
            'default'   => 0,
            'control'   => 'HU_controls',
            'label'     => __('Display author', 'hueman-pro'),
            'section'   => 'content_blog_sec',
            'type'      => 'nimblecheck',
            'priority'  => 72,
          ),
      );
    }



    /*-----------------------------------------------------------------------------------------------------
                                   SINGLE POSTS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_content_single_sec() {
      return array(
          'author-bio' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Single - Author Bio", 'hueman-pro'),
                'section'   => 'content_single_sec',
                'type'      => 'nimblecheck',
                'priority'  => 10,
                'notice'    => __( 'Display post author description, if it exists' , 'hueman-pro'),
                //'active_callback' => function_exists('HU_AD') ? 'hu_is_single' : ''//enabled when hueman-addons is enabled
          ),
          'singular-post-featured-image' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Featured image', 'hueman-pro' ),
                'section'   => 'content_single_sec',
                'type'      => 'nimblecheck',
                'priority'  => 10,
                'notice'    => __( 'Display the post\'s featured image when it is set. Note that audio and image post formats automatically display the featured image.' , 'hueman-pro'),
                'skoped' => false// implemented initally not skopable in jan-2020, see ctx_get_excluded_settings()
          ),
          'singular-post-cropped-feat-img' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Used cropped image ( max 1320x500 on desktops)', 'hueman-pro' ),
                'section'   => 'content_single_sec',
                'type'      => 'nimblecheck',
                'priority'  => 10,
                'skoped' => false// implemented initally not skopable in jan-2020, see ctx_get_excluded_settings()
          ),
          'related-posts' => array(
                'default'   => 'categories',
                'control'   => 'HU_controls',
                'title'     => __("Related posts", 'hueman-pro'),
                'label'     => __("Single - Related Posts", 'hueman-pro'),
                'section'   => 'content_single_sec',
                'type'      => 'select',
                'priority'  => 20,
                'choices' => array(
                  '1'           => __( 'Disable' , 'hueman-pro' ),
                  'categories'  => __( 'Related by categories' , 'hueman-pro' ),
                  'tags'        => __( 'Related by tags' , 'hueman-pro' )
                ),
                'notice'    => __( 'Display randomized related articles below the post' , 'hueman-pro'),
                //'active_callback' => function_exists('HU_AD') ? 'hu_is_single' : ''//enabled when hueman-addons is enabled
          ),
          'post-nav' => array(
                'default'   => 's1',
                'control'   => 'HU_controls',
                'title'     => __("Post navigation", 'hueman-pro'),
                'label'     => __("Post navigation in single posts", 'hueman-pro'),
                'section'   => 'content_single_sec',
                'type'      => 'select',
                'priority'  => 30,
                'choices' => array(
                  '1'           => __( 'Disable' , 'hueman-pro' ),
                  's1'          => __( 'Left Sidebar' , 'hueman-pro' ),
                  's2'          => __( 'Right Sidebar' , 'hueman-pro' ),
                  'content'     => __( 'Below content' , 'hueman-pro' )
                ),
                'notice'    => __( 'Display links to the next and previous article' , 'hueman-pro'),
                //'active_callback' => function_exists('HU_AD') ? 'hu_is_single' : '',//enabled when hueman-addons is enabled
                'ubq_section'   => array(
                    'section' => 'sidebars_design_sec',
                    'priority' => '2'
                )
          ),
          'post-tags' => array(
            'default'   => 1,
            'control'   => 'HU_controls',
            'title'     => __('Post Tags', 'hueman-pro'),
            'label'     => __('Post tags', 'hueman-pro'),
            'section'   => 'content_single_sec',
            'type'      => 'nimblecheck',
            'notice'    => __( 'Display the post tags after the post content.' , 'hueman-pro'),
            'priority'  => 33,
          ),
          'post-meta-author' => array(
            'default'   => 1,
            'control'   => 'HU_controls',
            'title'     => __('Post Metas', 'hueman-pro'),
            'label'     => __('Post author name', 'hueman-pro'),
            'section'   => 'content_single_sec',
            'type'      => 'nimblecheck',
            'notice'    => __( 'Display the author name below the post title.' , 'hueman-pro'),
            'priority'  => 31,
          ),
          'post-meta-date' => array(
            'default'   => 1,
            'control'   => 'HU_controls',
            'label'     => __('Post date', 'hueman-pro'),
            'section'   => 'content_single_sec',
            'type'      => 'nimblecheck',
            'notice'    => __( 'Display the date below the post title.' , 'hueman-pro'),
            'priority'  => 32,
          ),
        );
    }



    /*-----------------------------------------------------------------------------------------------------
                                   SINGLE PAGE SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_content_page_sec() {
      return array(
          'singular-page-featured-image' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Featured image', 'hueman-pro' ),
                'section'   => 'content_page_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Display the page\'s featured image when it is set' , 'hueman-pro')
          ),
          'singular-page-cropped-feat-img' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __( 'Used cropped image (max 1320x500 on desktops)', 'hueman-pro' ),
                'section'   => 'content_page_sec',
                'type'      => 'nimblecheck'
          ),
      );
    }



    /*-----------------------------------------------------------------------------------------------------
                                   THUMBNAIL SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_content_thumbnail_sec() {
      return array(
          'placeholder' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Thumbnail Placeholder", 'hueman-pro'),
                'section'   => 'content_thumbnail_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Display featured image placeholders if no featured image is set' , 'hueman-pro')
          ),
          'comment-count' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __("Thumbnail Comment Count", 'hueman-pro'),
                'section'   => 'content_thumbnail_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( 'Display comment count on thumbnails' , 'hueman-pro'),
                'ubq_section'   => array(
                    'section' => 'comments_sec',
                    'priority' => '30'
                )
          )
        );
    }




    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : SIDEBARS
    *******************************************************************************************************
    ******************************************************************************************************/
    /*-----------------------------------------------------------------------------------------------------
                                SIDEBAR DESIGN AND MOBILE SETTINGS SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_sidebars_design_sec() {
      return array(
          'sidebar-top' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Sidebar Top Boxes', 'hueman-pro'),
                'section'   => 'sidebars_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __('Display boxes at the top of the sidebars' , 'hueman-pro'),
                'priority'  => 1
          ),
          'primary-sb-text' => array(
                'control'   => 'HU_controls',
                'default'   => __( 'Follow:', 'hueman-pro' ),
                'label'     => __( 'Primary sidebar title', 'hueman-pro'),
                'type'      => 'text',
                'section'   => 'sidebars_design_sec',
                'sanitize_callback' => array( $this, 'hu_sanitize_html_text_input' ),
                'priority'  => 2,
                'notice'    => __( 'Html is allowed.', 'hueman-pro')
          ),
          'secondary-sb-text' => array(
                'control'   => 'HU_controls',
                'default'   => __( 'More', 'hueman-pro' ),
                'label'     => __( 'Secondary sidebar title', 'hueman-pro'),
                'type'      => 'text',
                'section'   => 'sidebars_design_sec',
                'sanitize_callback' => array( $this, 'hu_sanitize_html_text_input' ),
                'priority'  => 2,
                'notice'    => __( 'Html is allowed.', 'hueman-pro')
          ),
          'sidebar-background' => array(
                'default'   => '#f0f0f0',
                'control'   => 'HU_Customize_Color_Alpha_Control',
                'label'     => __('Sidebars background color', 'hueman-pro'),
                'section'   => 'sidebars_design_sec',
                'type'      =>  'wp_color_alpha' ,
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'priority'  => 4
          ),
          'desktop-sticky-sb' => array(
                'default'   => hu_user_started_before_version( '3.3.9', '1.0.3' ) ? 1 : 0,
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Desktop devices', 'hueman-pro' ) , __('make sidebars sticky on scroll', 'hueman-pro') ),
                'section'   => 'sidebars_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __("Glues your website's sidebars on top of the page, making them permanently visible when scrolling up and down. Useful when a sidebar is too tall or too short compared to the rest of the content." , 'hueman-pro')
          ),
          'mobile-sticky-sb' => array(
                'default'   => hu_user_started_before_version( '3.3.9', '1.0.3' ) ? 1 : 0,
                'control'   => 'HU_controls',
                'label'     => sprintf( '%1$s : %2$s', __('Mobile devices', 'hueman-pro' ) , __('make sidebars sticky on scroll', 'hueman-pro') ),
                'section'   => 'sidebars_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __( "Decide if your sidebars should be sticky on tablets and smartphones devices." , 'hueman-pro' )
          ),
          'mobile-sidebar-hide' => array(
                'default'   => '1',
                'control'   => 'HU_controls',
                'label'     => __('Mobile Sidebar Content', 'hueman-pro'),
                'section'   => 'sidebars_design_sec',
                'type'      => 'select',//@todo create a radio type
                'priority'  => 100,
                'choices' => array(
                  '1'           => __( 'Display both sidebars' , 'hueman-pro' ),
                  's1'          => __( 'Hide primary sidebar' , 'hueman-pro' ),
                  's2'          => __( 'Hide secondary sidebar' , 'hueman-pro' ),
                  's1-s2'       => __( 'Hide both sidebars' , 'hueman-pro' )
                ),
                'notice'    => __('Control how the sidebar content is displayed on smartphone mobile devices (480px).' , 'hueman-pro')
            ),
            'mobile-sidebar-primary-first' => array(
                  'default'   => 0,
                  'control'   => 'HU_controls',
                  'label'     => 'Mobile devices: display your primary sidebar first',
                  'section'   => 'sidebars_design_sec',
                  'type'      => 'nimblecheck',
                  'priority'  => 110,
                  'notice'     => __( 'Display the primary sidebar above the content column on smartphone mobile devices (480px).', 'hueman-pro' ),
            ),
            'sl-in-sidebar' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Display social links in the primary sidebar', 'hueman-pro'),
                'section'   => 'sidebars_design_sec',
                'type'      => 'nimblecheck',
                'priority'  => 120,
            ),

      );
    }


    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : FOOTER
    *******************************************************************************************************
    ******************************************************************************************************/

    /*-----------------------------------------------------------------------------------------------------
                                  FOOTER DESIGN SECTION
    ------------------------------------------------------------------------------------------------------*/
    function hu_footer_design_sec() {
      global $wp_version;
      $nav_section_desc = "<br/>" . sprintf( __("You can create menus and set their locations %s." , "hueman-pro"),
          sprintf( '%1$s<strong><a class="jump-to-menu-panel" href="#" title="%3$s">%2$s</a><strong>',
              sprintf( '<script>%1$s</script>',
                  "jQuery( function($) {
                      $('.jump-to-menu-panel').on('click', function() {
                          wp.customize.section('menu_locations').expanded( false );
                          wp.customize.panel('nav_menus').focus();
                      });
                  });"
              ),// "javascript:wp.customize.panel('nav_menus').focus();"
              __("in the menu panel" , "hueman-pro"),
              __("create/edit menus", "hueman-pro")
          )
      );
      return array(
          'footer-ads' => array(
                'default'   => hu_user_started_before_version( '3.2.4' ) ? 1 : 0,
                'control'   => 'HU_controls',
                'label'     => __("Display a full width widget area in your footer", 'hueman-pro'),
                'section'   => 'footer_design_sec',
                'type'      => 'nimblecheck',
                'notice'    => __('This zone is located before the other footer widgets and takes 100% of the width. Very appropriate to display a Google Map or an advertisement banner.', 'hueman-pro')
          ),
          'default-menu-footer' => array(
                'default'   => 0,
                'control'   => 'HU_controls',
                'label'     => __("Use a default page menu if no menu has been assigned.", 'hueman-pro'),
                'section'   => 'footer_design_sec',
                'type'      => 'nimblecheck',
                'priority'  => 15,
                'notice'    => $nav_section_desc
          ),
          'footer-widgets' => array(
                'default'   => hu_user_started_before_version( '3.2.4' ) ? '0' : '3',
                'control'   => 'HU_Customize_Layout_Control',
                'label'     => __('Select columns to enable footer widgets', 'hueman-pro'),
                'section'   => 'footer_design_sec',
                'type'      => 'czr_layouts',
                'choices'   => $this -> hu_get_footer_layout_choices(),
                'priority'  => 20,
                'notice'    => __('Recommended number of columns : 3' , 'hueman-pro')
          ),
          'footer-logo'  => array(
                'control'   =>  version_compare( $wp_version, '4.3', '>=' ) ? 'HU_Customize_Cropped_Image_Control' : 'HU_Customize_Upload_Control',
                'label'     =>  __( 'Upload your custom logo image' , 'hueman-pro' ),
                'section'   => 'footer_design_sec' ,
                'sanitize_callback' => array( $this , 'hu_sanitize_number' ),
                //we can define suggested cropping area and allow it to be flexible (def 150x150 and not flexible)
                'width'     => 250,
                'height'    => 100,
                'flex_width' => true,
                'flex_height' => true,
                //to keep the selected cropped size
                'dst_width'  => false,
                'dst_height'  => false,
                'priority'  => 25,
                'notice'    => __('Upload your custom logo image. Supported formats : .jpg, .png, .gif, svg, svgz' , 'hueman-pro')
          ),
          'color-footer' => array(
                'default'     => '#33363b',
                'control'     => 'HU_Customize_Color_Alpha_Control',
                'label'       => __( 'Footer Background' , 'hueman-pro' ),
                'section'     => 'footer_design_sec',
                'type'        =>  'wp_color_alpha' ,
                'sanitize_callback'    => 'maybe_hash_hex_color',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'priority'  => 30,
                'transport'   => 'postMessage'
          ),
          'copyright' => array(
                'control'   => 'HU_controls',
                'default'   => sprintf( '{{site_title}} &copy; {{year}}. %1$s', __( 'All Rights Reserved.', 'hueman-pro' ) ),
                'label'     => __( 'Replace the footer copyright text', 'hueman-pro'),
                'type'      => 'text',
                'section'   => 'footer_design_sec',
                'sanitize_callback' => array( $this, 'hu_sanitize_html_text_input' ),
                'priority'  => 35,
                'notice'    => __( 'Note : Html is allowed. The following template tags can be used : {{year}}, {{site_title}}, {{home_url}}.', 'hueman-pro')
          ),
          'credit' => array(
                'control'   => 'HU_controls',
                'default'   => 1,
                'label'     => __( 'Footer credit text', 'hueman-pro'),
                'type'      => 'nimblecheck',
                'section'   => 'footer_design_sec',
                'priority'  => 40,
                'transport' => 'postMessage'
          ),
          'sl-in-footer' => array(
                'default'   => 1,
                'control'   => 'HU_controls',
                'label'     => __('Display social links in the footer', 'hueman-pro'),
                'section'   => 'footer_design_sec',
                'type'      => 'nimblecheck',
                'priority'  => 50
          ),
      );
    }


    /******************************************************************************************************
    *******************************************************************************************************
    * PANEL : ADVANCED OPTIONS
    *******************************************************************************************************
    ******************************************************************************************************/
    /*-----------------------------------------------------------------------------------------------------
                                   CUSTOM CSS SECTION
    ------------------------------------------------------------------------------------------------------*/


    /***************************************************************
    * POPULATE PANELS
    ***************************************************************/
    /**
    * hook : hu_add_panel_map
    * @return  associative array of customizer panels
    */
    function hu_popul_panels_map( $panel_map ) {
      $_new_panels = array(
        'hu-general-panel' => array(
                  'priority'       => 10,
                  'capability'     => 'edit_theme_options',
                  'title'          => __( 'Web Page Design' , 'hueman-pro' ),
                  'czr_subtitle'   => __( 'Title, Logo, Fonts, Colors, Background, Socials, Links', 'hueman-pro'),
                  'description'    => __( "General settings for the Hueman theme : design, comments, mobile, ..." , 'hueman-pro' ),
                  'type'           => 'hu_panel'
        ),
        'hu-header-panel' => array(
                  'priority'       => 20,
                  'capability'     => 'edit_theme_options',
                  'title'          => __( 'Header Design' , 'hueman-pro' ),
                  'czr_subtitle'   => __( 'Header Image, Menu, Widget', 'hueman-pro'),
                  'description'    => __( "Header settings for the Hueman theme." , 'hueman-pro' ),
                  'type'           => 'hu_panel'
        ),
        'hu-content-panel' => array(
                  'priority'       => 30,
                  'capability'     => 'edit_theme_options',
                  'title'          => __( 'Main Body Design' , 'hueman-pro' ),
                  'czr_subtitle'   => __( 'Layout, Sidebars, Blog Posts, Thumbnails', 'hueman-pro'),
                  'description'    => __( "Content settings for the Hueman theme." , 'hueman-pro' ),
                  'type'           => 'hu_panel'
        ),
        // 'hu-sidebars-panel' => array(
        //           'priority'       => 30,
        //           'capability'     => 'edit_theme_options',
        //           'title'          => __( 'Sidebars' , 'hueman' ),
        //           'description'    => __( "Sidebars settings for the Hueman theme." , 'hueman' )
        // ),
        'hu-footer-panel' => array(
                  'priority'       => 40,
                  'capability'     => 'edit_theme_options',
                  'title'          => __( 'Footer Design' , 'hueman-pro' ),
                  'czr_subtitle'   => __( 'Logo, Layout, Menu', 'hueman-pro'),
                  'description'    => __( "Footer settings for the Hueman theme." , 'hueman-pro' ),
                  'type'           => 'hu_panel'
        ),
        'hu-advanced-panel' => array(
                  'priority'       => 1000,
                  'capability'     => 'edit_theme_options',
                  'title'          => __( 'Advanced options' , 'hueman-pro' ),
                  'czr_subtitle'   => __( 'Performances, SEO, CSS, Scroll', 'hueman-pro'),
                  'description'    => __( "Advanced settings for the Hueman theme." , 'hueman-pro' ),
                  'type'           => 'hu_panel'
        )
      );
      return array_merge( $panel_map, $_new_panels );
    }





    /***************************************************************
    * POPULATE REMOVE SECTIONS
    ***************************************************************/
    /**
     * removes default WP sections
     * hook : hu_remove_section_map
     */
    function hu_popul_remove_section_map( $_sections ) {
      //customizer option array
      $remove_section = array(
        'nav',
        'title_tagline'
      );
      return array_merge( $_sections, $remove_section );
    }



    /***************************************************************
    * HANDLES THE THEME SWITCHER (since WP 4.2)
    ***************************************************************/
    /**
    * Print the themes section (themes switcher) when previewing the themes from wp-admin/themes.php
    * hook : hu_remove_section_map
    */
    function hu_set_theme_switcher_visibility( $_sections) {
      //Don't do anything is in preview frame
      //=> because once the preview is ready, a postMessage is sent to the panel frame to refresh the sections and panels
      //Do nothing if WP version under 4.2
      global $wp_version;
      if ( !version_compare( $wp_version, '4.2', '>=') )
        return $_sections;

      if ( isset($_GET['theme']) && is_array($_sections) ) {
        array_push( $_sections, 'themes');
        return $_sections;
      }

      //when user access the theme switcher from the admin bar
      $_theme_switcher_requested = false;
      if ( isset( $_GET['autofocus'] ) ) {
        $autofocus = wp_unslash( $_GET['autofocus'] );
        if ( is_array( $autofocus ) && isset($autofocus['section']) ) {
          $_theme_switcher_requested = 'themes' == $autofocus['section'];
        }
      }

      if (  !is_array($_sections) || $_theme_switcher_requested )
        return $_sections;

      array_push( $_sections, 'themes');
      return $_sections;
    }




    /***************************************************************
    * POPULATE SECTIONS
    ***************************************************************/
    /**
    * hook : hu_add_section_map
    */
    function hu_popul_section_map( $_sections ) {
      $_new_sections = array(
        /*---------------------------------------------------------------------------------------------
        -> PANEL : GENERAL
        ----------------------------------------------------------------------------------------------*/
        //the title_tagline section holds the default WP setting for the Site Title and the Tagline
        //This section has been previously removed from its initial location and is added back here
        'title_tagline'         => array(
              'title'    => __( 'Site Identity : Logo, Title, Tagline and Site Icon', 'hueman-pro' ),
              'priority' => 10,
              'panel'   => 'hu-general-panel',
              'section_class' => 'HU_Customize_Sections',
              'ubq_panel'   => array(
                  'panel' => 'hu-header-panel',
                  'priority' => '1'
              )
        ),
        'general_design_sec'         => array(
              'title'    => __( 'General Design Options : Font, Colors, ...', 'hueman-pro' ),
              'priority' => 20,
              'panel'   => 'hu-general-panel'
        ),
        // Since June 2018, this section is registered dynamically
        // 'social_links_sec'         => array(
        //       'title'    => __( 'Social links', 'hueman' ),
        //       'priority' => 30,
        //       'panel'   => 'hu-general-panel'
        // ),



        /*---------------------------------------------------------------------------------------------
        -> PANEL : HEADER
        ----------------------------------------------------------------------------------------------*/
        'header_design_sec'         => array(
              'title'    => __( "Header Design : colors and others", 'hueman-pro' ),
              'priority' => 10,
              'panel'   => 'hu-header-panel'
        ),
        'header_image_sec'         => array(
              'title'    => __( 'Header Image', 'hueman-pro' ),
              'priority' => 30,
              'panel'   => 'hu-header-panel'
        ),
        'header_widget_sec'         => array(
              'title'    => __( 'Header Advertisement Widget', 'hueman-pro' ),
              'priority' => 20,
              'panel'   => 'hu-header-panel'
        ),
        'header_menus_sec'          => array(
              'title'    => __( 'Header Menus : mobile settings, scroll behaviour, search button', 'hueman-pro' ),
              'priority' => 40,
              'panel'   => 'hu-header-panel'
        ),

        /*---------------------------------------------------------------------------------------------
        -> PANEL : CONTENT
        ----------------------------------------------------------------------------------------------*/
        'content_layout_sec'         => array(
              'title'    => __( 'Column layout for the main content', 'hueman-pro' ),
              'priority' => 10,
              'panel'   => 'hu-content-panel'
        ),
        'sidebars_design_sec'         => array(
              'title'    => __( 'Sidebars : Design and Mobile Settings', 'hueman-pro' ),
              'priority' => 20,
              'panel'   => 'hu-content-panel'
        ),
        'content_blog_sec'         => array(
              'title'    => __( 'Post Lists Design and Content : Blog, Archives, Search Results', 'hueman-pro' ),
              'priority' => 30,
              'panel'   => 'hu-content-panel',
              //'active_callback' => 'hu_is_post_list'
        ),
        'content_page_sec'         => array(
            'title'    => __( 'Single Pages Settings', 'hueman-pro' ),
            'priority' => 35,
            'panel'   => 'hu-content-panel',
        ),
        'content_single_sec'         => array(
              'title'    => __( 'Single Posts Settings', 'hueman-pro' ),
              'priority' => 40,
              'panel'   => 'hu-content-panel',
              //'active_callback' => function_exists('HU_AD') ? 'hu_is_single' : ''
        ),
        'content_thumbnail_sec'         => array(
              'title'    => __( 'Thumbnails Settings', 'hueman-pro' ),
              'priority' => 50,
              'panel'   => 'hu-content-panel'
        ),
        'comments_sec'         => array(
              'title'    => __( 'Comments', 'hueman-pro' ),
              'priority' => 60,
              'panel'   => 'hu-content-panel',
              //'active_callback' => 'hu_is_singular'
        ),

        /*---------------------------------------------------------------------------------------------
        -> PANEL : FOOTER
        ----------------------------------------------------------------------------------------------*/
        'footer_design_sec'         => array(
              'title'    => __( 'Footer Design : Logo, layout, ...', 'hueman-pro' ),
              'priority' => 10,
              'panel'   => 'hu-footer-panel'
        ),



                /*---------------------------------------------------------------------------------------------
        -> PANEL : ADVANCED
        ----------------------------------------------------------------------------------------------*/
        // Removed in march 2020
        // 'smoothscroll_sec'         => array(
        //       'title'    => __( 'Smooth Scroll', 'hueman' ),
        //       'priority' => 10,
        //       'panel'   => 'hu-advanced-panel'
        // ),
        'mobiles_sec'         => array(
              'title'    => __( 'Mobile Devices', 'hueman-pro' ),
              'priority' => 20,
              'panel'   => 'hu-advanced-panel'
        ),
        'search_sec'         => array(
              'title'    => __( 'Search Results', 'hueman-pro' ),
              'priority' => 25,
              'panel'   => 'hu-advanced-panel'
        ),
        'performance_sec'         => array(
              'title'    => __( 'Performances and SEO', 'hueman-pro' ),
              'priority' => 30,
              'panel'   => 'hu-advanced-panel'
        ),
        'admin_sec'         => array(
              'title'    => __( 'Hueman Admin Settings', 'hueman-pro' ),
              'priority' => 50,
              'panel'   => 'hu-advanced-panel'
        )

      );

      if ( hu_is_pro_section_on() ) {
          //GO PRO SECTION
          $_sections = array_merge(
              array(
                  'go_pro_sec'   => array(
                      'title'         => esc_html__( 'Upgrade to Hueman Pro', 'hueman-pro' ),
                      'pro_text'      => esc_html__( 'Go Pro', 'hueman-pro' ),
                      'pro_url'       => esc_url('presscustomizr.com/hueman-pro') . '?ref=c&utm_source=usersite&utm_medium=link&utm_campaign=hueman-customizer-btn',
                      'priority'      => 0,
                      'section_class' => 'HU_Customize_Section_Pro',
                      'active_callback' => array( $this, 'hu_pro_section_active_cb' )
                  )
              ),
              $_sections
          );
      }

      return array_merge( $_sections, $_new_sections );
    }






    /***************************************************************
    * CONTROLS HELPERS
    ****************************************************************
    /*
    * @return array() of cat
    */
    function hu_get_the_cat_list() {
      $list = array(
        "0" => sprintf('-- %1$s --', __('Choose one ', 'hueman-pro') )
      );
      foreach ( get_categories() as $key => $cat) {
        $_id = $cat -> term_id;
        $list[$_id] = $cat -> name;
      }
      return $list;
    }



    /*
    * @return array() of layouts
    * adds an 'inherit' item if requested is not global
    */
    function hu_get_content_layout_choices( $_wot = null ) {
      $_layouts = array(
        'col-1c' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-1c.png',
          'label' => __( '1 Column' , 'hueman-pro' )
        ),
        'col-2cl'=> array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-2cl.png',
          'label' => __( '2 Columns - Content Left' , 'hueman-pro' )
        ),
        'col-2cr'=> array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-2cr.png',
          'label' => __( '2 Columns - Content Right' , 'hueman-pro' )
        ),
        'col-3cm'=> array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-3cm.png',
          'label' => __( '3 Columns - Content Middle' , 'hueman-pro' )
        ),
        'col-3cl'=> array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-3cl.png',
          'label' => __( '3 Columns - Content Left' , 'hueman-pro' )
        ),
        'col-3cr'=> array(
          'src' => get_template_directory_uri() . '/assets/admin/img/col-3cr.png',
          'label' => __( '3 Columns - Content Right' , 'hueman-pro' )
        )
      );
      if ( 'global' != $_wot )
        return array_merge(
          array(
            'inherit' => array(
              'src' => get_template_directory_uri() . '/assets/admin/img/layout-off.png',
              'label' => __( 'Inherit Global Layout' , 'hueman-pro' )
            )
          ),
          $_layouts
        );
      return $_layouts;
    }



    /*
    * @return array() of layouts
    * adds an 'inherit' item if requested is not global
    */
    function hu_get_footer_layout_choices( $_wot = null ) {
      $_layouts = array(
        '0' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/footer-widgets-0.png',
          'label' => __( 'Disable' , 'hueman-pro' )
        ),
        '1' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/footer-widgets-1.png',
          'label' => __( '1 Column' , 'hueman-pro' )
        ),
        '2' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/footer-widgets-2.png',
          'label' => __( '2 Columns' , 'hueman-pro' )
        ),
        '3' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/footer-widgets-3.png',
          'label' => __( '3 Columns' , 'hueman-pro' )
        ),
        '4' => array(
          'src' => get_template_directory_uri() . '/assets/admin/img/footer-widgets-4.png',
          'label' => __( '4 Columns' , 'hueman-pro' )
        ),
      );

      return $_layouts;
    }


    /*
    * @return array() of registered sidebars : id => label
     */
    /* DEPRECATED */
    function hu_get_widget_areas() {
      global $wp_registered_sidebars;

      $sidebars = array();
      foreach( $wp_registered_sidebars as $id => $sidebar ) {
        $sidebars[ $id ] = $sidebar[ 'name' ];
      }

      $sidebars = apply_filters( 'hu_authorized_sidebars', $sidebars );
      $_to_return = array();

      //return no sidebars if empty
      if ( !count( $sidebars ) ) {
        $_to_return['no-sidebars'] = '-- ' . __( 'No Sidebars', 'hueman-pro' )  . ' --';
        return $_to_return;
      }

      //else populate the array
      $_to_return[] = '-- ' . __( 'Choose Sidebar', 'hueman-pro' ) . ' --';

      foreach ( $sidebars as $id => $sidebar ) {
        $id = esc_attr( $id );
        $_to_return[$id] = esc_attr( $sidebar );
      }

      return $_to_return;
    }




    /***************************************************************
    * SANITIZATION HELPERS
    ***************************************************************/
    /**
     * adds sanitization callback funtion : textarea
     * @package Hueman
     * @since Hueman 3.3.0
     */
    function hu_sanitize_textarea( $value) {
      return esc_html( $value);
    }


    /**
     * adds sanitization callback for input including html
     * @package Hueman
     * @since Hueman 3.3.0
     */
    function hu_sanitize_html_text_input( $value) {
      return wp_kses_post( force_balance_tags( $value ) );
    }


    /**
     * adds sanitization callback funtion : number
     * @package Hueman
     * @since Hueman 3.3.0
     */
    function hu_sanitize_number( $value) {
      if ( !$value || is_null($value) )
        return $value;
      $value = esc_attr( $value); // clean input
      $value = (int) $value; // Force the value into integer type.
        return ( 0 < $value ) ? $value : null;
    }




    /**
     * adds sanitization callback funtion : url
     * @package Hueman
     * @since Hueman 3.3.0
     */
    function hu_sanitize_url( $value) {
      return esc_url( $value);
    }

    /**
     * adds sanitization callback funtion : email
     * @package Hueman
     * @since Hueman 3.3.0
     */
    function hu_sanitize_email( $value) {
      return sanitize_email( $value );
    }


    /**
    * active callback of section 'hueman_go_pro'
    * @return  bool
    */
    function hu_pro_section_active_cb() {
        return !hu_isprevdem();
    }






    /********************************************************************************************
    ************ TEMPORARY
    *********************************************************************************************/
    //temporary fix for the background color before final move in the customizer
    function hu_sanitize_bg_color( $value ) {
      if ( is_array($value) ) {
        $color = isset($color['body-background']) ? $color['body-background'] : '#eaeaea';
      }
      if ( $unhashed = sanitize_hex_color_no_hash( $color ) )
        return '#' . $unhashed;

      return $color;
    }


    /**
     * Ensures that any hex color is properly hashed.
     * Otherwise, returns value untouched.
     *
     * This method should only be necessary if using sanitize_hex_color_no_hash().
     *
     * @since 3.4.0
     *
     * @param string $color
     * @return string
     */
    function hu_sanitize_js_body_bg( $color ) {
      if ( is_array($color) ) {
        $color = isset($color['body-background']) ? $color['body-background'] : '#eaeaea';
      }
      if ( $unhashed = sanitize_hex_color_no_hash( $color ) )
        return '#' . $unhashed;

      return $color;
    }

    /********************************************************************************************
    ************ / TEMPORARY
    *********************************************************************************************/


    /**
    * Change upload's path to relative instead of absolute
    * @package Hueman
    * @since Hueman 3.3.0
    */
    function hu_sanitize_uploads( $url ) {
      $upload_dir = wp_upload_dir();
      return str_replace($upload_dir['baseurl'], '', $url);
    }

  }//end of class
endif;
