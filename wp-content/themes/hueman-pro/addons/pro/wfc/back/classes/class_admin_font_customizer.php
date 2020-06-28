<?php

class TC_admin_font_customizer {
	static $instance;

	public $font_weight_list;
	public $font_style_list;
	public $text_align_list;
	public $text_decoration_list;
	public $text_transform_list;
	public $tc_zone_map;
	public $tc_control_tree;
	public $tc_static_effect_list;
	public $tc_control_zones;

	function __construct () {
		self::$instance =& $this;
		if ( TC_wfc::$instance -> tc_is_customizing() ) {
			// Schedule the loading of the the control template on 'customize_controls_print_footer_scripts'
			require_once(  dirname( dirname( __FILE__ ) ) . '/czr_tmpl/wfc-module-tmpl.php' );
			// Load WFC_Customize_Modules control class extends WP_Customize_Control
			require_once(  dirname( __FILE__ ) . '/class-wfc-customizer-control.php' );
		}

		//loads the custom control classes and settings
		add_action ( 'customize_register'						        , array( $this , 'tc_customize_register' ) , 20, 1 );
		//control scripts
		add_action ( 'customize_controls_enqueue_scripts'		, array( $this , 'tc_plugin_controls_css' ) );
		add_action ( 'customize_controls_enqueue_scripts'		, array( $this , 'tc_plugin_controls_js' ), 20 );
		//preview scripts
		add_action ( 'customize_preview_init'					      , array( $this , 'tc_plugin_preview_js' ));
		//save last modif date in db
		add_action ( 'customize_save_after'       				  , array( $this , 'tc_db_actions') , 10 );

    	////DEPRECATED IN JUNE 2017 adds dynstyle to WP editor
		//add_action ( 'after_setup_theme'                    , array( $this , 'tc_add_editor_style' ), 100 );

		add_filter ( 'plugin_action_links' 						      , array( $this , 'tc_plugin_action_links' ), 10 , 2 );

		$this -> tc_static_effect_list 							= $this -> tc_get_static_effect_list();

        $this -> font_weight_list = array(
				'normal' 	=> __( 'normal', 'hueman-pro' ),
				'bold' 		=> __( 'bold', 'hueman-pro' ),
				'bolder' 	=> __( 'bolder', 'hueman-pro' ),
				'lighter' 	=> __( 'lighter', 'hueman-pro' ),
				100 		=> 100,
				200 		=> 200,
				300 		=> 300,
				400 		=> 400,
				500 		=> 500,
				600 		=> 600,
				700 		=> 700,
				800 		=> 800,
				900 		=> 900
		);

		$this -> font_style_list = array(
				'inherit' 	=> __( 'inherit', 'hueman-pro' ),
				'italic' 	=> __( 'italic', 'hueman-pro' ),
				'normal'	=> __( 'normal', 'hueman-pro' ),
				'oblique'	=> __( 'oblique', 'hueman-pro' )
		);

		$this -> text_align_list = array(
				'center' 	=> __( 'center', 'hueman-pro' ),
				'justify' 	=> __( 'justify', 'hueman-pro' ),
				'inherit' 	=> __( 'inherit', 'hueman-pro' ),
				'left' 		=> __( 'left', 'hueman-pro' ),
				'right' 	=> __( 'right', 'hueman-pro' )
		);

		$this -> text_decoration_list =  array(
				'none'			=> __( 'none', 'hueman-pro' ),
				'inherit'		=> __( 'inherit', 'hueman-pro' ),
				'line-through' => __( 'line-through', 'hueman-pro' ),
				'overline'		=> __( 'overline', 'hueman-pro' ),
				'underline'		=> __( 'underline', 'hueman-pro' )
		);

		$this -> text_transform_list =  array(
				'none'			=> __( 'none', 'hueman-pro' ),
				'inherit'		=> __( 'inherit', 'hueman-pro' ),
				'capitalize' 	=> __( 'capitalize', 'hueman-pro' ),
				'uppercase'		=> __( 'uppercase', 'hueman-pro' ),
				'lowercase'		=> __( 'lowercase', 'hueman-pro' )
		);

	}//end of construct













