<?php
/**
* @author Nicolas GUILLAUME - Rocco ALIBERTI
*/
if ( ! class_exists( 'PC_HA_CUSTOM_SCRIPTS ' ) ) :
final class PC_HA_CUSTOM_SCRIPTS  {
    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;

    function __construct() {
        self::$instance =& $this;
       //customizer
        add_filter( 'hu_add_section_map'         , array( $this, 'ha_fn_add_custom_scripts_section' ), 100 );
        //add controls to the map
        add_filter( 'hu_add_setting_control_map' , array( $this, 'ha_fn_popul_custom_scripts_section_option_map' ), 200, 2 );
        //front print scripts
        add_action( 'wp'                          , array( $this, 'ha_fn_front_hook_setup' ), 20 );
    }//end of construct



    //@hook: hu_add_section_map
    public function ha_fn_add_custom_scripts_section( $map ) {
        if ( !is_array( $map ) ) {
            return $map;
        }

        return array_merge( $map, array(
            /*---------------------------------------------------------------------------------------------
            -> PANEL : ADVANCED
            ----------------------------------------------------------------------------------------------*/
            'custom_scripts_sec'     => array(
                'title'     =>  __( 'Additional scripts' , 'hueman' ),
                'priority'  =>  15,
                'panel'     => 'hu-advanced-panel'
            )
        ) );
    }



    //@hook: hu_add_setting_control_map
    public function ha_fn_popul_custom_scripts_section_option_map( $_map, $get_default = null ) {
        if ( ! is_array( $_map ) )
            return;

        $_refresh_notice = sprintf( '%1$s%2$s',
            __( '<strong>Note:</strong>You need to click on the refresh button below to see the code applied to your site live preview.', 'hueman' ),
            sprintf( '<input type="button" style="cursor:pointer; display:block" onclick="wp.customize.previewer.refresh()" title="%1$s" value="%1$s" />',
                __( 'Refresh', 'hueman' )
            )
        );

        $_new_map = array(
                'custom_head_script' =>  array(
                                  'control'   => 'HU_Customize_Code_Editor_Control',
                                  'label'     => __( 'Add your custom scripts to the <head> of your site' , 'hueman' ),
                                  'section'   => 'custom_scripts_sec' ,
                                  'code_type' => 'text/html',
                                  'transport' => 'postMessage', //<- to avoid the refresh while typing, also we cannot really apply this live even debouncing the refresh because, if the user didn't finish to write the code, incomplete (see unbalanced tags) scripts might break the page layout, which is always scaring for users.
                                  'notice'    => sprintf( '%1$s<br/>%2$s',
                                      __( 'Any code you place here will appear in the head section of every page of your site. This is particularly useful if you need to input a tracking pixel for a state counter such as <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/" title="google-analytics" target="_blank">Google Analytics</a>', 'hueman'),
                                      $_refresh_notice
                                  )
                ),
                'custom_footer_script' =>  array(
                                  'control'   => 'HU_Customize_Code_Editor_Control',
                                  'label'     => __( 'Add your custom scripts before the </body> of your site' , 'hueman' ),
                                  'section'   => 'custom_scripts_sec' ,
                                  'input_attrs' => array(
                                    'aria-describedby' => 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4',
                                  ),
                                  'code_type' => 'text/html',
                                  'transport' => 'postMessage', //<- to avoid the refresh while typing, also we cannot really apply this live even debouncing the refresh because, if the user didn't finish to write the code, incomplete (see unbalanced tags) scripts might break the page layout, which is always scaring for users.
                                  'notice'    => sprintf( '%1$s<br/>%2$s',
                                      __( 'Any code you place here will appear at the very bottom of every page of your site, just before the closing &lt;/body&gt; tag.', 'hueman'),
                                      $_refresh_notice
                                  )
                ),
        );

        //Fall back on the standard textarea control if the CZR_Customize_Code_Editor_Control doesn't exists
        //e.g. wp version < 4.9
        if ( ! class_exists( 'HU_Customize_Code_Editor_Control' ) ) {
            foreach ( $_new_map as $key => &$params ) {
                unset( $params[ 'input_attrs' ], $params[ 'code_type' ] );
                $params[ 'type' ]        = 'textarea';
                $params[ 'control' ]     = 'HU_Controls';
                //in our base control we don't escape the html from the label
                //while the wp built-in label is escaped
                $params[ 'label' ]       = esc_html( $params['label'] );
            }
        }

        $_new_map = array_merge( $_map, $_new_map );

        return $_new_map;
    }




    //@hook: wp
    public function ha_fn_front_hook_setup() {
        add_action( 'wp_head'  , array( $this, 'ha_fn_maybe_print_custom_head_script' ), apply_filters( 'ha_custom_head_script_priority', 12 ) );
        add_action( 'wp_footer', array( $this, 'ha_fn_maybe_print_custom_footer_script' ), apply_filters( 'ha_custom_footer_script_priority', 12 ) );
    }



    //@hook: wp_head
    public function ha_fn_maybe_print_custom_head_script() {
        $custom_head_script = trim( hu_get_option( 'custom_head_script' ) );
        if ( ! empty( $custom_head_script ) ) {
            echo $custom_head_script;
        }
    }


    //@hook: wp_footer
    public function ha_fn_maybe_print_custom_footer_script() {
        $custom_footer_script = trim( hu_get_option( 'custom_footer_script' ) );
        if ( ! empty( $custom_footer_script ) ) {
            echo $custom_footer_script;
        }
    }

} //end of class
endif;