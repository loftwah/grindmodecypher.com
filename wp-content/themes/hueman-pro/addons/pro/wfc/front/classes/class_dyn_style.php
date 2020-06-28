<?php
/**
* Dynamic stylesheet generation
* @author Nicolas GUILLAUME
* @since 1.0
*/
class TC_dyn_style {

    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;

    function __construct () {
        self::$instance =& $this;
        add_action( '__dyn_style'                   , array( $this , 'tc_render_dyn_style' ) , 10 , 1 );

        //V1.19 FIX FOR THE MENU ITEMS
        add_action( '__dyn_style'                   , array( $this , 'tc_additional_styles' ) , 20 );
    }



    /*
    * @since v1.19
    * Fixes a default Customizr(3.2.0) style for the menu items first letter
    */
    function tc_additional_styles( $what = null ) {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        /* If $what = 'fonts' (font-family styling) we don't need this */
        if ( 'fonts' == $what )
            return;

        /*
        * Since Cusomizr(-Pro) 3.4.44(1.2.34) We got rid of the menu item first letter styling
        * so we don't need this patch for those versions
        */

        /*
        * If the theme:
        * a) is not Customizr
        * or
        * b) is Customizr and version > 3.4.33 do nothing
        */
        if ( 'customizr' != TC_wfc::$theme_name || version_compare( CUSTOMIZR_VER, '3.4.33', '>' ) )
          return;

        // is old customizr (before class and methods/function prefixes change) ?
        $_is_old_customizr             = version_compare( CUSTOMIZR_VER, '3.4.30', '<' );


        //'tc_menu_item_style_first_letter' hook filter was used also in Customizr TC_menu(CZR_menu) class
        if ( ! apply_filters('tc_menu_item_style_first_letter', $_is_old_customizr ?
                TC_utils::$instance -> tc_user_started_before_version( '3.2.0', '1.0.0') :
                CZR_utils::$instance -> czr_fn_user_started_before_version( '3.2.0', '1.0.0') ) )
            return;

        $_raw = TC_wfc::$instance -> tc_get_saved_option();
        if ( isset($_raw['menu_items']) && !empty($_raw['menu_items']) )
            //Disable the first letter default Customizr setting
            printf( '<style id="dyn-style-others" type="text/css">%1$s</style>',
                sprintf( '%1$s%2$s {%3$s}%4$s',
                    "/* Menu items first letter fix */ \n",
                    '.navbar .nav>li>a:first-letter',
                    "font-size: inherit;",
                    "\n\n"
                )
            );
    }


    // Used to render stylesheets.
    // On front, each selector can have up to 2 stylesheets printed : the font-family is printed in a dedicated stylesheet, early in <head>, while the other css properties are printed later in another stylesheet
    // In customizing mode, all properties of a given selector are printed in the same stylesheet, because it is easier to replace by the customized js generated stylesheet
    // The stylesheet id for a selector item id is : 'fonts' == $what ? 'wfc-style-fonts-' . $data[ 'id' ] : 'wfc-style-' . $data[ 'id' ]

