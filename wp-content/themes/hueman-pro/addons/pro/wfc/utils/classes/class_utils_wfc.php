<?php
class TC_utils_wfc {
	static $instance;
	public static $cfonts_list;
	public $tc_selector_title_map;
	public $default_options;
	public $is_customizing;

  public static $gfonts_decoded;

	function __construct () {
  		self::$instance 	=& $this;
  		self::$cfonts_list 	= array(
  						  'Arial Black,Arial Black,Gadget,sans-serif',
  						  'Century Gothic',
  					      'Comic Sans MS,Comic Sans MS,cursive',
  					      'Courier New,Courier New,Courier,monospace',
  					      'Georgia,Georgia,serif',
  					      'Helvetica Neue, Helvetica, Arial, sans-serif',
  					      'Impact,Charcoal,sans-serif',
  					      'Lucida Console,Monaco,monospace',
  					      'Lucida Sans Unicode,Lucida Grande,sans-serif',
  					      'Palatino Linotype,Book Antiqua,Palatino,serif',
  					      'Tahoma,Geneva,sans-serif',
  					      'Times New Roman,Times,serif',
  					      'Trebuchet MS,Helvetica,sans-serif',
  					      'Verdana,Geneva,sans-serif',
  		);//end of array;

      $this -> is_customizing   		= TC_wfc::$instance -> tc_is_customizing();

      self::$gfonts_decoded      = $this -> retrieve_decoded_gfonts();
	}//end of construct





    /**
    * Defines sections, settings and function of customizer and return and array
    * Also used to get the default options array, in this case $get_default_option = true and we DISABLE the __get_option (=>infinite loop)
    */
    function tc_customizer_map( $get_default_option = false ) {
    	//customizer option array
        $remove_section 				= array();//end of remove_sections array
        $add_section 					= array(
				                        'add_section'           =>   array(
				                                        'tc_font_customizer_settings'   => array(
				                                                                            'title'         =>  __( 'Font Customizer' , 'hueman-pro' ),
				                                                                            'priority'      =>  0,
				                                                                            'description'   =>  __( 'Play with beautiful fonts!' , 'hueman-pro' )
				                                        ),
				                        )
        );//end of add_sections array
        //specifies the transport for some options
        $get_setting 					= array();

        //was TC_wfc::$instance -> tc_font_customizer_plug[new_wfc]
        $tc_font_customizer_settings 	= array(
             TC_wfc::$opt_name => array(
                'default'   => array(),//empty items array by default
                'control'   => 'WFC_Customize_Modules',
                'label'     => __('Manage your customized text elements.', 'hueman-pro'),
                'description' => __( 'The New WFC' , 'hueman-pro'),
                'section'   => 'tc_font_customizer_settings',
                'type'      => 'czr_module',
                'module_type' => 'czr_wfc_module',
                'transport' => 'postMessage', //'refresh',
                //'sanitize_callback' => array( $this , 'tc_sanitize_before_db' ), //The name of the function that will be called to sanitize the input data before saving it to the database. Default: blank.
                'priority'  => 10
            ),
            // May 2020 option added for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
            TC_wfc::$opt_name . '_deactivated' => array(
                'default'   => 0,//empty items array by default
                //'control'   => 'WFC_Customize_Modules',
                'label'     => __('Check this option to deactivate Font Customizer ( and all related CSS and JS assets ) on this website.', 'hueman-pro'),
                //'description' => __( 'You can totally disable the Font Customizer by unchecking this option', 'wordpress_font_customizer'),
                'section'   => 'tc_font_customizer_settings',
                'type'      => 'checkbox',
                //'module_type' => 'czr_wfc_module',
                //'transport' => 'postMessage', //'refresh',
                //'sanitize_callback' => array( $this , 'tc_sanitize_before_db' ), //The name of the function that will be called to sanitize the input data before saving it to the database. Default: blank.
                'priority'  => 20
            ),
        );



    		$add_setting_control = array(
                        'add_setting_control'   =>   $tc_font_customizer_settings
        );
        $customizer_map = array_merge( $remove_section , $add_section , $get_setting , $add_setting_control );
        return apply_filters( 'wfc_customizer_map', $customizer_map );
    }





