<?php
/**
* PRO INFINITE SCROLL INIT CLASS
*
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HAPINF {
      static $instance;

      public $infinite_class;//Will store the pro infinite scroll instance


      function __construct () {

            self::$instance     =& $this;

            add_action( 'hu_hueman_loaded'             , array( $this,  'set_on_hueman_loaded_hooks') );

            //add customizer settings
            add_filter( 'hu_content_blog_sec'          , array( $this, 'ha_register_pro_infinite_settings' ) );


            if ( ! defined( 'PC_INFINITE_BASE_URL' ) ) {
                  define( 'PC_INFINITE_BASE_URL' , HA_BASE_URL . 'addons/pro/infinite/' );
            }
            if ( ! defined( 'PC_INFINITE_BASE' ) ) {
                  define( 'PC_INFINITE_BASE' , dirname( __FILE__ ) );
            }


            //The animation is too expensive on mobile.
            //Disable it if wp_is_mobile().
            add_filter( 'hu_animate_on', array( $this, 'is_animation_on' ) );

            //TODO:
            //port this:
            //https://github.com/Automattic/jetpack/blob/f09f26fd08feff3cd6c042707aabd055fcb6e0c8/modules/infinite-scroll.php
      }//end of construct




      //hook : 'hu_hueman_loaded'
      //set up hooks
      function set_on_hueman_loaded_hooks() {

            //do nothing if in customizer preview
            if ( hu_is_customize_preview_frame() )
                  return;

            add_action( 'init'                           , array( $this, 'hap_infinite_scroll_class_and_functions' ), 20 );

            add_action( 'contextualizer_options_filters_setup'           , array( $this, 'hap_infinite_scroll_init' ) );
            /*
            * TODO: find a way, if possible, to set posts per page dynamically for the infinite
            * when the "fill the rows" is on.
            */
            add_action( 'contextualizer_options_filters_setup'           , array( $this, 'hap_maybe_regenerate_query_for_classic_grid' ), 50 );


            //disable pagination
            add_filter( 'hu_is_template_part_on'         , array( $this, 'hap_infinite_disable_pagination' ), 10, 2 );


            //NOTE: we don't need to do anything special as of now for which regards the img smartloading as we don't perform any
            //php substitution when doing an ajax call


            //filter infinite template (could even become a settings param )
            /* see infinite-scroll/class_infinite.php:render() */
            add_filter( 'pc_infinite_scroll_template'  , array( $this, 'hap_set_infinite_scroll_render_template' ) );

            /* see class_hueman_infinite.php:render() */
            add_filter( 'pc_infinite_post_before'      , array( $this, 'hap_set_before_infinite_post' ), 10, 2 );
            add_filter( 'pc_infinite_post_after'       , array( $this, 'hap_set_after_infinite_post' ), 10, 2 );


            //filter infinite scroll query args to set correct posts per page in the classic grid
            add_filter( 'infinite_scroll_query_args'   , array( $this, 'hap_maybe_alter_endlessly_query_args_for_classic_grid' ), 999 );


            /* TESTING PROPEDEUTICAL CSS */
            add_action( 'wp_head'                      , array( $this , 'hap_various_infinite_css' ), 9999 );



            /* TESTING PURPOSE APPEARING EFFECTS */
            if ( apply_filters( 'hu_animate_on', false ) ) {

                  add_action( 'wp_head'                      , array( $this , '_appearing_animation_css' ), 999 );

                  //testing purpose only
                  //animation should be fired after masonry otherwise waypoint can be triggered too much early
                  //masonry bricks are absolute, appended new elements might result in the viewport before masonry
                  //moves them (performing masonry('layout') )
                  //would be great having some sort of ordered callbacks (like wp hooks)
                  add_action( 'wp_footer'                    , array( $this , '_appearing_animation_js' ), 999999999 );

            }

            add_filter( 'body_class'      , array( $this, 'set_body_class' ), 99999 );
      }

      //hook : 'hu_animate_on'
      function is_animation_on() {
            return ! wp_is_mobile();
      }



      //hook : 'init'
      //Require Infinite scroll class and instantiate it
      function hap_infinite_scroll_class_and_functions() {
            require_once(  PC_INFINITE_BASE  . '/infinite-scroll/class_infinite.php' );
            $this->infinite_class = new PC_infinite_scroll( array(
                'type'              => 'scroll',
                'isClickTypeOnDesktop' => ! hu_is_checked( 'load_on_scroll_desktop' ),
                'isClickTypeOnMobile' => ! hu_is_checked( 'load_on_scroll_mobile' ),
                'handle'            => '<div id="infinite-handle"><a class="hph-cta btn btn-fill btn-skin btn-large" href="javascript:void(0)">{text}</a></div>',
                'appendHandleTo'    => '#grid-wrapper',
                'minWidthForDetermineUrl' => 1024//<= tablet landscape mode
            ));

      }

      //hook : 'body_class'
      function set_body_class( $classes ) {
          $classes = is_array( $classes ) ? $classes : array();
          if ( esc_attr( hu_get_option( 'infinite-scroll' ) ) ) {
            $classes[] = 'hu-infinite-scroll-on';
          }

          return $classes;
      }


      //hook : 'contextualizer_options_filters_setup'
      //Initialize HA_Infinite_Scroll
      function hap_infinite_scroll_init() {

            if ( esc_attr( hu_get_option( 'infinite-scroll' ) ) && $this->infinite_class && class_exists( 'PC_infinite_scroll' ) ) {

                  //TODO: DO THIS BETTER, meaning change the class_hueman_infinite.php
                  PC_infinite_scroll::$settings = null;

                  //we can pass settings to this
                  add_theme_support( 'pc-infinite-scroll', array(

                        //allow browser's history page numbers push for non masonry post lists
                        //this is possible by setting a wrapper
                        'wrapper' => 'masonry-grid' != esc_attr( hu_get_option( 'pro_post_list_design' ) )

                  ) );

                  //regenerate settings
                  PC_infinite_scroll::get_settings();

            }

      }




      //hook: 'contextualizer_options_filters_setup'
      //fill the blog grid (not masonry, not standard) last row
      function hap_maybe_regenerate_query_for_classic_grid() {

            //we do this only for the for archive/blog page
            //don't run this on the endlessly query
            //instead filter the args passed to WP_Query using the filter 'infinite_scroll_query_args'
            if ( did_action( 'pc_before_endlessly_query' ) )
                  return;

            //Bail if:
            // a) infinite scroll option not checked
            // or
            // b) is_admin
            // or
            // c) we're not in a post list context
            if ( ! esc_attr( hu_get_option( 'infinite-scroll' ) ) || is_admin() || !hu_is_post_list() )
                  return;

            //Do this only for main query and classical grid
            if ( 'classic-grid' != esc_attr( hu_get_option( 'pro_post_list_design' ) ) )
                  return;

            global $wp_query, $wp_the_query;

            if ( !$wp_query->is_main_query() )
                  return;

            /*
            * Maybe improve the algorithm to set a
            */
            //get the number of posts based on the
            //1) $wp_query->post_count
            //2) $query_vars['posts_per_page']
            $query_vars       = $wp_query->query_vars;

            $new_posts_per_page_param   = $this->hap_calculate_classic_grid_posts_per_page( $query_vars['posts_per_page'], $wp_query->post_count );



            if ( !$new_posts_per_page_param )
                  return;

            //rebuild the query;
            $query_args    =  array_merge( $query_vars, array( 'posts_per_page' => $new_posts_per_page_param ) );

            /*From jetpack infinite scroll */
            // 4.0 ?s= compatibility, see https://core.trac.wordpress.org/ticket/11330#comment:50
            if ( empty( $query_args['s'] ) && ! isset( $wp_query->query['s'] ) ) {

                  unset( $query_args['s'] );
            }

            // By default, don't query for a specific page of a paged post object.
            // This argument can come from merging self::wp_query() into $query_args above.
            // Since IS is only used on archives, we should always display the first page of any paged content.
            unset( $query_args['page'] );

            $wp_query = $wp_the_query = new WP_Query( $query_args );

      }



      //hook: 'infinite_scroll_query_args'
      //fill the blog grid (not masonry, not standard) last row
      function hap_maybe_alter_endlessly_query_args_for_classic_grid( $args ) {

            if ( !isset( $args['posts_per_page'] ) )
                  return $args;


            //we have to know if we are in a classic grid design otherwise we don't have to alter the args
            //this might be heavy but I don't have any other way to do this at the moment
            //We have to build the query and skope the options.
            //would be great if we could skope just the options we need
            //Another idea would be to pass via js (in class_hueman_infinite.php) something like the "current skope id"
            //and be able to retrieve the skoped options without:
            //1) run any query
            //and maybe
            //2) cache the skope
            global $wp_query, $wp_the_query;
            $wp_the_query = $wp_query = new WP_Query( $args );

            //fire ajax_query_ready which will cache the skope options
            //if not done yet, e.g. in the preview
            if ( !did_action( 'contextualizer_options_filters_setup' ) ) {
                  do_action( 'ajax_query_ready' );
            }

            //Do this only for main query and classical grid
            if ( 'classic-grid' != esc_attr( hu_get_option( 'pro_post_list_design' ) ) )
                  return $args;

            if ( !$wp_query->is_main_query() )
                  return $args;

            //we just want to alter the posts per page if
            $new_posts_per_page_param = $this->hap_calculate_classic_grid_posts_per_page( $args['posts_per_page'], $args['posts_per_page'] );

            if ( ! $new_posts_per_page_param )
                  return $args;

            $args['posts_per_page'] = $new_posts_per_page_param;

            return $args;

      }





      //hook : 'pc_infinite_scroll_template'
      function hap_set_infinite_scroll_render_template( $template ) {
            $post_list_type = esc_attr( hu_get_option( 'pro_post_list_design' ) );
            switch ( $post_list_type ) {
                  case 'masonry-grid'  : return ha_locate_template( 'addons/pro/grids/masonry/front/tmpl/masonry-article.php' );
                  case 'standard'      : return ha_locate_template( 'content-standard.php' );
                  default              : return ha_locate_template( 'content.php' );
            }
      }




      /* Start: the row wrapper */

      //hook : 'pc_infinite_post_before'
      // for the blog not standard, not masonry only
      function hap_set_before_infinite_post( $before, $template ) {

            if ( HU_BASE . 'content.php' != $template ) {

                  return $before;

            }

            $nb_columns = esc_attr( hu_get_option( 'pro_grid_columns' ) ); //grid columns
            $nb_columns = is_numeric( $nb_columns ) ? $nb_columns : 2;

            global $wp_query;

            return 0 == $wp_query->current_post || 0 == $wp_query->current_post % $nb_columns ? '<div class="post-row">' : $before;

      }




      //hook : 'pc_infinite_post_after'
      // for the blog not standard, not masonry only
      function hap_set_after_infinite_post( $after, $template ) {

            if ( HU_BASE . 'content.php' != $template ) {

                  return $after;

            }

            $nb_columns = esc_attr( hu_get_option( 'pro_grid_columns' ) ); //grid columns
            $nb_columns = is_numeric( $nb_columns ) ? $nb_columns : 2;

            global $wp_query;

            return  $wp_query->current_post == $wp_query -> post_count - 1  ||  ( $wp_query->current_post + 1 ) % $nb_columns == 0 ? '</div>' : $after;

      }

      /* End: the row wrapper */





      /*
      * Move this in front and perform it only when infinite is checked
      */
      //hook: 'hu_is_template_part_on'
      //disable pagination
      function hap_infinite_disable_pagination( $bool, $tmpl ) {

            if ( esc_attr( hu_get_option( 'infinite-scroll' ) ) )
                  return $bool && !( hu_is_post_list() && 'pagination' == $tmpl );

            return $bool;
      }







      //hook : hu_content_blog_sec
      function ha_register_pro_infinite_settings( $settings ) {

            $infinite_settings = array(
                  'infinite-scroll'  =>  array(
                        'default'   => false,
                        'control'   => 'HU_controls' ,
                        'title'     => __( 'Infinite scroll', 'hueman' ),
                        'label'     => __( 'Enable infinite scroll' , 'hueman' ),
                        'section'   => 'content_blog_sec' ,
                        'type'      => 'nimblecheck' ,
                        //'active_callback' => 'hu_is_post_list',
                        'priority'        => 23,
                        'notice'        => sprintf("%s<br />%s",
                                    __( 'When this option is enabled, your posts are revealed when scrolling down, from the most recent to the oldest one, like on a Facebook wall.', 'hueman' ),
                                    __( '<strong>Note :</strong> this setting is not applied when customizing, but will take effect on front end.', 'hueman' )
                        ),
                        //temporary hack
                        //since atm this option is not available in the preview, let's avoid refresh
                        'transport' => 'postMessage',
                        'ubq_section'   => array(
                            'section' => 'static_front_page',
                            'priority' => '12'
                        )
                  ),
                  'load_on_scroll_desktop'  =>  array(
                        'default'   => true,
                        'control'   => 'HU_controls' ,
                        'label'     => __( 'Desktop and laptop devices : when the infinite scroll is enabled, load posts automatically when scrolling.' , 'hueman' ),
                        'section'   => 'content_blog_sec',
                        'type'      => 'nimblecheck' ,
                        //'active_callback' => 'czr_fn_is_list_of_posts',
                        'priority'        => 24,
                        'notice'          =>   __( 'When this option is disabled, a "Load more posts" button will be printed.', 'hueman' ),
                        'ubq_section'   => array(
                                'section' => 'performance_sec',
                                'priority' => '60'
                            )
                  ),
                  'load_on_scroll_mobile'  =>  array(
                        'default'   => false,
                        'control'   => 'HU_controls' ,
                        'label'     => __( 'Mobile devices : when the infinite scroll is enabled, load posts automatically when scrolling.' , 'hueman' ),
                        'section'   => 'content_blog_sec',
                        'type'      => 'nimblecheck' ,
                        //'active_callback' => 'czr_fn_is_list_of_posts',
                        'priority'        => 24,
                        'notice'          =>   __( 'For better performances on mobile devices, we recommend to let this option unchecked.', 'hueman' ),
                        'ubq_section'   => array(
                                'section' => 'performance_sec',
                                'priority' => '60'
                            )
                  ),

            );

            return array_merge( $infinite_settings, $settings );

      }



      //helper
      //@return bool or int
      function hap_calculate_classic_grid_posts_per_page( $current_number_of_posts_set_to_retrieve, $current_number_of_posts_retrieved ) {
            /*
            * Maybe improve the algorithm to set a
            */
            //always fill the rows (2 columns by default )
            $nb_columns = esc_attr( hu_get_option( 'pro_grid_columns' ) ); //grid columns
            $nb_columns = is_numeric( $nb_columns ) ? $nb_columns : 2;

            if ( 0 == $current_number_of_posts_retrieved % $nb_columns )
                  return;

            //we don't want to risk that in the new query there's a sticky post
            //Example:
            //posts_per_page = 2
            //
            //old query: sticky-post_1, post1, post2
            //we want to make it:
            //
            //posts_per_page = 3
            //new query: sticky-post_1, post1, post2, post3 to fill the rows
            //
            //if post3 is a sticky-post (sticky-post_2) we'll end up with
            //new query: sticky-post_1, sticky_post2, post1, post2
            // and that's fine
            $posts_per_page =  $current_number_of_posts_set_to_retrieve + ( $nb_columns - ( $current_number_of_posts_retrieved % $nb_columns ) );

            return $posts_per_page;
      }


      //hook : wp_head
      function hap_various_infinite_css() {
            if ( !esc_attr( hu_get_option( 'infinite-scroll' ) ) )
              return;
            $prim_color = maybe_hash_hex_color( hu_get_option('color-1') );
            ?>
            <style id="infinite-css" type="text/css">

                  #grid-wrapper.post-list.group [class*="infinite-view-"] {
                        float: left;
                        width: 100%;
                  }


                  /* reset */
                  .post-list [class*="infinite-view-"] .post-row:last-child {
                      border-bottom: 1px solid #eee;
                      margin-bottom: 30px;
                  }

                  .post-list [class*="infinite-view-"]:last-of-type .post-row:last-child {
                      border-width: 0px;
                      margin-bottom: 0px;
                  }

                  /* add a little space at the bottom for the infinite handle button displayed in absolute positioning */
                  .hu-infinite-scroll-on #grid-wrapper {
                    padding-bottom: 50px;
                  }
                  #infinite-handle {
                      text-align: center;
                      width: 100%;
                      position: absolute;
                      bottom: 30px;
                  }
                  #infinite-handle .btn-skin {
                    background-color: <?php echo $prim_color;?>;
                  }
                  #infinite-handle a {
                      text-transform: uppercase;
                      font-size: 0.9em;
                  }
                  #infinite-handle a:hover{
                    color: #fff;
                    text-decoration: underline;
                  }

            </style>
            <?php
      }





      function _appearing_animation_css() {

            if ( !esc_attr( hu_get_option( 'infinite-scroll' ) ) )
                  return;

            ?>
            <style id="appearing-animation-css" type="text/css">
                   /* Bottom to top keyframes */
                  @-webkit-keyframes btt-fade-in {
                        from{ -webkit-transform: translate3d(0, 100%, 0); opacity: 0; }
                        99% { -webkit-transform: translate3d(0, 0, 0); }
                        to { opacity: 1; }
                  }
                  @-moz-keyframes btt-fade-in {
                        from{ -moz-transform: translate3d(0, 100%, 0); opacity: 0; }
                        99% { -moz-transform: translate3d(0, 0, 0); }
                        to { opacity: 1; }
                  }

                  @-o-keyframes btt-fade-in {
                        from{ -o-transform: translate3d(0, 100%, 0); opacity: 0; }
                        99% { -o-transform: translate3d(0, 0, 0); }
                        to { opacity: 1; }
                  }

                  @keyframes btt-fade-in {
                        from { transform: translate3d(0, 100%, 0); opacity: 0; }
                        99% { transform: translate3d(0, 0, 0); }
                        to { opacity: 1; }
                  }
                  /*
                  * Hack: since ie11 doesn't animate 3d transforms in the right way
                  * with this specific vendor we override the non prefixes keyframes btt-in
                  * only for ms
                  */
                  @-ms-keyframes btt-fade-in {
                        from { transform: translate(0, 100%);  opacity: 0; }
                        99% { transform: translate(0, 0); }
                        to { opacity: 1; }
                  }



                  /* to allow the post-inner border and box shadow */
                  #grid-wrapper .grid-item  { overflow: visible; }

                  /* apply the overflow hidden to the post-inner as we had to remove from the article.grid-item
                  * see rule above
                  */
                  #grid-wrapper .post-row  {  overflow: hidden; }
                  /* apply the overflow hidden to the post-inner as we had to remove from the article.grid-item
                  * see rule above
                  */
                  #grid-wrapper .grid-item .post-inner {
                        overflow: hidden;
                        opacity: 0;
                        -webkit-animation-duration: 0.8s;
                           -moz-animation-duration: 0.8s;
                             -o-animation-duration: 0.8s;
                                animation-duration: 0.8s;
                        -webkit-perspective: 1000;
                        -webkit-backface-visibility: hidden;
                           -moz-backface-visibility: hidden;
                             -o-backface-visibility: hidden;
                            -ms-backface-visibility: hidden;
                                backface-visibility: hidden;
                  -webkit-animation-timing-function: ease-in-out;
                     -moz-animation-timing-function: ease-in-out;
                       -o-animation-timing-function: ease-in-out;
                          animation-timing-function: ease-in-out;
                        -webkit-animation-fill-mode: forwards;
                           -moz-animation-fill-mode: forwards;
                             -o-animation-fill-mode: forwards;
                                animation-fill-mode: forwards;
                  }

                  /*
                  * Consider to use modernizr for feature detection
                  */
                  .no-cssanimations #grid-wrapper .grid-item .post-inner { opacity: 1;}

                  /*
                  * .start_animation here is "hardcoded",
                  * we might want to have different animations in the future
                  */
                  #grid-wrapper .grid-item .post-inner.start_animation {
                        -webkit-animation-name: btt-fade-in;
                           -moz-animation-name: btt-fade-in;
                             -o-animation-name: btt-fade-in;
                                animation-name: btt-fade-in;
                                overflow: hidden;
                  }

                  #grid-wrapper .grid-item .post-inner.end_animation {opacity: 1;}

            </style>
            <?php
      }



      //hook : wp_footer
      function _appearing_animation_js() {

            if ( !esc_attr( hu_get_option( 'infinite-scroll' ) ) )
                  return;

            ?>
            <script id="appearing-animation-js" type="text/javascript">
/*****
Modernizr cssanimation test
****/
/*! modernizr 3.4.0 (Custom Build) | MIT *
 * https://modernizr.com/download/?-cssanimations-setclasses !*/
!function(e,n,t){function r(e,n){return typeof e===n}function o(){var e,n,t,o,s,i,a;for(var l in S)if(S.hasOwnProperty(l)){if(e=[],n=S[l],n.name&&(e.push(n.name.toLowerCase()),n.options&&n.options.aliases&&n.options.aliases.length))for(t=0;t<n.options.aliases.length;t++)e.push(n.options.aliases[t].toLowerCase());for(o=r(n.fn,"function")?n.fn():n.fn,s=0;s<e.length;s++)i=e[s],a=i.split("."),1===a.length?Modernizr[a[0]]=o:(!Modernizr[a[0]]||Modernizr[a[0]]instanceof Boolean||(Modernizr[a[0]]=new Boolean(Modernizr[a[0]])),Modernizr[a[0]][a[1]]=o),C.push((o?"":"no-")+a.join("-"))}}function s(e){var n=_.className,t=Modernizr._config.classPrefix||"";if(x&&(n=n.baseVal),Modernizr._config.enableJSClass){var r=new RegExp("(^|\\s)"+t+"no-js(\\s|$)");n=n.replace(r,"$1"+t+"js$2")}Modernizr._config.enableClasses&&(n+=" "+t+e.join(" "+t),x?_.className.baseVal=n:_.className=n)}function i(e,n){return!!~(""+e).indexOf(n)}function a(){return"function"!=typeof n.createElement?n.createElement(arguments[0]):x?n.createElementNS.call(n,"http://www.w3.org/2000/svg",arguments[0]):n.createElement.apply(n,arguments)}function l(e){return e.replace(/([a-z])-([a-z])/g,function(e,n,t){return n+t.toUpperCase()}).replace(/^-/,"")}function u(e,n){return function(){return e.apply(n,arguments)}}function f(e,n,t){var o;for(var s in e)if(e[s]in n)return t===!1?e[s]:(o=n[e[s]],r(o,"function")?u(o,t||n):o);return!1}function c(e){return e.replace(/([A-Z])/g,function(e,n){return"-"+n.toLowerCase()}).replace(/^ms-/,"-ms-")}function d(n,t,r){var o;if("getComputedStyle"in e){o=getComputedStyle.call(e,n,t);var s=e.console;if(null!==o)r&&(o=o.getPropertyValue(r));else if(s){var i=s.error?"error":"log";s[i].call(s,"getComputedStyle returning null, its possible modernizr test results are inaccurate")}}else o=!t&&n.currentStyle&&n.currentStyle[r];return o}function p(){var e=n.body;return e||(e=a(x?"svg":"body"),e.fake=!0),e}function m(e,t,r,o){var s,i,l,u,f="modernizr",c=a("div"),d=p();if(parseInt(r,10))for(;r--;)l=a("div"),l.id=o?o[r]:f+(r+1),c.appendChild(l);return s=a("style"),s.type="text/css",s.id="s"+f,(d.fake?d:c).appendChild(s),d.appendChild(c),s.styleSheet?s.styleSheet.cssText=e:s.appendChild(n.createTextNode(e)),c.id=f,d.fake&&(d.style.background="",d.style.overflow="hidden",u=_.style.overflow,_.style.overflow="hidden",_.appendChild(d)),i=t(c,e),d.fake?(d.parentNode.removeChild(d),_.style.overflow=u,_.offsetHeight):c.parentNode.removeChild(c),!!i}function y(n,r){var o=n.length;if("CSS"in e&&"supports"in e.CSS){for(;o--;)if(e.CSS.supports(c(n[o]),r))return!0;return!1}if("CSSSupportsRule"in e){for(var s=[];o--;)s.push("("+c(n[o])+":"+r+")");return s=s.join(" or "),m("@supports ("+s+") { #modernizr { position: absolute; } }",function(e){return"absolute"==d(e,null,"position")})}return t}function v(e,n,o,s){function u(){c&&(delete N.style,delete N.modElem)}if(s=r(s,"undefined")?!1:s,!r(o,"undefined")){var f=y(e,o);if(!r(f,"undefined"))return f}for(var c,d,p,m,v,g=["modernizr","tspan","samp"];!N.style&&g.length;)c=!0,N.modElem=a(g.shift()),N.style=N.modElem.style;for(p=e.length,d=0;p>d;d++)if(m=e[d],v=N.style[m],i(m,"-")&&(m=l(m)),N.style[m]!==t){if(s||r(o,"undefined"))return u(),"pfx"==n?m:!0;try{N.style[m]=o}catch(h){}if(N.style[m]!=v)return u(),"pfx"==n?m:!0}return u(),!1}function g(e,n,t,o,s){var i=e.charAt(0).toUpperCase()+e.slice(1),a=(e+" "+P.join(i+" ")+i).split(" ");return r(n,"string")||r(n,"undefined")?v(a,n,o,s):(a=(e+" "+z.join(i+" ")+i).split(" "),f(a,n,t))}function h(e,n,r){return g(e,t,t,n,r)}var C=[],S=[],w={_version:"3.4.0",_config:{classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0},_q:[],on:function(e,n){var t=this;setTimeout(function(){n(t[e])},0)},addTest:function(e,n,t){S.push({name:e,fn:n,options:t})},addAsyncTest:function(e){S.push({name:null,fn:e})}},Modernizr=function(){};Modernizr.prototype=w,Modernizr=new Modernizr;var _=n.documentElement,x="svg"===_.nodeName.toLowerCase(),b="Moz O ms Webkit",P=w._config.usePrefixes?b.split(" "):[];w._cssomPrefixes=P;var z=w._config.usePrefixes?b.toLowerCase().split(" "):[];w._domPrefixes=z;var E={elem:a("modernizr")};Modernizr._q.push(function(){delete E.elem});var N={style:E.elem.style};Modernizr._q.unshift(function(){delete N.style}),w.testAllProps=g,w.testAllProps=h,Modernizr.addTest("cssanimations",h("animationName","a",!0)),o(),s(C),delete w.addTest,delete w.addAsyncTest;for(var T=0;T<Modernizr._q.length;T++)Modernizr._q[T]();e.Modernizr=Modernizr}(window,document);

  ( function() {
  var _fireWhenCzrAppReady = function() {
          jQuery( function($){
                var animationEnd              = 'webkitAnimationEnd animationend msAnimationEnd oAnimationEnd',
                    wrapperSelector           = '#grid-wrapper',
                    animatableSelector        = '.post-inner',
                    animatableParentSelector  = '.grid-item',
                    $_container               = $( wrapperSelector );

                if ( !$_container.length )
                      return;

                var   $_collection      = $( animatableParentSelector, $_container ),
                      $_featured_slider = $('#flexslider-featured');

                //Wait for the featured slider ready if any
                //to avoid elements to be animated too early
                if ( $_featured_slider.length ) {
                      $_featured_slider.on( 'featured-slider-ready', function() {
                            animateMe(
                                $_collection,
                                $_container,
                                animatableSelector,
                                animatableParentSelector
                            );
                      });
                }                             //wait for masonry init before animate
                else if ( $_container.hasClass( 'masonry' ) ) {
                      $_container.on( 'masonry-init.hueman', function() {
                            animateMe(
                                $_collection,
                                $_container,
                                animatableSelector,
                                animatableParentSelector
                            );
                      });

                } else {
                      animateMe(
                          $_collection,
                          $_container,
                          animatableSelector,
                          animatableParentSelector
                      );
                }

                var _event = $_container.hasClass( 'masonry' ) ? 'masonry.hueman' : 'post-load';

                //maybe animate infinite appended elements
                $('body').on( _event, function( e, response ) {
                      if ( 'success' == response.type && response.collection && response.container ) {
                            animateMe(
                                response.collection,
                                $( '#'+response.container ), //_container
                                animatableSelector,//_to_animate_selector
                                animatableParentSelector//_to_animate_parent_selector
                            );
                      }
                } );



                /*
                * params:
                * _collection                  : an object of the type { id : element [...] } || a jquery object (e.g. list of jquery elements)
                * _container                   : the jquery container element or the items to animate, or the selector
                * _to_animate_selector         : item selector to animate
                * _to_animate_parent_selector  : item to animate parent selector
                */
                function animateMe( _collection, _container, _to_animate_selector, _to_animate_parent_selector, type ) {
                      var   $_container        = $(_container),
                            collection         = null;

                      //from array of jquery elements to collection ?
                      //create an array of selectors
                      if ( _collection instanceof jQuery || 'object' !== typeof _collection ) {
                            collection = _.chain( $( _to_animate_parent_selector, $_container ) )
                                  .map( function( _element ) {
                                        return '#' + $(_element).attr( 'id' );
                                  })
                                  //remove falsy
                                  .compact()
                                  //values the chain
                                  .value();
                      }
                      else {
                            collection = _collection;
                      }


                      if ( 'object' !== typeof collection ) {
                            return;
                      }

                      type = type || 'advanced-animation';//simple-animation';

                      $( wrapperSelector ).addClass( type );
                      /*
                      * see boxAnimation function in library/js/app.js in the theme you know
                      */
                      var   $allItems    = _.size( collection ),
                            startIndex   = 0,
                            shown        = 0,
                            index        = 0,
                            sequential   = true;

                      var _simpleAnimation = function( elementSelector ) {
                            $(  elementSelector, $_container).find( animatableSelector ).css( 'opacity' , 1 );
                      };

                      var _advancedAnimation = function( elementSelector ) {
                            //store the collection index into the element to animate
                            var $_to_animate = $(  elementSelector + ' ' + _to_animate_selector , $_container);

                            if ( $_to_animate.hasClass( 'end_animation' ) ) {
                                  return;//continue
                            }

                            $_to_animate.attr('data-collection-index', index );

                            new Waypoint({

                                  element: $( elementSelector, $_container ),
                                  handler: function() {
                                        var   element = $( _to_animate_selector, this.element),
                                              parent  = $(this.element),
                                              currentIndex,
                                              isLast;

                                        //in case posts are per row the delay is based on the index in the row
                                        if ( parent.parent('[class*=grid-cols].row-fluid').length ) {

                                              currentIndex = parent.index();
                                              isLast       = parent.is(':last-child');
                                        } else {
                                              currentIndex = element.attr('data-collection-index');
                                              isLast       = false
                                        }

                                        //testing purpose
                                       // element.attr('data-index', currentIndex );
                                        var  delay = (!sequential) ? index : ((startIndex !== 0) ? currentIndex - $allItems : currentIndex),
                                            delayAttr = parseInt(element.attr('data-delay'));

                                        if (isNaN(delayAttr)) delayAttr = 100;
                                        delay -= shown;

                                        var objTimeout = setTimeout(function() {

                                              //replace start_animation with an animation class
                                              //the animationEnd routine is needed only because
                                              //IS removes not visible nodes (in classical grid and classical blog)
                                              //and re-adds them when needed. In the latter case, a new animation
                                              //will be triggered,
                                              element.addClass('start_animation')
                                                    .on( animationEnd, function(evt) {
                                                          if ( element.get()[0] == evt.target ) {
                                                                element.removeClass('start_animation')
                                                                       .addClass('end_animation');
                                                                element.parent(_to_animate_parent_selector).removeClass( 'infinite-doing-animation' );
                                                          }
                                                    })
                                                    .parent(_to_animate_parent_selector).addClass( 'infinite-doing-animation' );
                                              shown = isLast ? 0 : currentIndex;

                                        }, delay * delayAttr );

                                        parent.data('objTimeout', objTimeout);
                                        this.destroy();
                                  },//end handler

                                  offset: '150%'//might be tied to a fn() of matchMedia and user choosen grid type in the future

                            }).context.refresh(); //end Waypoint

                            index++;
                      };

                      //Fire an animation
                      _.each( collection, function( elementSelector ) {
                            if ( 'simple-animation' == type ) {
                                  _simpleAnimation( elementSelector );
                            } else {
                                  _advancedAnimation( elementSelector );
                            }

                      });//end each on the collection
                };//end animateMe
            });//jQuery
      };//_fireWhenCzrAppReady

      if ( window.czrapp && czrapp.ready && 'resolved' === czrapp.ready.state() ) {
            _fireWhenCzrAppReady();
      } else {
            document.addEventListener('czrapp-is-ready', _fireWhenCzrAppReady );
      }
  })();
            </script>
            <?php
      }//end function


} //end of class