    // 1) When not customizing, for the font-family only => printed early :  add_action( 'wp_head' , array( $this , 'tc_write_font_dynstyle'), 0 );
    // 2) for the other css properties, printed later : add_action( 'wp_head', array( $this , 'tc_write_other_dynstyle'), 999 );
    function tc_render_dyn_style( $what = null ) {
        // May 2020 for https://github.com/presscustomizr/wordpress-font-customizer/issues/115
        if ( (bool)get_option( TC_wfc::$opt_name . '_deactivated' ) )
          return;

        $_raw                           = TC_wfc::$instance -> tc_get_saved_option();
        foreach ( $_raw as $data) {
            if ( empty( $data ) )
                continue;


            //we only want to add style for the customized properties

            // store the normalized customized property array
            $customized   = $data['customized'];
            $customized   = is_array( $customized ) ? $customized : array();
            $customized_data = array();
            foreach( $customized as $prop ) {
                $customized_data[ $prop ] = $data[ $prop ];
            }

            //check what is requested : font or other properties
            if ( 'fonts' == $what && ! array_key_exists( 'font-family', $customized_data ) )
                continue;

            // When not customizing if we're not printing the font-family and there's no other property customized, skip
            if ( ! TC_wfc::$instance -> tc_is_customizing() && 'fonts' != $what && array_key_exists( 'font-family', $customized_data ) && 1 == count( $customized_data ) )
                continue;

            $selector = $data['selector'];
            $title = $data['title'];
            $css = '';

            if ( ! empty( $customized_data ) ) {
                //selector css block
                $css .= sprintf( '%1$s%2$s {%3$s}%4$s',
                    "/* Setting : ".$title." */ \n",
                    $selector,
                    "\n".$this -> tc_get_properties( array(
                            'data' => $customized_data,
                            'special' => null,
                            'what' => $what
                        )
                    ),
                    "\n\n"
                );
            }

            //hover color
            if ( 'fonts' != $what && array_key_exists('color-hover', $customized_data ) ) {
                //if several selectors, then add :hover to each selector
                if ( false !== strpos($selector, ",") ) {
                    $sel_array =  explode(",", $selector);
                    foreach ($sel_array as $key => $sel) {
                        $sel_array[$key] = $sel.':hover';
                    }
                    $selector = implode(",", $sel_array);
                } else {
                    $selector = $selector.':hover';
                }

                $css .= sprintf( '%1$s%2$s {%3$s}%4$s',
                    "/* Setting : ".$title." */ \n",
                    $selector,
                    "\n". $this -> tc_get_properties( array(
                            'data' => $customized_data,
                            'special' => 'hover',
                            'what' => $what
                        )
                    ),
                    "\n\n"
                );
            }

            //Handles some Static effect exceptions
            if ( 'fonts' != $what && array_key_exists( 'static-effect', $customized_data ) ) {
                if ('inset' == $customized_data['static-effect'] ) {
                    $css .= sprintf( '%1$s%2$s {%3$s}%4$s',
                        "/* Hack for Mozilla and IE Inset Effect (not supported) : ".$title." */ \n",
                        '.mozilla '.$selector . ', .ie '.$selector,
                        "\n".sprintf( '%1$s : %2$s;%3$s',
                                'background-color',
                                'rgba(0, 0, 0, 0) !important',
                                "\n"
                            ),
                        "\n\n"
                    );
                }
            }
            if ( ! empty( $css ) ) {
                printf( '<style id="%1$s" type="text/css" data-origin="server">%2$s</style>',
                    'fonts' == $what ? 'wfc-style-fonts-' . $data[ 'id' ] : 'wfc-style-' . $data[ 'id' ],
                    "\n" . $css
                );
            }
        }//foreach
    }