	/***********************************************
	**************** CUSTOMIZER ********************
	************************************************/
  // hook : 'customize_register'
  // @uses TC_utils_wfc::$instance -> tc_customizer_map()
	function tc_customize_register( $wp_customize) {
		return $this -> tc_customize_factory (
        $wp_customize ,
        $args = $this -> tc_customize_arguments(),
        $setup = TC_utils_wfc::$instance -> tc_customizer_map()
    );
	}

	/**
	 * Generates customizer
	 */
	function tc_customize_factory ( $wp_customize , $args, $setup ) {

		//remove sections
		if ( isset( $setup['remove_section'])) {
			foreach ( $setup['remove_section'] as $section) {
				$wp_customize	-> remove_section( $section);
			}
		}

		//add sections
		if ( isset( $setup['add_section'])) {
			foreach ( $setup['add_section'] as  $key => $options) {
				//generate section array
				$option_section = array();

				foreach( $args['sections'] as $sec) {
					$option_section[$sec] = isset( $options[$sec]) ?  $options[$sec] : null;
				}

				//add section
				$wp_customize	-> add_section( $key,$option_section);
			}//end foreach
		}//end if


		//get_settings
		if ( isset( $setup['get_setting'])) {
			foreach ( $setup['get_setting'] as $setting) {
				$wp_customize	-> get_setting( $setting )->transport = 'postMessage';
			}
		}

		//add settings and controls
		if ( isset( $setup['add_setting_control'])) {

			foreach ( $setup['add_setting_control'] as $key => $options) {
				//isolates the option name for the setting's filter
				$f_option_name = 'setting';
				$f_option = preg_match_all( '/\[(.*?)\]/' , $key , $match );
	            if ( isset( $match[1][0] ) ) {$f_option_name = $match[1][0];}

				//declares settings array
				$option_settings = array();
				foreach( $args['settings'] as $set => $set_value) {
					if ( $set == 'setting_type' ) {
						$option_settings['type'] = isset( $options['setting_type']) ?  $options['setting_type'] : $args['settings'][$set];
						$option_settings['type'] = apply_filters( $f_option_name .'_customizer_set', $option_settings['type'] , $set );
					}
					else {
						$option_settings[$set] = isset( $options[$set]) ?  $options[$set] : $args['settings'][$set];
						$option_settings[$set] = apply_filters( $f_option_name .'_customizer_set' , $option_settings[$set] , $set );
					}
				}

				//add setting
				$wp_customize	-> add_setting( $key, $option_settings );

				//generate controls array
				$option_controls = array();
				foreach( $args['controls'] as $con) {
					$option_controls[$con] = isset( $options[$con]) ?  $options[$con] : null;
				}

				//add control with a dynamic class instanciation if not default
				if(!isset( $options['control'])) {
						$wp_customize	-> add_control( $key,$option_controls );
				}
				else {
						$wp_customize	-> add_control( new $options['control']( $wp_customize, $key, $option_controls ));
				}

			}//end for each
		}//end if isset
	}//end of customize generator function



	function tc_customize_arguments() {
		$args = array(
				'sections' => array(
							'title' ,
							'priority' ,
							'description'
				),
				'settings' => array(
							'default'			=>	null,
							'capability'		=>	'manage_options' ,
							'setting_type'		=>	'option' ,
							'sanitize_callback'	=>	null,
							'transport'			=>	null
				),
				'controls' => array(
							'title' ,
							'text' ,
							'label' ,
							'section' ,
							'settings' ,
							'type' ,

              'module_type',

							'choices' ,
							'priority' ,
							'sanitize_callback' ,
							'notice' ,
							'buttontext' ,//button specific
							'link' ,//button specific
							'step' ,//number specific
							'min' ,//number specific
							'range-input' ,
							'max',
							'dropdown-posts-pages',
							'savedsettings',
							'selector'
				)
		);
		return apply_filters( 'fpc_customizer_arguments', $args );
	}




