<?php
/**
* Check for theme updates
* @author Nicolas GUILLAUME
* @since 1.0
*/
class HU_theme_check_updates {
    static $instance;
    public $theme_name;
    public $theme_version;
    public $theme_prefix;
    public $theme_lang;
    protected $strings = null;
    protected $config;

    function __construct( $args ) {
        self::$instance =& $this;

        //extract properties from args
        list( $this -> theme_name , $this -> theme_prefix , $this -> theme_version ) = $args;

        //loads the updater
        add_action( 'admin_init'                       , array( $this , 'tc_theme_update_check' ) );
    }//end of construct

    function tc_theme_update_check() {
      // To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
      $doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;

      if ( !current_user_can( 'manage_options' ) && !$doing_cron) {
        return;
      }

      /* If there is no valid license key status, don't allow updates. */
      if ( get_option( 'tc_' . $this->theme_prefix . '_license_status', false) != 'valid' ) {
        return;
      }

      // retrieve our license key from the DB
      $_license_key = trim( get_option( 'tc_' . $this -> theme_prefix . '_license_key' ) );

      // setup the updater
      $edd_updater = new HU_theme_updater( array(
              'remote_api_url'  => TC_THEME_URL,  // Our store URL that is running EDD
              'theme_slug'     => get_template(),
              'version'   => $this -> theme_version,               // current version number
              'license'   => $_license_key,            // license key (used get_option above to retrieve from DB)
              'item_name' => $this -> theme_name,     // name of this plugin
              'author'    => 'Press Customizr',  // author of this plugin
              'beta'      => false, //added april 2020, not used
              'item_id'   => '' //added april 2020, not used
          )
      );
    }

}//end of class