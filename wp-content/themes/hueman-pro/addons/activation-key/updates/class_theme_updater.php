<?php
class HU_theme_updater{
  private $remote_api_url;
  private $request_data;
  private $response_key;
  private $theme_slug;
  private $license_key;
  private $version;
  private $author;
  protected $strings = null;

  function __construct( $args = array() ) {
    $args = wp_parse_args( $args, array(
        'remote_api_url' => 'https://presscustomizr.com',
        'request_data'   => array(),
        'theme_slug'     => get_template(),
        'item_name'      => '',
        'license'        => '',
        'version'        => '',
        'author'         => '',
        'beta'           => false,//added april 2020, not used
        'item_id'        => ''//added april 2020, not used
    ) );
    extract( $args );

    /**
     * Fires after the theme $config is setup.
     *
     * @since x.x.x
     *
     * @param array $config Array of EDD SL theme data.
     */
    do_action( 'post_edd_sl_theme_updater_setup', $args );

    $theme                = wp_get_theme( sanitize_key( $theme_slug ) );
    $this->license        = $license;
    $this->item_name      = $item_name;
    $this->version        = ! empty( $version ) ? $version : $theme->get( 'Version' );
    $this->theme_slug     = sanitize_key( $theme_slug );
    $this->author         = $author;
    $this->remote_api_url = $remote_api_url;
    $this->response_key   = $this->theme_slug . '-update-response';
    $this->strings        = HU_activation_key::$instance -> strings;
    $this->beta           = $beta;//added april 2020, not used
    $this->item_id        = $item_id;//added april 2020, not used

    //api not accessible transient name
    $this->api_not_accessible_transient = $this->theme_slug . '_api_not_accessible';

    add_action( 'load-themes.php'                       , array( &$this, 'load_themes_screen' ) );

    add_filter( 'site_transient_update_themes'          , array( &$this, 'theme_update_transient' ) );
    add_filter( 'delete_site_transient_update_themes'   , array( &$this, 'delete_theme_update_transient' ) );
    add_action( 'load-update-core.php'                  , array( &$this, 'delete_theme_update_transient' ) );
    add_action( 'load-themes.php'                       , array( &$this, 'delete_theme_update_transient' ) );


    //change the url for the changelog
    add_filter( 'wp_prepare_themes_for_js'              , array( $this, 'tc_set_correct_changelog_url') );
  }


  /*******************************************************
  * VIEWS
  *******************************************************/
  /*
  * hook : load-themes.php
  */
  function load_themes_screen() {
    add_thickbox();
    //UPGRADE MESSAGE : print the box on top of the theme's list in the the theme page
    add_action( 'admin_notices', array( $this, 'update_nag' ) );

    //API NOT ACCESSIBLE MESSAGE : print a box on top of the theme's list in the the theme page
    add_action( 'admin_notices', array( $this, '_api_not_accessible' ) );
  }