	/* SANITIZATION */
	function _cleanInput( $input , $type = null ) {
	  	$search = array(
		    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
		    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
	  	);
	    $output = preg_replace($search,'', $input);
	    //replace all non alphanumerical by _
	    $output = ( is_null($type) || 'name' != $type ) ? preg_replace('/[^A-Za-z0-9]/', '_' , $output) : preg_replace('/[^,;a-zA-Z0-9_-]|[,;]$/s', ' ', $output);
	    return $output;
	}


	function _sanitize( $input ) {
	    if ( is_array($input)) {
	        foreach($input as $var => $val) {
	            $output[$var] = $this -> _sanitize($val);
	        }
	    }
	    else {
	        if ( get_magic_quotes_gpc() ) {
	            $input = stripslashes($input);
	        }
	        $input  = $this -> _cleanInput($input);
	        $output = esc_sql($input);
	    }
	    return $output;
	}



	function tc_get_static_effect_list() {
  		return 	apply_filters( 'tc_static_effect_list',
  				array(
  					//key => effect name, class, recommended color
  					'none'				=> array( __('No effect' , 'hueman-pro' ) , 'no-effect', ''),
  					'emboss' 			=> array('Emboss', 'font-effect-emboss', '#ddd'),
  					'3d-one' 			=> array( __('3D one' , 'hueman-pro' ) , 'font-effect-3d-one', '#fff'),
  					'3d-two' 			=> array( __('3D two' , 'hueman-pro' ) , 'font-effect-3d-two', '#555'),
  					'3d-float' 			=> array('3D-float', 'font-effect-3d-float', '#fff'),
  					'static' 			=> array('Static', 'font-effect-static', '#343956'),
  					'outline' 			=> array('Outline', 'font-effect-outline', '#fff'),
  					'shadow-soft' 		=> array( __('Shadow soft' , 'hueman-pro' ) , 'font-effect-shadow-soft', '#5a5a5a'),
  					'shadow-simple' 	=> array( __('Shadow simple' , 'hueman-pro' ) , 'font-effect-shadow-simple', '#5a5a5a'),
  					'shadow-distant' 	=> array( __('Shadow distant' , 'hueman-pro' ) , 'font-effect-shadow-distant', '#5a5a5a'),
  					'shadow-close-one' 	=> array( __('Shadow close one' , 'hueman-pro' ) , 'font-effect-shadow-close-one', '#5a5a5a'),
  					'shadow-close-two' 	=> array( __('Shadow close two' , 'hueman-pro' ) , 'font-effect-shadow-close-two', '#5a5a5a'),
  					'shadow-multiple' 	=> array( __('Shadow multiple' , 'hueman-pro' ) , 'font-effect-shadow-multiple', '#222'),
  					'vintage-retro' 	=> array( __('Vintage retro' , 'hueman-pro' ) , 'font-effect-vintage-retro', '#5a5a5a'),
  					'neon-blue' 		=> array( __('Neon blue' , 'hueman-pro' ) , 'font-effect-neon-blue', '#fff'),
  					'neon-green' 		=> array( __('Neon green' , 'hueman-pro' ) , 'font-effect-neon-green', '#fff'),
  					'neon-orange' 		=> array( __('Neon orange' , 'hueman-pro' ) , 'font-effect-neon-orange', '#fff'),
  					'neon-pink' 		=> array( __('Neon pink' , 'hueman-pro' ) , 'font-effect-neon-pink', '#fff'),
  					'neon-red' 			=> array( __('Neon red' , 'hueman-pro' ) , 'font-effect-neon-red', '#fff'),
  					'neon-grey' 		=> array( __('Neon grey' , 'hueman-pro' ) , 'font-effect-neon-grey', '#fff'),
  					'neon-black' 		=> array( __('Neon black' , 'hueman-pro' ) , 'font-effect-neon-black', '#fff'),
  					'neon-white' 		=> array( __('Neon white' , 'hueman-pro' ) , 'font-effect-neon-white', '#fff'),
  					'fire' 				=> array('Fire', 'font-effect-fire', '#ffe'),
  					'fire-animation' 	=> array('Fire Animation', 'font-effect-fire-animation', '#ffe'),
  					'anaglyph' 			=> array('Anaglyph', 'font-effect-anaglyph', ''),
  					'inset' 			=> array('Inset', 'font-effect-inset', '#555'),
  					'brick-sign' 		=> array('Brick Sign', 'font-effect-brick-sign', '#600'),
  					'canvas-print' 		=> array('Canvas Print', 'font-effect-canvas-print', '#7A5C3E'),
  					'crackle' 			=> array('Crackle', 'font-effect-crackle', '#963'),
  					'decaying' 			=> array('Decaying', 'font-effect-decaying', '#958e75'),
  					'destruction' 		=> array('Destruction', 'font-effect-destruction', '#e10707'),
  					'distressed' 		=> array('Distressed', 'font-effect-distressed', '#306'),
  					'distressed-wood' 	=> array('Distressed Wood', 'font-effect-distressed-wood', '#4d2e0d'),
  					'fragile' 			=> array('Fragile', 'font-effect-fragile', '#663'),
  					'grass' 			=> array('Grass', 'font-effect-grass', '#390'),
  					'ice' 				=> array('Ice', 'font-effect-ice', '#0cf'),
  					'mitosis' 			=> array('Mitosis', 'font-effect-mitosis', '#600'),
  					'putting-green' 	=> array('Putting green', 'font-effect-putting-green', '#390'),
  					'scuffed-steel' 	=> array('Scuffed Steel', 'font-effect-scuffed-steel', '#acacac'),
  					'splintered' 		=> array('Splintered', 'font-effect-splintered', '#5a3723'),
  					'stonewash' 		=> array('Stonewash', 'font-effect-stonewash', '#343956'),
  					'vintage' 			=> array('Vintage', 'font-effect-vintage', '#db8'),
  					'wallpaper' 		=> array('Wallpaper', 'font-effect-wallpaper', '#9c7')
  				)
  		);//end of filter
	}