    //@param $args {array}. Ex : array(
    //         'data' => $customized_data,
    //         'special' => null,
    //         'what' => $what
    //     )
    // ),
    // was $customizer_setting , $raw_single_setting , $data , $special = null , $what = null
    function tc_get_properties( $args ) {
        $what = $args['what'];
        $special = $args['special'];
        //authorized css properties
        $css_properties = array(
          'font-family',
          'font-weight',
          'font-style',
          'color',
          'font-size',
          'line-height',
          'letter-spacing',
          'text-align',
          'text-decoration',
          'text-transform',
          'color-hover'
        );

        //declares the property rendering var
        $properties_to_render   = array();
        $render_properties      = '';

        foreach ( $args['data'] as $property => $value ) {
            //checks if it an authorized property
            if ( ! in_array( $property, $css_properties ) )
                continue;

            // Some properties like font-style can get the _not_set_ value when the user chooses "Select"
            // we don't want to print this specific value
            // @see customizer js input constructor for
            // case 'font-weight' :
            // case 'font-style' :
            // case 'text-align' :
            // case 'text-decoration' :
            // case 'text-transform' :
            if ( '_not_set_' == $value )
                continue;

            //checks what is requested : fonts or the rest
            if ( ! TC_wfc::$instance -> tc_is_customizing() ) {
                if ( 'fonts' == $what && 'font-family' != $property )
                    continue;
                if ( 'other' == $what && 'font-family' == $property )
                    continue;
            }

            //checks if there are DB saved settings for this property
            // if ( 'notcase' != $special && ! isset( $raw_single_setting[$property] ) )
            //     continue;

            //hover case
            if ( 'hover' == $special && 'color-hover' != $property )
                continue;

            switch ($property) {
                case 'font-family':
                    //font: [font-stretch] [font-style] [font-variant] [font-weight] [font-size]/[line-height] [font-family];
                    //special treatment for font-family
                    if ( strstr( $value, '[gfont]') ) {
                        $split                      = explode(":", $value);
                        $value                      = $split[0];
                        //only numbers for font-weight. 400 is default
                        $properties_to_render['font-weight']    = $split[1] ? preg_replace('/\D/', '', $split[1]) : '';
                        $properties_to_render['font-weight']    = empty($properties_to_render['font-weight']) ? 400 : $properties_to_render['font-weight'];
                        $properties_to_render['font-style']     = ( $split[1] && strstr($split[1], 'italic') ) ? 'italic' : 'normal';
                    }
                    $value = str_replace( array( '[gfont]', '[cfont]') , '' , $value );
                    $properties_to_render['font-family'] = in_array( $value, TC_utils_wfc::$cfonts_list) ? $value : "'" . str_replace( '+' , ' ' , $value ) . "'";
                break;

                case 'font-size' :
                case 'line-height' :
                    //if no unit specified, then set px if value > 7, else em.
                    $unit = preg_replace('/[^a-z\s]/i', '', $value);
                    $num_value = $value;
                    if ( !is_numeric( $num_value ) ) {
                        $num_value = preg_replace('/[^0-9]/','', $num_value );
                    }
                    // cast to number to fix https://github.com/presscustomizr/wordpress-font-customizer/issues/114
                    $num_value = (float)$num_value;
                    if ( '' == $unit && $num_value >= 0 ) {
                        $unit = 'px';//( $num_value > 7 ) ? 'px' : 'rem';
                        $value = $num_value . $unit;
                    }
                    //convert in rem only if unit is pixel!
                    //Note : we might have users with a previous version, for which this is already set in em, in this case keep it unchanged
                    if ( 'px' == $unit && $num_value >= 0 ) {
                        $emsize      = $num_value / 16;
                        $emsize      = number_format( (float)$emsize, 2, '.', '');
                        $value       = $emsize . 'rem';
                    }
                break;

                case 'letter-spacing' :
                    $value = preg_replace('/[^0-9]/','', $value) . 'px';
                break;

                case 'font-style' :
                case 'font-weight' :
                    $value = ( is_null($value) || !$value || empty($value) ) ? 'normal' : $value;
                break;

                case 'color' :
                    if ( array_key_exists( 'static-effect', $args['data'] ) && 'inset' == $args['data']['static-effect'] ) {
                       $properties_to_render['background-color'] = $value.'!important';
                    }
                break;
            }//end switch

            //not font family additional treatment
            if ( $property != 'font-family') {
                  $properties_to_render[$property] = $value;
            }

            //color hover additional treatment
            if ( 'color-hover' == $property && 'hover' == $special ) {
                $properties_to_render['color'] = $properties_to_render['color-hover'];
            }
            if ( isset($properties_to_render['color-hover']) )
                unset($properties_to_render['color-hover']);

        }//end foreach

        foreach ($properties_to_render as $prop => $prop_val) {
            //checks what is requested : fonts or the rest
            if ( 'fonts' == $what && 'font-family' != $prop )
                continue;

            //check if is !important : it can be set in the raw json config file OR manually set by user in the customizer
            $has_important      = false;
            $has_important      = ( array_key_exists( 'important', $args['data'] ) && $args['data']['important'] ) ? true : $has_important;
            $render_properties .=   sprintf( '%1$s : %2$s;%3$s',
                  $prop,
                  ( $has_important || 'font-family' == $prop ) ? $prop_val.'!important' : $prop_val,
                  "\n"
            );
        }//end foreach

        return $render_properties;

    }//end of function

} //end of class