  /*
  * hook : admin_notices
  * fired in load_themes_screen
  */
  function update_nag() {
    $strings = $this->strings;

    $theme = wp_get_theme( $this->theme_slug );

    $api_response = get_transient( $this->response_key );

    if( false === $api_response )
      return;

    $update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->theme_slug ), 'upgrade-theme_' . $this->theme_slug );
    $update_onclick = ' onclick="if ( confirm(\'' . esc_js( $strings['update-notice'] ) . '\') ) {return true;}return false;"';

    if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {

      echo '<div id="update-nag">';
      printf(
        $strings['update-available'],
        $theme->get( 'Name' ),
        $api_response->new_version,
        '#TB_inline?width=640&amp;inlineId=' . $this->theme_slug . '_changelog',
        $theme->get( 'Name' ),
        $update_url,
        $update_onclick
      );
      echo '</div>';
      echo '<div id="' . $this->theme_slug . '_' . 'changelog" style="display:none;">';
      echo wpautop( $api_response->sections['changelog'] );
      echo '</div>';
    }
  }



  /*
  * hook : admin_notices
  * fired in load_themes_screen
  * Maybe display a notice in the theme screen if attemps to access the update API have failes
  * @uses transient : api_not_accessible
  */
  function _api_not_accessible() {
    //check if transient api not accessible exists
    $api_response = get_transient( $this->response_key );

    if ( ! get_transient($this->api_not_accessible_transient) )
      return;

    $theme = wp_get_theme( $this->theme_slug );
    $theme_name = $theme->get( 'Name' );

    $_html = sprintf('<div class="notice-info notice"><p>%1$s</p></div>',
        sprintf( '%1$s <strong>%2$s</strong>. %3$s <a href="%4$s" title="%5$s">%5$s</a>.',
          __( "We couldn't check the updates for ", 'hueman-pro'),
          $theme_name,
          __( 'You might need to check for updates on <a href="https://presscustomizr.com/hueman-pro" title="Press Customizr" target="_blank">presscustomizr.com</a> and ', "hueman-pro" ),
          admin_url( 'themes.php?page=tc-licenses'),
          __( "upload the theme manually", "hueman-pro" )
        )
    );

    echo $_html;
  }



  /*******************************************************
  * WORDPRESS UPDATE API HOOKS
  *******************************************************/
  /*
  * hook : site_transient_update_themes
  * fired in all admin pages
  */
  function theme_update_transient( $value ) {
    //) if api has not been accessible in the last 6 hours, do nothing
    if ( get_transient($this->api_not_accessible_transient) )
      return $value;

    $update_data = $this->check_for_update();
    if ( $update_data ) {
      // Make sure the theme property is set. See issue 1463 on Github in the Software Licensing Repo.
      $update_data['theme'] = $this->theme_slug;
      // make sure we have a properly set value.
      // fixes https://github.com/presscustomizr/customizr-pro-activation-key/issues/8
      if ( !$value || !is_object($value) || !isset($value->response) || !is_array($value->response) ) {
        return $value;
      } else {
        if ( version_compare( $this->version, $update_data['new_version'], '<' ) ) {
          $value->response[ $this->theme_slug ] = $update_data;
        } else {
          $value->no_update[ $this->theme_slug ] = $update_data;
        }
      }
    }
    return $value;
  }


  /*
  * hook : delete_site_transient_update_themes
  * hook : load-update-core.php
  * hook : load-themes.php
  */
  function delete_theme_update_transient() {
    delete_transient( $this->response_key );
  }


  /*
  * fired in theme_update_transient() <= in all admin pages
  * Maybe set a transient if API not accessible $this->api_not_accessible_transient
  */
  function check_for_update() {
    $theme = wp_get_theme( $this->theme_slug );

    $update_data = get_transient( $this->response_key );

    if ( false === $update_data ) {
      /*********************************************
      * No transient. We need to set an update_data transient
      **********************************************/
      $failed = false;

      // if no license entered => do nothing
      if( empty( $this->license ) )
        return false;

      $api_params = array(
          'edd_action'  => 'get_version',
          'license'     => $this->license,
          'name'      => $this->item_name,
          'slug'      => $this->theme_slug,
          'author'    => $this->author,
          'url'           => home_url(),
          'beta'       => $this->beta,//added april 2020, not used
          'item_id'    => $this->item_id//added april 2020, not used
      );

      $response = wp_remote_post( $this->remote_api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params , 'decompress' => false) );

      // make sure the response was successful
      if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
        set_transient( $this->api_not_accessible_transient, true, 60*60*6 );
        $failed = true;
      }

      $update_data = json_decode( wp_remote_retrieve_body( $response ) );

      if ( ! is_object( $update_data ) ) {
        $failed = true;
      }

      // if the response failed, try again in 30 minutes
      if ( $failed ) {
        $data = new stdClass;
        $data->new_version = $this->version;
        set_transient( $this->response_key, $data, strtotime( '+30 minutes' ) );
        return false;
      }

      // if the status is 'ok', return the update arguments
      if ( ! $failed ) {
        $update_data->sections = maybe_unserialize( $update_data->sections );
        set_transient( $this->response_key, $update_data, strtotime( '+12 hours' ) );
      }
    }//if false === $update_data

    /*********************************************
    * We have a transient (30 min long )
    **********************************************/
    if ( version_compare( $this->version, $update_data->new_version, '>=' ) ) {
      return false;
    }

    //at this stage, the API has answered so we can delete the $this->api_not_accessible_transient if exists
    delete_transient( $this->api_not_accessible_transient );

    return (array) $update_data;
  }//check_for_updates()




  /*******************************************************
  * VARIOUS CALLBACKS / HELPERS
  *******************************************************/
  /**
  * Change the url for the changelog to get the local changelod (instead of the one on presscustomizr.com)
  * hook : wp_prepare_themes_for_js
  */
  function tc_set_correct_changelog_url( $prepared_themes ) {
    if ( ! isset($prepared_themes[$this->theme_slug]) || ! $prepared_themes[$this->theme_slug]['hasUpdate'] || ! isset($prepared_themes[$this->theme_slug]['update']) )
      return $prepared_themes;

    $prepared_themes[$this->theme_slug]['update'] = str_replace(
      'https://presscustomizr.com/hueman-pro/?changelog=1&#038;TB_iframe=true&#038;width=1024&#038;height=800',
      '#TB_inline?width=640&inlineId=hueman-pro_changelog',
      $prepared_themes[$this->theme_slug]['update']
    );
    return $prepared_themes;
  }
}