<?php
/**
* Manages various admin notices
* @author Nicolas GUILLAUME
* @since 1.0
*/
class TC_wfc_admin_notices {
	static $instance;
	public $plug_name;
  public $plug_prefix;
  public $plug_version;

	function __construct( $args ) {

        //extract properties from args
        list( $this -> plug_name , $this -> plug_prefix  ) = $args;

    	self::$instance =& $this;

    	//Checks Customizr version to choose the right hook
        $tc_theme               = wp_get_theme();
        //gets the version of Customizr or parent if using a child theme
        $this -> version        = ( $tc_theme -> parent() ) ? $tc_theme -> parent() -> Version : $tc_theme -> Version;

    	//Checks if users uses Customizr has a theme or a parent and checks version is 3.1+
        //version compare returns 1 if second version < first version
        if( $tc_theme->Name != 'Customizr' && ( $tc_theme -> parent() ? ($tc_theme -> parent() -> Name != 'Customizr') : true ) 
          && $tc_theme->Template != 'customizr' || 1 == version_compare( '3.1.0' , $this -> version ) ) {
          if(is_admin()){
            add_action('admin_notices'                , array( $this , 'tc_plugin_notice') );
            add_action('admin_init'                   , array( $this , 'tc_plugin_notice_meta') );
          }
          return;
        }

    }//end of construct

    function tc_plugin_notice(){
        global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta( $user_id, 'tc_' . $this -> plug_prefix . '_notice' ) ) {
            printf( '<div class="updated"><p>%1$s</p><a href="%2$s">Dismiss</a></div>',
              sprintf( __('The <strong>%1$s</strong> plugin can not be used with a theme (or a parent theme) different than Customizr with a version less than v3.1.0.' , 'customizr-plugins'),
                $this -> plug_name
                ),
              '?dismiss_' . $this -> plug_prefix . '_notice=0'
            );
      }
      
    }


    function tc_plugin_notice_meta() {
      global $current_user;
      $user_id = $current_user->ID;
      if ( isset($_GET['dismiss_' . $this -> plug_prefix . '_notice']) && '0' == $_GET['dismiss_' . $this -> plug_prefix . '_notice'] ) {
           add_user_meta($user_id, 'tc_' . $this -> plug_prefix . '_notice', 'true', true);
      }
    }

}//end of class