	/**
	 *  Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 * @package Customizr
	 * @since Customizr 1.0
	 */
	function tc_plugin_preview_js() {
  		wp_enqueue_script(
    			'font-customizer-preview' ,
          sprintf('%1$s/back/assets/js/font-customizer-preview%2$s.js' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
    			array( 'customize-preview' ),
    			( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
    			true
      );

  		wp_localize_script(
          'font-customizer-preview',
          'TCFontPreview',
            array(
                'DefaultSettings'				=> TC_wfc::$instance -> tc_get_selector_list(),
                'DBSettings' 					=> TC_wfc::$instance -> tc_get_saved_option( null , false )
            )
    	);
	}



    /**
    * Adds CSS scripts to controls
    */
    function tc_plugin_controls_css() {

        wp_enqueue_style(
            'font-customizer-fontselect-style',
            sprintf('%1$s/back/assets/css/fontselect%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            array( 'customize-controls' ),
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : TC_wfc::$instance -> plug_version,
            $media = 'all'
        );

        //loads the jquery plugins CSS assets when is :
        //1) any theme different than customizr-pro or hueman-pro
        //EXCLUDING
        //2) customizr version >= 3.2.5
        //3) hueman version >= 3.0.0
      if ( ! in_array( TC_wfc::$theme_name, array( 'customizr-pro', 'hueman-pro' ) ) &&
        ! ( ( 'hueman' == TC_wfc::$theme_name && defined( 'HUEMAN_VER' ) && version_compare( HUEMAN_VER, '3.0.0', '>=' ) )
            || ('customizr' == TC_wfc::$theme_name && version_compare( CUSTOMIZR_VER, '3.2.5', '>=' ) ) ) )  {

           //ICHECK
           // wp_enqueue_style(
           //    'wfc-icheck-style',
           //    sprintf('%1$s/back/assets/css/icheck%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
           //    array( 'customize-controls' ),
           //    ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
           //    $media = 'all'
           // );

           //SELECTER
           wp_enqueue_style(
              'wfc-selecter-style',
              sprintf('%1$s/back/assets/css/selecter%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
              array( 'customize-controls' ),
              ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
              $media = 'all'
           );

           //STEPPER
           wp_enqueue_style(
              'wfc-stepper-style',
               sprintf('%1$s/back/assets/css/stepper%2$s.css' , TC_WFC_BASE_URL, ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
               array( 'customize-controls' ),
               ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
               $media = 'all'
           );
        }//end of jquery CSS plugin assets

        //adds some nice google fonts to the customizer
        wp_enqueue_style(
            'customizer-google-fonts',
            $this-> tc_customizer_gfonts_url(array('Lobster Two' , 'Roboto' , 'PT Sans')),
            array( 'customize-controls' ),
            null
        );
    }


    /**
	* Adds JS scripts to controls
	*/
    function tc_plugin_controls_js() {
        //loads the jquery plugins CSS assets when is :
        //1) any theme different than customizr-pro
        //EXCLUDING
        //2) customizr version >= 3.2.5
        //3) hueman version >= 3.0.0
        if ( 'customizr-pro' != TC_wfc::$theme_name  &&
            ! ( ( 'hueman' == TC_wfc::$theme_name && defined( 'HUEMAN_VER' ) && version_compare( HUEMAN_VER, '3.0.0', '>=' ) )
              || ('customizr' == TC_wfc::$theme_name && version_compare( CUSTOMIZR_VER, '3.2.5', '>=' ) ) ) )  {

            // wp_enqueue_script(
            //    'icheck-script',
            //    //dev / debug mode mode?
            //    sprintf('%1$s/back/assets/js/lib/lib_icheck.js' , TC_WFC_BASE_URL),
            //    $deps = array('jquery'),
            //    ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
            //    $in_footer = true
            // );

            wp_enqueue_script(
               'selecter-script',
               //dev / debug mode mode?
               sprintf('%1$s/back/assets/js/lib/lib_selecter.js' , TC_WFC_BASE_URL),
               $deps = array('jquery'),
               ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
               $in_footer = true
            );

            wp_enqueue_script(
               'stepper-script',
               //dev / debug mode mode?
               sprintf('%1$s/back/assets/js/lib/lib_stepper.js' , TC_WFC_BASE_URL),
               $deps = array('jquery'),
               ( defined('WP_DEBUG') && true === WP_DEBUG ) ? TC_wfc::$instance -> plug_version . time() : TC_wfc::$instance -> plug_version,
               $in_footer = true
            );
      }//end of jquery CSS plugin assets

      // wp_register_script(
      //     'require',
      //     sprintf('%1$s/back/assets/js/require.js' , TC_WFC_BASE_URL),
      //     array('jquery'),
      //     null,
      //     $in_footer = true
      // );

      // $_app_on_server				      = dirname(dirname(__FILE__)) . '/assets/js/require/app.js';
      // $_app_path 					        = file_exists($_app_on_server) ? sprintf('%1$s/back/assets/js/%2$s' , TC_WFC_BASE_URL, 'require/app.js') : false;

      //displays unminified script in plugins dev mode only AND if unmified file exists!
      //checked with false === strpos( dirname( dirname( dirname (__FILE__) ) ) , 'addons/wfc' )
      $_use_unminified = defined('CZR_DEV')
          && true === CZR_DEV
         // && false === strpos( dirname( dirname( dirname (__FILE__) ) ) , 'addons/wfc' )
          && file_exists( sprintf( '%s/assets/js/font-customizer-control.js' , dirname( dirname( __FILE__ ) ) ) );

      $_prod_script_path          = sprintf(
          '%1$s/back/assets/js/%2$s' ,
          TC_WFC_BASE_URL,
          $_use_unminified ? 'font-customizer-control.js' : 'font-customizer-control.min.js'
      );

      wp_register_script(
          'font-customizer-control',
          //dev / debug mode mode?
          $_prod_script_path,
          //( apply_filters( 'wfc_require_app_js' , false ) && false != $_app_path ) ? $_app_path : $_prod_script_path,
          //$deps = array('customize-controls' , 'require', 'underscore'),
          array('customize-controls' , 'jquery', 'underscore'),
          ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : TC_wfc::$instance -> plug_version,
          $in_footer = true
      );
      wp_enqueue_script('font-customizer-control');

      $theme_name = TC_wfc::$theme_name;

      wp_localize_script(
          'font-customizer-control',
          'TCFontAdmin', apply_filters('tc_font_customizer_control_params',
            array(
                'DefaultSettings'				=> TC_wfc::$instance -> tc_get_selector_list(),
                'DBSettings' 					=> TC_wfc::$instance -> tc_get_saved_option( null , false ),
                'HasSavedSets'					=> TC_wfc::$instance -> tc_get_saved_option($selector = null , $bool = true),
                'fontCollection'      => array(
                    'cfonts' => TC_utils_wfc::$instance -> get_cfonts(),
                    'gfonts' => TC_utils_wfc::$instance -> get_gfonts(),
                    'subsets' => TC_utils_wfc::$instance -> get_gfonts('subsets')
                ),
                'selectOptionLists' => array(
                    'font-weight' => $this -> font_weight_list,
                    'font-style' => $this -> font_style_list,
                    'text-align' => $this -> text_align_list ,
                    'text-decoration' => $this -> text_decoration_list,
                    'text-transform' => $this -> text_transform_list,
                    'static-effect' => $this -> tc_static_effect_list
                ),
                'CFonts'						=> TC_utils_wfc::$instance -> get_cfonts_names(),

                'Translations'		 			=> array(
                	'reset_all_button' 	=> __('Reset all' , 'hueman-pro' ),
                	'reset_all_confirm'	=> __('All settings reset to default' , 'hueman-pro' ),
                	'reset_all_warning'	=> __('Are you sure you want to reset all your font settings to default?' , 'hueman-pro' ),
                	'reset_all_yes'		=> __('Yes' , 'hueman-pro' ),
                	'reset_all_no'		=> __('No' , 'hueman-pro' ),
                  'This selector has already been added.' => __('This selector has already been added.','hueman-pro' ),
                  'Custom' => __('Custom', 'hueman-pro' ),
                  'Please specify a CSS selector' => __( 'Please specify a CSS selector', 'hueman-pro' ),
                  'Select a font family' => __('Select a font family' , 'hueman-pro' ),
                  'Pre-defined selectors' => __( 'Pre-defined selectors', 'hueman-pro' ),
                  'Define a custom selector' => __( 'Define a custom selector', 'hueman-pro' ),
                  'Web Safe Fonts' => __( 'Web Safe Fonts', 'hueman-pro' ),
                  'Google Fonts' => __( 'Google Fonts', 'hueman-pro' ),
                  'Please confirm the removal of the customizations for' => __( 'Please confirm the removal of the customizations for', 'hueman-pro' ),
                  'Make sure to use valid css selectors.' => __( 'Make sure to use valid css selectors.', 'hueman-pro' ),
                  'This css selector is not valid.' => __( 'This css selector is not valid.', 'hueman-pro' ),
                  'This selector does not exist in this context.' => __('This selector does not exist in this context.', 'hueman-pro' ),
                  'Select' => __( 'Select', 'hueman-pro' )
                ),

                'AjaxUrl'          				=> admin_url( 'admin-ajax.php' ),
                'WFCNonce' 						=> wp_create_nonce( 'wfc-nonce' ),
                'HasCustomToAdd' 				=> get_transient( 'custom_selector_added' ),
                // NEW WFC
                'defaultModel' => TC_wfc::$instance -> default_model
            )
          )//end filter
      );

      //delete the transient after setting the js var HasCustomToAdd
	     delete_transient( 'custom_selector_added' );
    }



	/**
	* Builds Google Fonts url
	* @package Customizr
	* @since Customizr 3.1.1
	*/
	function tc_customizer_gfonts_url( $fonts = null ) {

      //declares the google font vars
      $fonts_url         = '';
      $fonts 			 = is_null($fonts) ? array('Raleway') : $fonts;
      $fonts 			 = is_array($fonts) ? $fonts : array($fonts);
      $font_families     = apply_filters( 'tc_customizer_google_fonts' , $fonts );

      $query_args        = array(
          'family' => implode( '|', $font_families ),
          //'subset' => urlencode( 'latin,latin-ext' ),
      );

      $fonts_url          = add_query_arg( $query_args, "//fonts.googleapis.com/css" );

      return $fonts_url;
    }


  // hook : 'customize_save_after'
	function tc_db_actions() {
        $dt             = new DateTime(null, new DateTimeZone('UTC'));
        $dt             = $dt->format('D, d M Y H:i:s \G\M\T');
        //updates last modified option
        update_option( 'tc_font_customizer_last_modified' , $dt );

        //update front end google fonts option
        TC_utils_wfc::$instance -> tc_update_front_end_gfonts();
  }



  	function tc_plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname( dirname( dirname(__FILE__) ) ). '/' . basename( TC_wfc::$instance -> plug_file ) ) ) {
			$links[] = '<a href="' . admin_url( 'customize.php' ) . '">'.__( 'Settings', 'hueman-pro' ).'</a>';
			$links[] = '<a href="' . admin_url( 'options.php?page=tc-system-info' ) . '">'.__('System Informations', 'hueman-pro').'</a>';
		}
		return $links;
	  }


	// Copy of czr_fn_get_controls_css_attr() and the equivalent in Hueman Pro
	function wfc_get_controls_css_attr() {
		return apply_filters('controls_css_attr',
			array(
				'multi_input_wrapper' => 'czr-multi-input-wrapper',
				'sub_set_wrapper'     => 'czr-sub-set',
				'sub_set_input'       => 'czr-input',
				'img_upload_container' => 'czr-imgup-container',

				'edit_modopt_icon'    => 'czr-toggle-modopt',
				'close_modopt_icon'   => 'czr-close-modopt',
				'mod_opt_wrapper'     => 'czr-mod-opt-wrapper',


				'items_wrapper'     => 'czr-items-wrapper',
				'single_item'        => 'czr-single-item',
				'item_content'      => 'czr-item-content',
				'item_header'       => 'czr-item-header',
				'item_title'        => 'czr-item-title',
				'item_btns'         => 'czr-item-btns',
				'item_sort_handle'   => 'czr-item-sort-handle',

				//remove dialog
				'display_alert_btn' => 'czr-display-alert',
				'remove_alert_wrapper'   => 'czr-remove-alert-wrapper',
				'cancel_alert_btn'  => 'czr-cancel-button',
				'remove_view_btn'        => 'czr-remove-button',

				'edit_view_btn'     => 'czr-edit-view',
				//pre add dialog
				'open_pre_add_btn'      => 'czr-open-pre-add-new',
				'adding_new'        => 'czr-adding-new',
				'pre_add_wrapper'   => 'czr-pre-add-wrapper',
				'pre_add_item_content'   => 'czr-pre-add-view-content',
				'cancel_pre_add_btn'  => 'czr-cancel-add-new',
				'add_new_btn'       => 'czr-add-new',
				'pre_add_success'   => 'czr-add-success'
			)
		);
	}

}//end of class