    function tc_get_selector_title_map() {
      if ( isset( $this -> tc_selector_title_map ) )
        return apply_filters( 'all_selectors_title_map' , $this -> tc_selector_title_map );

	  	  $default_map =  apply_filters(
        	'tc_default_selector_title_map',
        	array(
        		'body' 					=> __( 'Default website font' , 'hueman-pro' ),
        		'site_title'			=> __( 'Site title' , 'hueman-pro' ),
        		'site_description' 		=> __( 'Site description' , 'hueman-pro' ),
        		'menu_items' 			=> __( 'Menu items' , 'hueman-pro' ),
        		'slider_title' 			=> __( 'Slider title' , 'hueman-pro' ),
        		'slider_text' 			=> __( 'Slider text' , 'hueman-pro' ),
        		'slider_button' 		=> __( 'Slider button' , 'hueman-pro' ),
        		'fp_title' 				=> __( 'Featured pages title' , 'hueman-pro' ),
        		'fp_text' 				=> __( 'Featured pages text' , 'hueman-pro' ),
        		'fp_btn' 				=> __( 'Featured pages button' , 'hueman-pro' ),
        		'single_post_title' 	=> __( 'Single post/page titles' , 'hueman-pro' ),
        		'post_list_titles' 		=> __( 'Post list titles' , 'hueman-pro' ),
        		'archive_titles' 		=> __( 'Archive/Blog titles' , 'hueman-pro' ),
        		'post_content' 			=> __( 'Post content / excerpt' , 'hueman-pro' ),
        		'post_metas' 			=> __( 'Post metas' , 'hueman-pro' ),
        		'post_links' 			=> __( 'Links in post/pages' , 'hueman-pro' ),
        		'post_hone' 			=> __( 'H1 headings' , 'hueman-pro' ),
        		'post_htwo' 			=> __( 'H2 headings' , 'hueman-pro' ),
        		'post_hthree' 			=> __( 'H3 headings' , 'hueman-pro' ),
        		'post_hfour' 			=> __( 'H4 headings' , 'hueman-pro' ),
        		'post_hfive' 			=> __( 'H5 headings' , 'hueman-pro' ),
        		'post_hsix' 			=> __( 'H6 headings' , 'hueman-pro' ),
        		'blockquote' 			=> __( 'Blockquotes' , 'hueman-pro' ),
        		'comment_title' 		=> __( 'Comments title' , 'hueman-pro' ),
        		'comment_author' 		=> __( 'Comments author' , 'hueman-pro' ),
        		'comment_content'		=> __( 'Comments content' , 'hueman-pro' ),
        		'sidebars_widget_title' => __( 'Sidebar widget titles' , 'hueman-pro' ),
        		'sidebars_links' 		=> __( 'Links in sidebars' , 'hueman-pro' ),
        		'footer_widget_title' 	=> __( 'Widget titles' , 'hueman-pro' ),
        		'footer_credits' 		=> __( 'Footer credits' , 'hueman-pro' ),
            'footer_credits_links'    => __( 'Footer credits links' , 'hueman-pro' )
        	)//end of array
		);//end of filter

		$theme_name 				= TC_wfc::$theme_name;
    $_opt_prefix        = TC_wfc::$instance -> plug_option_prefix;
		//returns default if no customs
		if ( ! get_option( "{$_opt_prefix}_customs_{$theme_name}" ) )
			return $default_map;

		$customs 					= get_option( "{$_opt_prefix}_customs_{$theme_name}" );
		$custom_map 				= array();
		foreach ($customs as $id => $data) {
			$custom_map[$id] 		= isset($data['title']) ? $data['title'] : $id;
		}

    $this -> tc_selector_title_map = array_merge( $default_map , $custom_map );
		return apply_filters( 'all_selectors_title_map' , $this -> tc_selector_title_map );
	}



  //Note : this will be fired by $setting -> sanitize()
  //if a wp_error or null is returned => will abort the save process
  function tc_sanitize_before_db( $values_to_save ) {
		//fired only when necessary
		if ( ! isset($_POST['action'] ) || ( isset($_POST['action']) && 'customize_save' != $_POST['action'] ) )
			return true;

		if ( empty($values_to_save) )
			return $values_to_save;

		$values_to_save = (array)json_decode($values_to_save);

		foreach ($values_to_save as $setting_type => $value) {
			switch ( $setting_type ) {
				case 'font-size' :
				case 'line-height' :
				case 'letter-spacing' :
					//number input have to be 2 digits (max) and 2 letters
					$value 		= esc_attr( $value); // clean input
					$unit 		= 'px';
					$unit 		= ( false != strpos($value,'px') ) ? 'px' : 'em';
					$split 		= explode( $unit , $value );
					$values_to_save[$setting_type] 		= (int) $split[0] . $unit; // Force the value into integer type and adds the unit.
				break;

				case 'color' :
				case 'color-hover' :
					$values_to_save[$setting_type] = '#' . sanitize_hex_color_no_hash($value);
				break;

				default :
					//to do very secure => check if entry exist in list
					$values_to_save[$setting_type] = sanitize_text_field($value);
				break;
			}
		}
		return json_encode($values_to_save);
	}


	function get_cfonts() {
		$cfonts = array();
		foreach ( self::$cfonts_list as $font ) {
			//no subsets for cfonts => epty array()
			$cfonts[] = array(
				'name' 		=> $font ,
				'subsets' 	=> array()
			);
		}
		return apply_filters( 'tc_font_customizer_cfonts', $cfonts );
	}

    function get_cfonts_names(){
      $cfonts = $this -> get_cfonts();
      return array_map( array( $this, 'get_font_property') , $cfonts, array_fill( 0, sizeof($cfonts), 'name' ) );
    }

    /**
    *  returns the requested prop for the passed font
    *
    * @param $font : array( 'name' => 'Name', 'subset' => array() )
    * @param $prop : string 'name' or 'subset'
    * @return mixed: name string or subset array
    */
    function get_font_property( $font, $prop ){
        return $font[$prop];
    }


