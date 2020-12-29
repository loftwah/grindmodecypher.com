<?php
/**
* PRO INFINITE SCROLL CLASS
*
* @author Nicolas GUILLAUME
* @since 1.0
* based on jetpacks infinite scroll
*/
/***
 * Infinite Scroll
 *
 * Adds infinite scrolling support for the blog homepage.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Use class to avoid namespace collisions
if ( ! class_exists('PC_infinite_scroll') ) :


final class PC_infinite_scroll {

      /**
       * Initialize our static variables
       */
      static $the_time            = null;
      static $settings            = null; // Don't access directly, instead use self::get_settings().

      static $option_name_enabled = 'pc_infinite_scroll';

      static $type;
      static $isClickTypeOnMobile;
      static $isClickTypeOnDesktop;
      static $handle;
      static $appendHandleTo;
      static $minWidthForDetermineUrl;

      /**
      * Register actions and filters, plus parse IS settings
      *
      * @uses add_action, add_filter, self::get_settings
      * @return null
      */
      function __construct( $args ) {
            $args = array_merge(
                array(
                  'type'            => 'scroll',
                  'isClickTypeOnMobile' => true,
                  'isClickTypeOnDesktop' => false,
                  'handle'          => '<div id="infinite-handle"><span><button>{text}</button></span></div>',
                  'appendHandleTo'  => '',
                  'minWidthForDetermineUrl'      => 575
                ),
                $args
            );
            self::$type = $args['type'];
            self::$isClickTypeOnMobile = $args['isClickTypeOnMobile'];
            self::$isClickTypeOnDesktop = $args['isClickTypeOnDesktop'];
            self::$handle = $args['handle'];
            self::$appendHandleTo = $args['appendHandleTo'];
            self::$minWidthForDetermineUrl = $args['minWidthForDetermineUrl'];

            //THIS WON'T WORK IN SKOPE
            //add_action( 'pre_get_posts',                  array( $this, 'posts_per_page_query' ) );

            //higher the priority (less than 10) as most of the skoped "features" are hooked to template_redirect|10
            //e.g. the pro header slider assumes skope is ready at  template_redirect|10
            //but in ajax context, when doing infinity, the actual context is defined by the infinity query performed by
            // this class' query() method, triggered in ajax_response callback at template_redirect|5
            add_action( 'template_redirect',                 array( $this, 'action_template_redirect' ), 5 );
            add_action( 'template_redirect',                 array( $this, 'ajax_response' ), 5 );

            add_action( 'custom_ajax_infinite_scroll',       array( $this, 'query' ) );

            add_filter( 'infinite_scroll_query_args',        array( $this, 'inject_query_args' ) );
            add_filter( 'infinite_scroll_allowed_vars',      array( $this, 'allowed_query_vars' ) );


            add_action( 'the_post',                          array( $this, 'preserve_more_tag' ) );

            //Can be useful
            add_action( 'wp_footer',                         array( $this, 'footer' ) );

            // Plugin compatibility
            add_filter( 'grunion_contact_form_redirect_url', array( $this, 'filter_grunion_redirect_url' ) );


            if ( ! defined( 'PC_INFINITE_SCROLL_BASE_URL' ) ) { define( 'PC_INFINITE_SCROLL_BASE_URL' , PC_INFINITE_BASE_URL . '/infinite-scroll' ); }

            // Parse IS settings from theme
            self::get_settings();

      }



      /**
      * Parse IS settings provided by theme
      *
      * @return object
      */
      static function get_settings() {

            if ( is_null( self::$settings ) ) {

                  $css_pattern = '#[^A-Z\d\-_]#i';

                  $settings = $defaults = array(
                    'type'            => self::$type,// 'click',// 'scroll', // scroll | click
                    'isClickTypeOnMobile' => self::$isClickTypeOnMobile,
                    'isClickTypeOnDesktop' => self::$isClickTypeOnDesktop,
                    'requested_type'  => self::$type, // store the original type for use when logic overrides it
                    'footer_widgets'  => false, // true | false | sidebar_id | array of sidebar_ids -- last two are checked with is_active_sidebar
                    'container'       => 'grid-wrapper',// 'content', // container html id
                    'wrapper'         => false,// true, // true | false | html class
                    'render'          => false, // optional function, otherwise the `content` template part will be used
                    'render_inner_loop' => false,
                    'footer'          => false, //true, // boolean to enable or disable the infinite footer | string to provide an html id to derive footer width from
                    'footer_callback' => false, // function to be called to render the IS footer, in place of the default
                    'posts_per_page'  => false, // int | false to set based on IS type
                    'click_handle'    => true, // boolean to enable or disable rendering the click handler div. If type is click and this is false, page must include its own trigger with the HTML ID `infinite-handle`.
                    'handle'          => self::$handle, //'<div id="infinite-handle"><span><button>{text}</button></span></div>',//'<div id="infinite-handle"><span><button>' + text.replace( '\\', '' ) + '</button></span></div>'
                    'appendHandleTo'  => self::$appendHandleTo,
                    'minWidthForDetermineUrl'      => self::$minWidthForDetermineUrl
                  );

                  // Validate settings passed through add_theme_support()

                  // TODO: Not sure about this
                  $_settings = get_theme_support( 'pc-infinite-scroll' );

                  if ( is_array( $_settings ) ) {

                        // Preferred implementation, where theme provides an array of options
                        if ( isset( $_settings[0] ) && is_array( $_settings[0] ) ) {

                              foreach ( $_settings[0] as $key => $value ) {

                                    switch ( $key ) {

                                          case 'type' :

                                                if ( in_array( $value, array( 'scroll', 'click' ) ) )
                                                      $settings[ $key ] = $settings['requested_type'] = $value;

                                          break;

                                          /* In case we want to show footer widgets in the "sticky footer" */
                                          case 'footer_widgets' :

                                                if ( is_string( $value ) )
                                                      $settings[ $key ] = sanitize_title( $value );
                                                elseif ( is_array( $value ) )
                                                      $settings[ $key ] = array_map( 'sanitize_title', $value );
                                                elseif ( is_bool( $value ) )
                                                      $settings[ $key ] = $value;

                                          break;

                                          case 'container' :
                                          case 'wrapper' :

                                                if ( 'wrapper' == $key && is_bool( $value ) ) {

                                                      $settings[ $key ] = $value;

                                                }
                                                else {
                                                      //@pc_addon : allow css_pattern customization, to validate the container html selector as not only an id
                                                      if ( isset( $_settings[ 'css_pattern' ] ) && $_settings[ 'css_pattern' ] ) {
                                                          $value = preg_replace( $css_pattern, '', $value );
                                                      }
                                                      if ( ! empty( $value ) )
                                                            $settings[ $key ] = $value;
                                                }

                                          break;

                                          /* Custom render callback */
                                          case 'render' :

                                                if ( false !== $value && is_callable( $value ) ) {
                                                      $settings[ $key ] = $value;

                                                      add_action( 'infinite_scroll_render', $value );
                                                }

                                          break;
                                          /* Custom render callback */
                                          case 'render_inner_loop' :

                                                if ( false !== $value && is_callable( $value ) ) {
                                                      $settings[ $key ] = $value;
                                                }

                                          break;
                                          /* In case we want to show a "sticky footer" */
                                          case 'footer' :

                                                if ( is_bool( $value ) ) {

                                                      $settings[ $key ] = $value;

                                                } elseif ( is_string( $value ) ) {

                                                      $value = preg_replace( $css_pattern, '', $value );

                                                if ( ! empty( $value ) )
                                                        $settings[ $key ] = $value;
                                                }

                                          break;

                                          case 'footer_callback' :

                                                if ( is_callable( $value ) )
                                                      $settings[ $key ] = $value;
                                                else
                                                      $settings[ $key ] = false;

                                          break;

                                          case 'posts_per_page' :

                                                if ( is_numeric( $value ) )
                                                      $settings[ $key ] = (int) $value;

                                          break;

                                          case 'click_handle' :

                                                if ( is_bool( $value ) ) {
                                                      $settings[ $key ] = $value;
                                                }

                                          break;
                                    }//end switch
                              }//endforeach
                        }//end isset( $_settings[0] ) && is_array( $_settings[0] )

                        elseif ( is_string( $_settings[0] ) ) {

                              // Checks below are for backwards compatibility

                              // Container to append new posts to
                                    $settings['container'] = preg_replace( $css_pattern, '', $_settings[0] );

                              // Wrap IS elements?
                              if ( isset( $_settings[1] ) )
                                    $settings['wrapper'] = (bool) $_settings[1];
                        }

                  }//end if is_array( $_settings )

                  // Always ensure all values are present in the final array
                  $settings = wp_parse_args( $settings, $defaults );

                  //Footer widgets treatment
                  // If a widget area ID or array of IDs was provided in the footer_widgets parameter, check if any contains any widgets.
                  // It is safe to use `is_active_sidebar()` before the sidebar is registered as this function doesn't check for a sidebar's existence when determining if it contains any widgets.
                  if ( is_array( $settings['footer_widgets'] ) ) {

                        $sidebar_ids = $settings['footer_widgets'];
                        $settings['footer_widgets'] = false;

                        foreach ( $sidebar_ids as $sidebar_id ) {
                              if ( is_active_sidebar( $sidebar_id ) ) {
                                    $settings['footer_widgets'] = true;
                                    break;
                              }
                        }

                        unset( $sidebar_ids );
                        unset( $sidebar_id );

                  } elseif ( is_string( $settings['footer_widgets'] ) ) {

                        $settings['footer_widgets'] = (bool) is_active_sidebar( $settings['footer_widgets'] );
                  }

                  /**
                   * Filter Infinite Scroll's `footer_widgets` parameter.
                   *
                   * @module infinite-scroll
                   *
                   * @since 2.0.0
                   *
                   * @param bool $settings['footer_widgets'] Does the current theme have Footer Widgets.
                   */
                  $settings['footer_widgets'] = apply_filters( 'infinite_scroll_has_footer_widgets', $settings['footer_widgets'] );

                  // Finally, after all of the sidebar checks and filtering, ensure that a boolean value is present, otherwise set to default of `false`.
                  if ( ! is_bool( $settings['footer_widgets'] ) )
                    $settings['footer_widgets'] = false;


                  // Ensure that IS is enabled and no footer widgets exist if the IS type isn't already "click".
                  if ( 'click' != $settings['type'] ) {

                        // Check the setting status
                        $disabled = '' === get_option( self::$option_name_enabled ) ? true : false;

                        // Footer content or Reading option check
                        if ( $settings['footer_widgets'] || $disabled )
                              $settings['type'] = 'click';
                  }
                  //@pc_addon: Do not force the posts per page
                  // posts_per_page defaults to 6 for scroll, posts_per_page option for click
          /*        if ( false === $settings['posts_per_page'] ) {

                        if ( 'scroll' === $settings['type'] ) {

                              $settings['posts_per_page'] = 6;//why?

                        }

                        else {
*/
                            $settings['posts_per_page'] = (int) get_option( 'posts_per_page' );
/*
                        }

                  }
*/
                  // If IS is set to click, and if the site owner changed posts_per_page, let's use that
                  if ( 'click' == $settings['type'] && ( '10' !== get_option( 'posts_per_page' ) ) ) {

                        $settings['posts_per_page'] = (int) get_option( 'posts_per_page' );
                  }

                  // Force display of the click handler and attendant bits when the type isn't `click`
                  if ( 'click' !== $settings['type'] ) {

                        $settings['click_handle'] = true;

                  }

                  // Store final settings in a class static to avoid reparsing
                  /**
                   * Filter the array of Infinite Scroll settings.
                   *
                   *
                   * @param array $settings Array of Infinite Scroll settings.
                   */
                  self::$settings = apply_filters( 'pc_infinite_scroll_settings', $settings );
            }

            /** This filter is documented in modules/infinite-scroll/infinity.php */
            return (object) apply_filters( 'pc_infinite_scroll_settings', self::$settings );
      }





      /**
       * Does the legwork to determine whether the feature is enabled.
       *
       * @return null
       */
      function action_template_redirect() {

            if ( !self::pc_infinite_is_ajax() ) {
              // Check that we support infinite scroll, and are on the home page.
              if ( ! current_theme_supports( 'pc-infinite-scroll' ) || ! self::archive_supports_infinity() )
                  return;
            }

            //the container id
            $id = self::get_settings()->container;

            // Check that we have an id.
            if ( empty( $id ) )
                  return;

            // Add our scripts.
            wp_register_script(
                'pc-infinite-scroll',
                sprintf('%1$sfront/assets/js/endlessly%2$s.js',trailingslashit( PC_INFINITE_SCROLL_BASE_URL ) , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array( 'jquery' ),
                (defined('WP_DEBUG') && true === WP_DEBUG ) ? '20190215' . time() : '420190215',
                true
            );

            // Add our default styles.
            wp_register_style(
                'pc-infinite-scroll',
                sprintf('%1$sfront/assets/css/endlessly%2$s.css',trailingslashit( PC_INFINITE_SCROLL_BASE_URL ) , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                array(),
                ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '20140422' . time() : '20140422'
            );

            // Make sure there are enough posts for IS
            if ( self::is_last_batch() )
                  return;

            // Add our scripts.
            wp_enqueue_script( 'pc-infinite-scroll' );

            // Add our default styles.
            wp_enqueue_style( 'pc-infinite-scroll' );


            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_spinner_scripts' ) );

            add_action( 'wp_footer', array( $this, 'action_wp_footer_settings' ), 2 );

            add_action( 'wp_footer', array( $this, 'action_wp_footer' ), 21 ); // Core prints footer scripts at priority 20, so we just need to be one later than that

            add_filter( 'infinite_scroll_results', array( $this, 'filter_infinite_scroll_results' ), 10, 3 );

      }



      /*****************************
      * Query processing helpers
      */

      /**
      * In case IS is activated on search page, we have to exclude initially loaded posts which match the keyword by title, not the content as they are displayed before content-matching ones
      *
      * @return array
      */
      function get_excluded_posts() {

            $excluded_posts = array();

            //loop through posts returned by wp_query call
            foreach( self::wp_query()->get_posts() as $post ) {

                  $orderby = isset( self::wp_query()->query_vars['orderby'] ) ? self::wp_query()->query_vars['orderby'] : '';
                  $post_date = ( ! empty( $post->post_date ) ? $post->post_date : false );

                  if ( 'modified' === $orderby || false === $post_date ) {

                        $post_date = $post->post_modified;

                  }


                  //in case all posts initially displayed match the keyword by title we add em all to excluded posts array
                  //else, we add only posts which are older than last_post_date param as newer are natually excluded by last_post_date condition in the SQL query
                  if ( self::has_only_title_matching_posts() || $post_date <= self::get_last_post_date() ) {

                    array_push( $excluded_posts, $post->ID );

                  }

            }

            return $excluded_posts;

      }




      /**
       * In case IS is active on search, we have to exclude posts matched by title rather than by post_content in order to prevent dupes on next pages
       *
       * @return array
       */
      function get_query_vars() {

            $query_vars = self::wp_query()->query_vars;

            //applies to search page only
            if ( true === self::wp_query()->is_search() ) {
                  //set post__not_in array in query_vars in case it does not exists
                  if ( false === isset( $query_vars['post__not_in'] ) ) {
                        $query_vars['post__not_in'] = array();
                  }

                  //get excluded posts
                  $excluded = self::get_excluded_posts();

                  //merge them with other post__not_in posts (eg.: sticky posts)
                  $query_vars['post__not_in'] = array_merge( $query_vars['post__not_in'], $excluded );

            }

            return $query_vars;
      }



      /**
       * This function checks whether all posts returned by initial wp_query match the keyword by title
       * The code used in this function is borrowed from WP_Query class where it is used to construct like conditions for keywords
       *
       * @return bool
       */
      function has_only_title_matching_posts() {

            //apply following logic for search page results only
            if ( false === self::wp_query()->is_search() ) {
                  return false;
            }

            //grab the last posts in the stack as if the last one is title-matching the rest is title-matching as well
            $post = end( self::wp_query()->posts );

            //code inspired by WP_Query class
            if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', self::wp_query()->get( 's' ), $matches ) ) {

                  $search_terms = self::wp_query()->query_vars['search_terms'];

                  // if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
                  if ( empty( $search_terms ) || count( $search_terms ) > 9 ) {

                        $search_terms = array( self::wp_query()->get( 's' ) );

                  }

            }
            else {

                  $search_terms = array( self::wp_query()->get( 's' ) );
            }

            //actual testing. As search query combines multiple keywords with AND, it's enough to check if any of the keywords is present in the title
            $term = current( $search_terms );
            if ( ! empty( $term ) && false !== strpos( $post->post_title, $term ) ) {

                  return true;

            }

            return false;
      }




      /**
       * Grab the timestamp for the initial query's last post.
       *
       * This takes into account the query's 'orderby' parameter and returns
       * false if the posts are not ordered by date.
       *
       * @return string 'Y-m-d H:i:s' or false
       */
      function get_last_post_date() {

            if ( self::got_infinity() )
                  return;

            if ( ! self::wp_query()->have_posts() ) {

                  return null;

            }

            //In case there are only title-matching posts in the initial WP_Query result, we don't want to use the last_post_date param yet
            if ( true === self::has_only_title_matching_posts() ) {

                  return false;

            }

            $post      = end( self::wp_query()->posts );
            $orderby   = isset( self::wp_query()->query_vars['orderby'] ) ? self::wp_query()->query_vars['orderby'] : '';
            $post_date = ( ! empty( $post->post_date ) ? $post->post_date : false );

            switch ( $orderby ) {

                  case 'modified':
                        return $post->post_modified;

                  case 'date':
                  case '':
                        return $post_date;

                  default:
                        return false;

            }

      }





      /**
       * Returns the appropriate `wp_posts` table field for a given query's
       * 'orderby' parameter, if applicable.
       *
       * @param optional object $query
       * @return string or false
       */
      function get_query_sort_field( $query = null ) {

            if ( empty( $query ) )
                  $query = self::wp_query();

            $orderby = isset( $query->query_vars['orderby'] ) ? $query->query_vars['orderby'] : '';

            switch ( $orderby ) {
                  case 'modified':
                        return 'post_modified';

                  case 'date':
                  case '':
                        return 'post_date';

                  default:
                        return false;
            }

      }





      /**
       * Create a where clause that will make sure post queries
       * will always return results prior to (descending sort)
       * or before (ascending sort) the last post date.
       *
       * @global $wpdb
       * @param string $where
       * @param object $query
       *
       * @filter posts_where
       * @return string
       */
      function query_time_filter( $where, $query ) {

            if ( self::got_infinity() ) {

                  global $wpdb;

                  $sort_field = self::get_query_sort_field( $query );

                  if ( false == $sort_field )
                        return $where;

                  $last_post_date = $_REQUEST['last_post_date'];

                  // Sanitize timestamp
                  if ( empty( $last_post_date ) || !preg_match( '|\d{4}\-\d{2}\-\d{2}|', $last_post_date ) )
                        return $where;

                  $operator = 'ASC' == $_REQUEST['query_args']['order'] ? '>' : '<';

                  // Construct the date query using our timestamp
                  $clause = $wpdb->prepare( " AND {$wpdb->posts}.{$sort_field} {$operator} %s", $last_post_date );


                  /**
                   * Filter Infinite Scroll's SQL date query making sure post queries
                   * will always return results prior to (descending sort)
                   * or before (ascending sort) the last post date.
                   *
                   * @param string $clause SQL Date query.
                   * @param object $query Query.
                   * @param string $operator Query operator.
                   * @param string $last_post_date Last Post Date timestamp.
                   */
                  $where .= apply_filters( 'infinite_scroll_posts_where', $clause, $query, $operator, $last_post_date );
            }

            return $where;

      }

      /**
       * Let's overwrite the default post_per_page setting to always display a fixed amount.
       *
       * @param object $query
       * @uses is_admin, self::archive_supports_infinity, self::get_settings
       * @return null
       */
      function posts_per_page_query( $query ) {

            if ( ! is_admin() && self::archive_supports_infinity() && $query->is_main_query() )
                  $query->set( 'posts_per_page', self::get_settings()->posts_per_page );

      }



      /**
       * Update the $query_args array with the parameters provided via AJAX/GET.
       *
       * @param array $query_args
       * @filter infinite_scroll_query_args
       * @return array
       */
      function inject_query_args( $query_args ) {

            /**
             * Filter the array of allowed Infinite Scroll query arguments.
             *
             * @module infinite-scroll
             *
             * @since 2.6.0
             *
             * @param array $args Array of allowed Infinite Scroll query arguments.
             * @param array $query_args Array of query arguments.
             */
            $allowed_vars = apply_filters( 'infinite_scroll_allowed_vars', array(), $query_args );

            $query_args = array_merge( $query_args, array(
                  'suppress_filters' => false,
            ) );

            if ( is_array( $_REQUEST[ 'query_args' ] ) ) {

                  foreach ( $_REQUEST[ 'query_args' ] as $var => $value ) {

                        if ( in_array( $var, $allowed_vars ) && ! empty( $value ) )
                              $query_args[ $var ] = $value;

                  }
            }

            return $query_args;

      }


  /**
   * Alias for renamed class method.
   *
   * Previously, JS settings object was unnecessarily output in the document head.
   * When the hook was changed, the method name no longer made sense.
   */
  function action_wp_head() {
    $this->action_wp_footer_settings();
  }





      /**
       * Prints the relevant infinite scroll settings in JS.
       *
       * @global $wp_rewrite
       * @uses self::get_settings, esc_js, esc_url_raw, self::has_wrapper, __, apply_filters, do_action, self::get_query_vars
       * @action wp_footer
       * @return string
       */
      function action_wp_footer_settings() {

            global $wp_rewrite;
            global $currentday;

            // Default click handle text
            $click_handle_text = __( 'Load more', 'hueman-pro' );

            // If a single CPT is displayed, use its plural name instead of "posts"
            // Could be empty (posts) or an array of multiple post types.
            // In the latter two cases cases, the default text is used, leaving the `infinite_scroll_js_settings` filter for further customization.
            $post_type = self::wp_query()->get( 'post_type' );

            // If it's a taxonomy, try to change the button text.
            if ( is_tax() ) {

                  // Get current taxonomy slug.
                  $taxonomy_slug = self::wp_query()->get( 'taxonomy' );

                  // Get taxonomy settings.
                  $taxonomy = get_taxonomy( $taxonomy_slug );

                  // Check if the taxonomy is attached to one post type only and use its plural name.
                  // If not, use "Posts" without confusing the users.
                  if ( count( $taxonomy->object_type ) < 2 ) {

                        $post_type = $taxonomy->object_type[0];

                  }
            }

            $post_type_name = '';
            if ( is_string( $post_type ) && ! empty( $post_type ) ) {

                  $post_type = get_post_type_object( $post_type );

                  if ( is_object( $post_type ) && ! is_wp_error( $post_type ) ) {

                        if ( isset( $post_type->labels->name ) ) {

                              $cpt_text = $post_type->labels->name;
                        }
                        elseif ( isset( $post_type->label ) ) {

                              $cpt_text = $post_type->label;
                        }

                        if ( isset( $cpt_text ) ) {
                              $post_type_name = strtolower( $post_type->labels->name );
                              $click_handle_text = sprintf( __( 'Older %s', 'hueman-pro' ), $cpt_text );
                              unset( $cpt_text );
                        }
                  }
            }

            unset( $post_type );

            // Base JS settings
            $js_settings = array(

                  'id'               => self::get_settings()->container,
                  'ajaxurl'          => esc_url_raw( self::ajax_url() ),
                  'type'             => esc_js( self::get_settings()->type ),
                  'isClickTypeOnMobile' => self::$isClickTypeOnMobile,
                  'isClickTypeOnDesktop' => self::$isClickTypeOnDesktop,
                  'wrapper'          => self::has_wrapper(),
                  'wrapper_class'    => is_string( self::get_settings()->wrapper ) ? esc_js( self::get_settings()->wrapper ) : 'infinite-wrap',
                  'footer'           => is_string( self::get_settings()->footer ) ? esc_js( self::get_settings()->footer ) : self::get_settings()->footer,

                  'handle'           => self::get_settings()->handle,
                  'appendHandleTo'   => self::get_settings()->appendHandleTo,
                  'click_handle'     => esc_js( self::get_settings()->click_handle ),

                  'text'             => apply_filters( 'czr_infinite_scroll_handle_text', esc_js( $click_handle_text ) ),
                  'totop'            => esc_js( __( 'Scroll back to top', 'hueman-pro' ) ),
                  'currentday'       => $currentday,
                  'order'            => 'DESC',
                  'scripts'          => array(),
                  'styles'           => array(),
                  'google_analytics' => true,//false,
                  'offset'           => self::wp_query()->get( 'paged' ),
                  'history'          => array(
                        'host'                 => preg_replace( '#^http(s)?://#i', '', untrailingslashit( esc_url( get_home_url() ) ) ),
                        'path'                 => self::get_request_path(),
                        'use_trailing_slashes' => $wp_rewrite->use_trailing_slashes,
                        'parameters'           => self::get_request_parameters(),
                  ),
                  'query_args'      => self::get_query_vars(),
                  'last_post_date'  => self::get_last_post_date(),
                  'body_class'      => self::body_class(),
                  //@pc_addon: this is passed to the js that will add it to the infinite query vars
                  //so that we can have this information when doing ajax.
                  //E.g. we use this info to exclude the sticky posts only in home (blog)
                  'is_home'         => is_home() ? 1 : 0,

                  'minWidthForDetermineUrl'      => self::get_settings()->minWidthForDetermineUrl,
                  // nov 2020 : added for https://github.com/presscustomizr/pro-bundle/issues/169
                  'postType'        => $post_type_name
            );

            // Optional order param
            if ( isset( $_REQUEST['order'] ) ) {

                  $order = strtoupper( $_REQUEST['order'] );

                  if ( in_array( $order, array( 'ASC', 'DESC' ) ) )
                        $js_settings['order'] = $order;

            }


            /**
             * Filter the Infinite Scroll JS settings outputted in the head.
             *
             *
             * @param array $js_settings Infinite Scroll JS settings.
             */
            $js_settings = apply_filters( 'infinite_scroll_js_settings', $js_settings );


            /**
             * Fires before Infinite Scroll outputs inline JavaScript in the head.
             *
             * @module infinite-scroll
             *
             * @since 2.0.0
             */
            do_action( 'infinite_scroll_wp_head' );

            ?>
            <script type="text/javascript">
                  //<![CDATA[
                  var infiniteScroll = <?php echo json_encode( array( 'settings' => $js_settings ) ); ?>;
                  //]]>
            </script>
            <?php
      }




  /**
   * Build path data for current request.
   * Used for Google Analytics and pushState history tracking.
   *
   * @global $wp_rewrite
   * @global $wp
   * @uses user_trailingslashit, sanitize_text_field, add_query_arg
   * @return string|bool
   */
  private function get_request_path() {
    global $wp_rewrite;

    if ( $wp_rewrite->using_permalinks() ) {
      global $wp;

      // If called too early, bail
      if ( ! isset( $wp->request ) )
        return false;

      // Determine path for paginated version of current request
      if ( false != preg_match( '#' . $wp_rewrite->pagination_base . '/\d+/?$#i', $wp->request ) )
        $path = preg_replace( '#' . $wp_rewrite->pagination_base . '/\d+$#i', $wp_rewrite->pagination_base . '/%d', $wp->request );
      else
        $path = $wp->request . '/' . $wp_rewrite->pagination_base . '/%d';

      // Slashes everywhere we need them
      if ( 0 !== strpos( $path, '/' ) )
        $path = '/' . $path;

      $path = user_trailingslashit( $path );
    } else {
      //@pc_addon: allow only string alike fields in the request
      //fixes https://github.com/presscustomizr/hueman-pro-addons/issues/93
      $path = array_filter( $_REQUEST, 'is_string' );

      // Clean up raw $_REQUEST input
      $path = array_map( 'sanitize_text_field', $path );
      $path = array_filter( $path );

      $path['paged'] = '%d';

      $path = add_query_arg( $path, '/' );
    }

    return empty( $path ) ? false : $path;
  }

  /**
   * Return query string for current request, prefixed with '?'.
   *
   * @return string
   */
  private function get_request_parameters() {
    $uri = $_SERVER[ 'REQUEST_URI' ];
    $uri = preg_replace( '/^[^?]*(\?.*$)/', '$1', $uri, 1, $count );
    if ( $count != 1 )
      return '';
    return $uri;
  }

  /**
   * Provide IS with a list of the scripts and stylesheets already present on the page.
   * Since posts may contain require additional assets that haven't been loaded, this data will be used to track the additional assets.
   *
   * @global $wp_scripts, $wp_styles
   * @action wp_footer
   * @return string
   */
  function action_wp_footer() {
    global $wp_scripts, $wp_styles;

    $scripts = is_a( $wp_scripts, 'WP_Scripts' ) ? $wp_scripts->done : array();
    /**
     * Filter the list of scripts already present on the page.
     *
     * @module infinite-scroll
     *
     * @since 2.1.2
     *
     * @param array $scripts Array of scripts present on the page.
     */
    $scripts = apply_filters( 'infinite_scroll_existing_scripts', $scripts );

    $styles = is_a( $wp_styles, 'WP_Styles' ) ? $wp_styles->done : array();
    /**
     * Filter the list of styles already present on the page.
     *
     * @module infinite-scroll
     *
     * @since 2.1.2
     *
     * @param array $styles Array of styles present on the page.
     */
    $styles = apply_filters( 'infinite_scroll_existing_stylesheets', $styles );

    ?><script type="text/javascript">
      jQuery.extend( infiniteScroll.settings.scripts, <?php echo json_encode( $scripts ); ?> );
      jQuery.extend( infiniteScroll.settings.styles, <?php echo json_encode( $styles ); ?> );
    </script><?php
  }

      /**
       * Identify additional scripts required by the latest set of IS posts and provide the necessary data to the IS response handler.
       *
       * @global $wp_scripts
       * @uses sanitize_text_field, add_query_arg
       * @filter infinite_scroll_results
       * @return array
       */
      function filter_infinite_scroll_results( $results, $query_args, $wp_query ) {

            // Don't bother unless there are posts to display
            if ( 'success' != $results['type'] )
                  return $results;

            // Parse and sanitize the script handles already output
            $initial_scripts = isset( $_REQUEST['scripts'] ) && is_array( $_REQUEST['scripts'] ) ? array_map( 'sanitize_text_field', $_REQUEST['scripts'] ) : false;

            if ( is_array( $initial_scripts ) ) {
              global $wp_scripts;

              // Identify new scripts needed by the latest set of IS posts
              $new_scripts = array_diff( $wp_scripts->done, $initial_scripts );

              // If new scripts are needed, extract relevant data from $wp_scripts
              if ( ! empty( $new_scripts ) ) {
                $results['scripts'] = array();

                foreach ( $new_scripts as $handle ) {
                  // Abort if somehow the handle doesn't correspond to a registered script
                  if ( ! isset( $wp_scripts->registered[ $handle ] ) )
                    continue;

                  // Provide basic script data
                  $script_data = array(
                    'handle'     => $handle,
                    'footer'     => ( is_array( $wp_scripts->in_footer ) && in_array( $handle, $wp_scripts->in_footer ) ),
                    'extra_data' => $wp_scripts->print_extra_script( $handle, false )
                  );

                  // Base source
                  $src = $wp_scripts->registered[ $handle ]->src;

                  // Take base_url into account
                  if ( strpos( $src, 'http' ) !== 0 )
                    $src = $wp_scripts->base_url . $src;

                  // Version and additional arguments
                  if ( null === $wp_scripts->registered[ $handle ]->ver )
                    $ver = '';
                  else
                    $ver = $wp_scripts->registered[ $handle ]->ver ? $wp_scripts->registered[ $handle ]->ver : $wp_scripts->default_version;

                  if ( isset( $wp_scripts->args[ $handle ] ) )
                    $ver = $ver ? $ver . '&amp;' . $wp_scripts->args[$handle] : $wp_scripts->args[$handle];

                  // Full script source with version info
                  $script_data['src'] = add_query_arg( 'ver', $ver, $src );

                  // Add script to data that will be returned to IS JS
                  array_push( $results['scripts'], $script_data );
                }
              }
            }

            // Expose additional script data to filters, but only include in final `$results` array if needed.
            if ( ! isset( $results['scripts'] ) )
                  $results['scripts'] = array();

            /**
             * Filter the additional scripts required by the latest set of IS posts.
             *
             *
             * @param array $results['scripts'] Additional scripts required by the latest set of IS posts.
             * @param array|bool $initial_scripts Set of scripts loaded on each page.
             * @param array $results Array of Infinite Scroll results.
             * @param array $query_args Array of Query arguments.
             * @param WP_Query $wp_query WP Query.
             */
            $results['scripts'] = apply_filters(

                  'infinite_scroll_additional_scripts',
                  $results['scripts'],
                  $initial_scripts,
                  $results,
                  $query_args,
                  $wp_query

            );

            if ( empty( $results['scripts'] ) )
                  unset( $results['scripts' ] );

            // Parse and sanitize the style handles already output
            $initial_styles = isset( $_REQUEST['styles'] ) && is_array( $_REQUEST['styles'] ) ? array_map( 'sanitize_text_field', $_REQUEST['styles'] ) : false;

            if ( is_array( $initial_styles ) ) {
              global $wp_styles;

              // Identify new styles needed by the latest set of IS posts
              $new_styles = array_diff( $wp_styles->done, $initial_styles );

              // If new styles are needed, extract relevant data from $wp_styles
              if ( ! empty( $new_styles ) ) {
                $results['styles'] = array();

                foreach ( $new_styles as $handle ) {
                  // Abort if somehow the handle doesn't correspond to a registered stylesheet
                  if ( ! isset( $wp_styles->registered[ $handle ] ) )
                    continue;

                  // Provide basic style data
                  $style_data = array(
                    'handle' => $handle,
                    'media'  => 'all'
                  );

                  // Base source
                  $src = $wp_styles->registered[ $handle ]->src;

                  // Take base_url into account
                  if ( strpos( $src, 'http' ) !== 0 )
                    $src = $wp_styles->base_url . $src;

                  // Version and additional arguments
                  if ( null === $wp_styles->registered[ $handle ]->ver )
                    $ver = '';
                  else
                    $ver = $wp_styles->registered[ $handle ]->ver ? $wp_styles->registered[ $handle ]->ver : $wp_styles->default_version;

                  if ( isset($wp_styles->args[ $handle ] ) )
                    $ver = $ver ? $ver . '&amp;' . $wp_styles->args[$handle] : $wp_styles->args[$handle];

                  // Full stylesheet source with version info
                  $style_data['src'] = add_query_arg( 'ver', $ver, $src );

                  // Parse stylesheet's conditional comments if present, converting to logic executable in JS
                  if ( isset( $wp_styles->registered[ $handle ]->extra['conditional'] ) && $wp_styles->registered[ $handle ]->extra['conditional'] ) {
                    // First, convert conditional comment operators to standard logical operators. %ver is replaced in JS with the IE version
                    $style_data['conditional'] = str_replace( array(
                      'lte',
                      'lt',
                      'gte',
                      'gt'
                    ), array(
                      '%ver <=',
                      '%ver <',
                      '%ver >=',
                      '%ver >',
                    ), $wp_styles->registered[ $handle ]->extra['conditional'] );

                    // Next, replace any !IE checks. These shouldn't be present since WP's conditional stylesheet implementation doesn't support them, but someone could be _doing_it_wrong().
                    $style_data['conditional'] = preg_replace( '#!\s*IE(\s*\d+){0}#i', '1==2', $style_data['conditional'] );

                    // Lastly, remove the IE strings
                    $style_data['conditional'] = str_replace( 'IE', '', $style_data['conditional'] );
                  }

                  // Parse requested media context for stylesheet
                  if ( isset( $wp_styles->registered[ $handle ]->args ) )
                    $style_data['media'] = esc_attr( $wp_styles->registered[ $handle ]->args );

                  // Add stylesheet to data that will be returned to IS JS
                  array_push( $results['styles'], $style_data );
                }
              }
            }

            // Expose additional stylesheet data to filters, but only include in final `$results` array if needed.
            if ( ! isset( $results['styles'] ) )
                  $results['styles'] = array();

            /**
             * Filter the additional styles required by the latest set of IS posts.
             *
             * @module infinite-scroll
             *
             * @since 2.1.2
             *
             * @param array $results['styles'] Additional styles required by the latest set of IS posts.
             * @param array|bool $initial_styles Set of styles loaded on each page.
             * @param array $results Array of Infinite Scroll results.
             * @param array $query_args Array of Query arguments.
             * @param WP_Query $wp_query WP Query.
             */
            $results['styles'] = apply_filters(
                  'infinite_scroll_additional_stylesheets',
                  $results['styles'],
                  $initial_styles,
                  $results,
                  $query_args,
                  $wp_query
            );



            if ( empty( $results['styles'] ) )
                  unset( $results['styles' ] );


            // Lastly, return the IS results array
            return $results;
      }



      /**
       * Runs the query and returns the results via JSON.
       * Triggered by an AJAX request.
       *
       * @return string or null
       */
      function query() {

            global $wp_version;

            if ( ! isset( $_REQUEST['page'] ) /*|| ! current_theme_supports( 'pc-infinite-scroll' ) */ )
                    die;

            $page = (int) $_REQUEST['page'];

            // Sanitize and set $previousday. Expected format: dd.mm.yy
            if ( preg_match( '/^\d{2}\.\d{2}\.\d{2}$/', $_REQUEST['currentday'] ) ) {

                    global $previousday;
                    $previousday = $_REQUEST['currentday'];

            }


            $post__not_in = self::wp_query()->get( 'post__not_in' );

            //we have to take post__not_in args into consideration here not only sticky posts
            if ( true === isset( $_REQUEST['query_args']['post__not_in'] ) ) {
                    $post__not_in = array_unique( array_merge( $post__not_in, array_map( 'intval', (array) $_REQUEST['query_args']['post__not_in'] ) ) );
            }

            //@pc_addon: exclude stickies only in home when not ignored
            //(meaning that they are prepended to the first page of the list of posts so they do not have to be in the middle of the infinite posts)
            if ( isset( $_REQUEST[ 'is_home' ] ) && $_REQUEST[ 'is_home' ] && !self::wp_query()->get( 'ignore_sticky_posts' ) ) {

                  $sticky = get_option( 'sticky_posts' );

                  if ( ! empty( $post__not_in ) ) {

                        $post__not_in = array_unique( array_merge( $sticky, $post__not_in ) );
                  }
                  else
                        $post__not_in = (array)$sticky;
            }



            $post_status = array( 'publish' );

            if ( current_user_can( 'read_private_posts' ) ) {

                  array_push( $post_status, 'private' );

            }


            $order = in_array( $_REQUEST['order'], array( 'ASC', 'DESC' ) ) ? $_REQUEST['order'] : 'DESC';

            $query_args = array_merge( self::wp_query()->query_vars, array(

                    'paged'          => $page,
                    'post_status'    => $post_status,
                    'posts_per_page' => self::get_settings()->posts_per_page,
                    'post__not_in'   => $post__not_in,
                    'order'          => $order

            ) );

            // 4.0 ?s= compatibility, see https://core.trac.wordpress.org/ticket/11330#comment:50
            if ( empty( $query_args['s'] ) && ! isset( self::wp_query()->query['s'] ) ) {

                    unset( $query_args['s'] );

            }

            // By default, don't query for a specific page of a paged post object.
            // This argument can come from merging self::wp_query() into $query_args above.
            // Since IS is only used on archives, we should always display the first page of any paged content.
            unset( $query_args['page'] );

            /**
             * Filter the array of main query arguments.
             *
             *
             * @param array $query_args Array of Query arguments.
             */
            $query_args = apply_filters( 'infinite_scroll_query_args', $query_args );

            // Add query filter that checks for posts below the date
            add_filter( 'posts_where', array( $this, 'query_time_filter' ), 10, 2 );

            //@pc_addon:
            do_action( 'pc_before_endlessly_query' );

            $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'] = new WP_Query( $query_args );

            //@pc_addon:
            do_action( 'pc_after_endlessly_query' );

            //@pc_addon:
            //fire ajax_query_ready which will cache the skope options
            //if not done yet, e.g. in the preview
            if ( !did_action( 'contextualizer_options_filters_setup' ) ) {
                  do_action( 'ajax_query_ready' );
            }

            remove_filter( 'posts_where', array( $this, 'query_time_filter' ), 10, 2 );

            //@pc_addon:
            //use the ArrayObject instead of a pure array, so that we can pass it by reference to the actions
            $results = new ArrayObject();

            if ( have_posts() ) {

                  // Fire wp_head to ensure that all necessary scripts are enqueued. Output isn't used, but scripts are extracted in self::action_wp_footer.
                  ob_start();
                  wp_head();
                  while ( ob_get_length() ) {

                        ob_end_clean();

                  }


                  $results['type'] = 'success';

                  /*
                  * We actually don't use a specific callback, we just filter the template in the render function
                  * so that we have the loop in one place only
                  */
                  // First, try theme's specified rendering handler, either specified via `add_theme_support` or by hooking to this action directly.
                  ob_start();
                  /**
                  * Fires when rendering Infinite Scroll posts.
                  *
                  */
                  do_action_ref_array( 'infinite_scroll_render', array( &$results ) );

                  $results['html'] = ob_get_clean();


                  // Fall back if a theme doesn't specify a rendering function. Because themes may hook additional functions to the `infinite_scroll_render` action, `has_action()` is ineffective here.
                  if ( empty( $results['html'] ) ) {

                          add_action( 'infinite_scroll_render', array( $this, 'render' ) );
                          rewind_posts();

                          ob_start();
                          /** This action is already documented in modules/infinite-scroll/infinity.php */
                          do_action_ref_array( 'infinite_scroll_render', array( &$results ) );

                          $results['html'] = ob_get_clean();

                  }


                  //@pc_addon:
                  if ( !empty( $results[ 'html' ] ) ) {

                        //store the container in the results set
                        $results['container'] = self::get_settings()->container;

                        //store the pagenum in the results set
                        $results['pageNum'] = $page;

                  }

                  // If primary and fallback rendering methods fail, prevent further IS rendering attempts. Otherwise, wrap the output if requested.
                  if ( empty( $results['html'] ) ) {

                        unset( $results['html'] );
                        /**
                         * Fires when Infinite Scoll doesn't render any posts.
                         */
                        do_action( 'infinite_scroll_empty' );
                        $results['type'] = 'empty';

                  }



                  // @pc_addon:
                  // do not add wrapper in customize preview to also prevent history paged pushing causing reload of the page
                  elseif ( !is_customize_preview() && $this->has_wrapper() ) {

                        $wrapper_classes = is_string( self::get_settings()->wrapper ) ? self::get_settings()->wrapper : 'infinite-wrap';
                        $wrapper_classes .= ' infinite-view-' . $page;
                        $wrapper_classes = trim( $wrapper_classes );

                        $results['html'] = '<div class="' . esc_attr( $wrapper_classes ) . '" id="infinite-view-' . $page . '" data-page-num="' . $page . '">' . $results['html'] . '</div>';

                  }



                  // Fire wp_footer to ensure that all necessary scripts are enqueued. Output isn't used, but scripts are extracted in self::action_wp_footer.
                  ob_start();
                  wp_footer();

                  while ( ob_get_length() ) {

                        ob_end_clean();

                  }


                  if ( 'success' == $results['type'] ) {

                        global $currentday;
                        $results['lastbatch'] = self::is_last_batch( self::wp_query()->query_vars[ 'posts_per_page' ] );
                        $results['currentday'] = $currentday;

                  }




                  // Loop through posts to capture sharing data for new posts loaded via Infinite Scroll
                  if ( 'success' == $results['type'] && function_exists( 'sharing_register_post_for_share_counts' ) ) {

                        global $jetpack_sharing_counts;

                        while( have_posts() ) {

                              the_post();

                              sharing_register_post_for_share_counts( get_the_ID() );

                        }

                        $results['postflair'] = array_flip( $jetpack_sharing_counts );

                  }

            }
            else {

                  /** This action is already documented in modules/infinite-scroll/infinity.php */
                  do_action( 'infinite_scroll_empty' );
                  $results['type'] = 'empty';

            }

            // This should be removed when WordPress 4.8 is released.
            if ( version_compare( $wp_version, '4.7', '<' ) && is_customize_preview() ) {

                  global $wp_customize;
                  $wp_customize->remove_preview_signature();

            }



            wp_send_json(
                  /**
                   * Filter the Infinite Scroll results.
                   *
                   * @param array $results Array of Infinite Scroll results.
                   * @param array $query_args Array of main query arguments.
                   * @param WP_Query $wp_query WP Query.
                   */
                  apply_filters( 'infinite_scroll_results', $results, $query_args, self::wp_query() )
            );

      }






      /**
       * Rendering fallback used when themes don't specify their own handler.
       *
       * @action infinite_scroll_render
       * echoes the rendered loop
       *
       * Note: infinite-scroll base code calls this inside an ob_(start|get_clean) block
       */
      function render( $results ) {

            /*
            * @pc_addon:
            * added:
            * - result collection field
            * - before and after action hooks ( pc__before_infinite_scroll_render_loop, pc__after_infinite_scroll_render_loop)
            * - befpre and after post filtered html ( pc_infinite_post_before, pc_infinite_post_after )
            * - template filter (  'pc_infinite_scroll_template' )
            * - wrap the post template loading into an ob_start/ob_end_clean block
            */
            $results['collection'] = isset( $results['collection'] ) && is_array( $results[ 'collection' ] ) ? $results[ 'collection' ] : array();


            do_action( 'pc__before_infinite_scroll_render_loop' );

            $template           = apply_filters( 'pc_infinite_scroll_template', null ); //HU_BASE . 'content.php' );
            $render_inner_loop  = self::get_settings() -> render_inner_loop;

            if ( !$render_inner_loop && $template ) {
              $callback = 'load_template';
              $args     = array( $template, false );
            }
            else if( $render_inner_loop ) {
              $callback = self::get_settings()->render_inner_loop;
              $args     = array();
            }
            else {
              return;
            }


            while ( have_posts() ) {

                  the_post();


                  ob_start();

                      call_user_func_array($callback, $args);

                  $_post_html = ob_get_clean();

                  $_post_id   = get_the_ID();

                  //add to the collection
                  $results['collection'][] = "#post-{$_post_id}";

                  /* Used for instance with the grid (not masonry) to render the row wrapper in Hueman Pro */
                  $before = apply_filters( 'pc_infinite_post_before', '', $template, $_post_html, $results[ 'collection' ] );
                  $after  = apply_filters( 'pc_infinite_post_after', '', $template, $_post_html, $results[ 'collection' ] );

                  echo $before.$_post_html.$after;

            }

            do_action( 'pc__after_infinite_scroll_render_loop' );

      }






      /* FOOTER stuff: We don't use this two functions atm */
      /**
       * The Infinite Blog Footer
       *
       * @return string or null
       */

      function footer() {

            // Bail if theme requested footer not show
            if ( false == self::get_settings()->footer )
                  return;

            // We only need the new footer for the 'scroll' type
            if ( 'scroll' != self::get_settings()->type || ! self::archive_supports_infinity() )
                  return;

            if ( self::is_last_batch() )
                  return;


            // Display a footer, either user-specified or a default
            if ( false !== self::get_settings()->footer_callback && is_callable( self::get_settings()->footer_callback ) ) {

                  call_user_func( self::get_settings()->footer_callback, self::get_settings() );

            }
            else {

                  self::default_footer();

            }

      }



      /* We don't use this atm */
      /**
       * Render default IS footer
       *
       * @return string
       */
      private function default_footer() {

            $credits = sprintf(
              '<a href="http://wordpress.org/" target="_blank" rel="generator">%1$s</a> ',
              __( 'Proudly powered by WordPress', 'hueman-pro' )
            );
            $credits .= sprintf(
              __( 'Theme: %1$s.', 'hueman-pro' ),
              function_exists( 'wp_get_theme' ) ? wp_get_theme()->Name : get_current_theme()
            );
            /**
             * Filter Infinite Scroll's credit text.
             *
             * @module infinite-scroll
             *
             * @since 2.0.0
             *
             * @param string $credits Infinite Scroll credits.
             */
            $credits = apply_filters( 'infinite_scroll_credit', $credits );

            ?>
            <div id="infinite-footer">
              <div class="container">
                <div class="blog-info">
                  <a id="infinity-blog-title" href="<?php echo home_url( '/' ); ?>" target="_blank" rel="home">
                    <?php bloginfo( 'name' ); ?>
                  </a>
                </div>
                <div class="blog-credits">
                  <?php echo $credits; ?>
                </div>
              </div>
            </div><!-- #infinite-footer -->
            <?php

      }



      /**
       * Our own Ajax response, avoiding calling admin-ajax
       */
      function ajax_response() {

            // Only proceed if the url query has a key of "Infinity"
            if ( ! self::got_infinity() )
                return false;

            // This should already be defined below, but make sure.
            if ( ! defined( 'DOING_AJAX' ) ) { define( 'DOING_AJAX', true ); }

            @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
            send_nosniff_header();

            /**
             * Fires at the end of the Infinite Scroll Ajax response.
             *
             * @module infinite-scroll
             *
             * @since 2.0.0
             */
            do_action( 'custom_ajax_infinite_scroll' );
            die( '0' );
      }




      /**
      ** HELPERS
      **/

      /**
       * Retrieve the query used with Infinite Scroll
       *
       * @return object
       */
      static function wp_query() {

            global $wp_the_query;
            /**
             * Filter the Infinite Scroll query object.
             *
             */
            return apply_filters( 'infinite_scroll_query_object', $wp_the_query );

      }




      /**
       * Update the $allowed_vars array with the standard WP public and private
       * query vars, as well as taxonomy vars
       *
       * @global $wp
       * @param array $allowed_vars
       * @filter infinite_scroll_allowed_vars
       * @return array
       */
      function allowed_query_vars( $allowed_vars ) {

          global $wp;

          $allowed_vars += $wp->public_query_vars;
          $allowed_vars += $wp->private_query_vars;
          $allowed_vars += $this->get_taxonomy_vars();

          foreach ( array_keys( $allowed_vars, 'paged' ) as $key ) {

                  unset( $allowed_vars[ $key ] );

          }

          return array_unique( $allowed_vars );

      }





      /**
       * Has infinite scroll been triggered?
       * @return bool
       */
      static function got_infinity() {
            /**
             * Filter the parameter used to check if Infinite Scroll has been triggered.
             *
             *
             * @param bool isset( $_GET[ 'infinity' ] ) Return true if the "infinity" parameter is set.
             */
            return apply_filters( 'infinite_scroll_got_infinity', isset( $_GET[ 'infinity' ] ) );
      }




      /**
       * Is this guaranteed to be the last batch of posts?
       * @return bool
       */
      static function is_last_batch( $posts_per_page = null ) {

            /**
            * Override whether or not this is the last batch for a request
            *
            *
            * @param bool|null null                 Bool if value should be overridden, null to determine from query
            * @param object    self::wp_query()     WP_Query object for current request
            * @param object    self::get_settings() Infinite Scroll settings
            */


            $override = apply_filters( 'infinite_scroll_is_last_batch', null, self::wp_query(), self::get_settings() );
            if ( is_bool( $override ) ) {

                  return $override;

            }



            $entries          = (int) self::wp_query()->found_posts;
            $posts_per_page   = is_null( $posts_per_page ) ? self::get_settings()->posts_per_page : $posts_per_page ;


            // This is to cope with an issue in certain themes or setups where posts are returned but found_posts is 0.
            if ( 0 == $entries ) {

                  return (bool) ( count( self::wp_query()->posts ) < $posts_per_page );

            }

            $paged            = self::wp_query()->get( 'paged' );

            // Are there enough posts for more than the first page?
            if ( $entries <= $posts_per_page ) {

                  return true;

            }

            // Calculate entries left after a certain number of pages
            if ( $paged && $paged > 1 ) {

                  $entries   -= $posts_per_page * $paged;

            }

            // Are there some entries left to display?
            return $entries <= 0;

      }




      /**
       * The more tag will be ignored by default if the blog page isn't our homepage.
       * Let's force the $more global to false.
       */
      function preserve_more_tag( $array ) {

            global $more;

            if ( self::got_infinity() )
                  $more = 0; //0 = show content up to the more tag. Add more link.

            return $array;
      }



      /**
       * Check if the IS output should be wrapped in a div.
       * Setting value can be a boolean or a string specifying the class applied to the div.
       *
       * @return bool
       */
      function has_wrapper() {

          return (bool) self::get_settings()->wrapper;

      }




      /**
       * Returns the Ajax url
       *
       * @return string
       */
      function ajax_url() {

          $base_url = set_url_scheme( home_url( '/' ) );

          $ajaxurl  = add_query_arg( array( 'infinity' => 'scrolling' ), $base_url );

          /**
           * Filter the Infinite Scroll Ajax URL.
           *
           * @param string $ajaxurl Infinite Scroll Ajax URL.
           */
          return apply_filters( 'infinite_scroll_ajax_url', $ajaxurl );

      }




      /**
       * Returns an array of stock and custom taxonomy query vars
       *
       * @global $wp_taxonomies
       * @return array
       */
      function get_taxonomy_vars() {
            global $wp_taxonomies;

            $taxonomy_vars = array();

            foreach ( $wp_taxonomies as $taxonomy => $t ) {

                  if ( $t->query_var )
                        $taxonomy_vars[] = $t->query_var;

            }

            // still needed?
            $taxonomy_vars[] = 'tag_id';

            return $taxonomy_vars;
      }




      /**
       * Allow plugins to filter what archives Infinite Scroll supports
       *
       * @return bool
       */
      public static function archive_supports_infinity() {

            $supported = current_theme_supports( 'pc-infinite-scroll' ) && ( is_home() || is_archive() || is_search() );

            // Disable when previewing a non-active theme in the customizer
            if ( is_customize_preview() && ! $GLOBALS['wp_customize']->is_theme_active() ) {

              return false;

            }

            /**
             * Allow plugins to filter what archives Infinite Scroll supports.
             *
             *
             * @param bool $supported Does the Archive page support Infinite Scroll.
             * @param object self::get_settings() IS settings provided by theme.
             */
            return (bool) apply_filters( 'infinite_scroll_archive_supported', $supported, self::get_settings() );

      }



      /*
      *  Functional callbacks and resource enqueuing
      */

      /**
       * Returns classes to be added to <body>. If it's enabled, 'infinite-scroll'. If set to continuous scroll, adds 'neverending' too.
       *
       * @since 4.7.0 No longer added as a 'body_class' filter but passed to JS environment and added using JS.
       *
       * @return string.
       */
      function body_class() {

            $classes = '';

            // Do not add infinity-scroll class if disabled through the Reading page
            $disabled = '' === get_option( self::$option_name_enabled ) ? true : false;

            if ( ! $disabled || 'click' == self::get_settings()->type ) {

                  $classes     = 'infinite-scroll';

                  if ( 'scroll' == self::get_settings()->type )
                    $classes  .= 'neverending';

            }

            return $classes;
      }




      /**
      * Enqueue spinner scripts.
      */
      function enqueue_spinner_scripts() {
            if ( ! wp_script_is( 'spin', 'registered' ) ) {
                  wp_register_script(
                      'spin',
                      sprintf('%1$sfront/assets/js/spin%2$s.js' , trailingslashit( PC_INFINITE_SCROLL_BASE_URL ) , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                      false,
                      ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '1.3' . time() : '1.3'
                  );
            }

            if ( ! wp_script_is( 'jquery.spin', 'registered' ) ) {
                  wp_register_script(
                      'jquery.spin',
                      sprintf('%1$sfront/assets/js/jqueryspin%2$s.js' , trailingslashit( PC_INFINITE_SCROLL_BASE_URL ) , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
                      array( 'jquery', 'spin' ),
                      ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '1.3' . time() : '1.3'
                  );
            }

            wp_enqueue_script( 'jquery.spin' );
      }




      /**
       * Ensure that IS doesn't interfere with Grunion by stripping IS query arguments from the Grunion redirect URL.
       * When arguments are present, Grunion redirects to the IS AJAX endpoint.
       *
       * @param string $url
       * @filter grunion_contact_form_redirect_url
       * @return string
       */
      public function filter_grunion_redirect_url( $url ) {

            // Remove IS query args, if present
            if ( false !== strpos( $url, 'infinity=scrolling' ) ) {

                  $url = remove_query_arg( array(

                        'infinity',
                        'action',
                        'page',
                        'order',
                        'scripts',
                        'styles'

                  ), $url );

            }

            return $url;
      }



      /**
      * @pc_addon
      * helper
      * Whether or not we are in the ajax context
      * @return bool
      */
      public static function pc_infinite_is_ajax() {
          /*
          * wp_doing_ajax() introduced in 4.7.0
          */
          $wp_doing_ajax = ( function_exists('wp_doing_ajax') && wp_doing_ajax() ) || ( ( defined('DOING_AJAX') && 'DOING_AJAX' ) );
          /*
          * https://core.trac.wordpress.org/ticket/25669#comment:19
          * http://stackoverflow.com/questions/18260537/how-to-check-if-the-request-is-an-ajax-request-with-php
          */
          $_is_ajax      = $wp_doing_ajax || ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
          return apply_filters( 'pc_infinite_is_ajax', $_is_ajax );
      }


}//end of class

endif;


/**
 * Early accommodation of the Infinite Scroll AJAX request
 */
if ( PC_infinite_scroll::got_infinity() ) {

      /**
       * If we're sure this is an AJAX request (i.e. the HTTP_X_REQUESTED_WITH header says so),
       * indicate it as early as possible for actions like init
       */
      if ( ! defined( 'DOING_AJAX' ) && isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtoupper( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'XMLHTTPREQUEST' ) {

        define( 'DOING_AJAX', true );

      }

      // Don't load the admin bar when doing the AJAX response.
      show_admin_bar( false );
}