  //retrieves gfonts:
  // 1) from webfonts.json if needed (transient doesn't exists, or is new version => set in TC_wfc ) and decodes them
  // otherwise
  // 2) from the transiet set if it exists
  //
  // => Until June 2017, the webfonts have been stored in 'tc_gfonts' transient
  // => In November 2018, the Google Fonts have been updated with a new webfonts.json generated from : https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBID8gp8nBOpWyH5MrsF7doP4fczXGaHdA
  // => The transient name is now : czr_gfonts_nov_2018
  function retrieve_decoded_gfonts() {

      if ( false == get_transient( 'czr_gfonts_nov_2018' ) ) {
          $gfont_raw      = @file_get_contents( dirname( dirname(__FILE__) ) ."/assets/fonts/webfonts.json" );

          if ( $gfont_raw === false ) {
            $gfont_raw = wp_remote_fopen( dirname( dirname(__FILE__) ) ."/assets/fonts/webfonts.json" );
          }

          $gfonts_decoded   = json_decode( $gfont_raw, true );
          set_transient( 'czr_gfonts_nov_2018' , $gfonts_decoded , 60*60*24*3000 );
      }
      else {
        $gfonts_decoded = get_transient( 'czr_gfonts_nov_2018' );
      }

      return $gfonts_decoded;
  }

  //@return the subsets or the google fonts
	function get_gfonts( $what = null ) {
		//checks if transient exists or has expired

    $gfonts_decoded = self::$gfonts_decoded;
		$gfonts = array();
		$subsets = array();

		$subsets['all-subsets'] = sprintf( '%1$s ( %2$s %3$s )',
			__( 'All languages' , 'hueman-pro' ),
			count($gfonts_decoded['items']) + count( $this -> get_cfonts() ),
			__('fonts' , 'hueman-pro' )
		);

		foreach ( $gfonts_decoded['items'] as $font ) {
			foreach ( $font['variants'] as $variant ) {
				$name 		= str_replace( ' ', '+', $font['family'] );
				$gfonts[] 	= array(
					'name' 		=> $name . ':' .$variant ,
					'subsets' 	=> $font['subsets']
				);
			}
			//generates subset list : subset => font number
			foreach ( $font['subsets'] as $sub ) {
				$subsets[$sub] = isset($subsets[$sub]) ? $subsets[$sub]+1 : 1;
			}
		}

		//finalizes the subset array
		foreach ( $subsets as $subset => $font_number ) {
			if ( 'all-subsets' == $subset )
				continue;
			$subsets[$subset] = sprintf('%1$s ( %2$s %3$s )',
				$subset,
				$font_number,
				__('fonts' , 'hueman-pro' )
			);
		}

		return ('subsets' == $what) ? apply_filters( 'tc_font_customizer_gfonts_subsets ', $subsets ) : apply_filters( 'tc_font_customizer_gfonts', $gfonts )  ;
	}


	function get_font_list() {
		return array_merge( $this -> get_cfonts() , $this -> get_gfonts( 'font') );
	}


	/*
	* Extracts a clean list of Google fonts from saved options and save it in options
  *=> Important : if the settings defined in sets/....json do no match the actual saved options, like for example when switching from classical to modern,
  * we need to make sure the key exists in the saved option
	*/
	function tc_update_front_end_gfonts() {
  		$saved 				      = TC_wfc::$instance -> tc_get_saved_option( null , false );
  		$front_end_gfonts 	= array();
      $_opt_prefix        = TC_wfc::$instance -> plug_option_prefix;

  		//extract the gfont list
  		foreach ( $saved as $setting ) {
    			if ( ! array_key_exists('font-family', $setting ) )
            continue;

    			$family 		= $setting['font-family'];
    			//check if is gfont first
    			if ( false != strstr( $family, '[gfont]') ) {
    				//removes [gfont]
    				$family = str_replace( '[gfont]', '' , $setting['font-family']);
    				//add the font to the list if does not exist
    				if ( isset( $front_end_gfonts[$family] ) ) {
    					//adds another subset to the subset's array if don't exist
    					if ( !in_array( $setting['subset'] , $front_end_gfonts[$family] ) )
    						$front_end_gfonts[$family][] = $setting['subset'];
    				} else {
    					$front_end_gfonts[$family] = array( $setting['subset'] );
    				}

    			}
  		}//foreach

  		//creates the clean family list ready for link
  		$families = array();
          $subsets  = array();
          foreach ($front_end_gfonts as $single_font => $single_font_subset) {
              //Creates the subsets array
              //if several subsets are defined for the same fonts > adds them and makes a subset array of unique subset values
              foreach ($single_font_subset as $key => $sub) {
                  if ( 'all-subsets' == $sub )
                      continue;
                  if ( $sub && ! in_array( $sub , $subsets) ) {
                      $subsets[] = $sub;
                  }
              }//end foreach
              $families[] = $single_font;
          }//end foreach
          $families = implode( "|", $families );
          if ( ! empty($subsets) ) {
              $families = $families . '&subset=' . implode( ',' , $subsets );
          }
  		update_option( "{$_opt_prefix}_gfonts" , $families );
	}

}//end of class
