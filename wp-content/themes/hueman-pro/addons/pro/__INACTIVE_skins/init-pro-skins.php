<?php
/**
* SKIN CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
final class PC_HASKINS {
    static $instance;

    function __construct () {
        self::$instance =& $this;


        add_filter( 'hu_general_design_sec'   , array( $this, 'ha_register_pro_skins_settings' ) );


        add_filter( 'body_class'              , array( $this , 'hu_add_body_class'), 100 );
        //Add properties to the server control params
        //- content picker nonce
        //add_filter( 'czr_js_customizer_control_params', array( $this, 'ha_add_control_params' ) );

        //The customizer slider ajax action
        //add_action( 'hu_hueman_loaded', array( $this,  'ha_hook_slider_ajax' ) );
    }//end of construct



    //hook : general_design_sec
    function ha_register_pro_skins_settings( $settings ) {
         return array_merge(  array(
            'pro_skins' => array(
                'default'   => 'none',
                'control'   => 'HU_controls',
                'label'     => __('Pick a predefined skin', 'hueman-pro'),
                'section'   => 'general_design_sec',
                'type'      => 'select',
                'choices' => array(
                    'none'    => __( 'None', 'hueman-pro' ),
                    'light'   => __( 'Light' , 'hueman-pro' ),
                    'dark'      => __( 'Dark' , 'hueman-pro' )
                ),
                'priority'  => 0,
                'notice'    => __( 'This will apply a predefined light or dark style to your website. You will be able to set more specific colors to your website in the other sections of the customizer : header, content, footer.' , 'hueman-pro')
            )
        ), $settings );
    }

    //hook filter : body_class
    function hu_add_body_class( $classes ) {
        if ( 'light' != hu_get_option( 'pro_skins' ) )
          return $classes;
        $classes = is_array( $classes ) ? $classes : array();
        array_push( $classes, 'pro-light' );
        return $classes;
    }



    function _print_dark_skin() {
        /* PRIMARY : #fff;
       SECONDARY : #fff;
       HEADER, TOPBAR, MENU : #fff;
       BACKGROUND : #f7f8f9;*/
      /* HEADER */
      ?>
        <style type="text/css">
          /* FLEXSLIDER*/
          #flexslider-featured .flex-control-nav li a.flex-active {
              background: #000;
          }
        </style>
      <?php
    }




    function _print_light_skin() {
        /* PRIMARY : #fff;
       SECONDARY : #fff;
       HEADER, TOPBAR, MENU : #fff;
       BACKGROUND : #f7f8f9;
       FOOTER : #202020
      HEADER */
      ?>
        <style type="text/css">
            @media only screen and (min-width: 720px) {
              #nav-header .nav li a {
                  color: #202020;
              }
            }
            @media only screen and (min-width: 720px) {
              #nav-header .nav li.current-menu-ancestor>a, #nav-header .nav li.current-menu-item>a, #nav-header .nav li.current-post-parent>a, #nav-header .nav li.current_page_item>a, #nav-header .nav li:hover>a, #nav-header .nav li>a:hover {
                  color: #000;
              }
            }
            #nav-header.nav-container {
                background-color: #ffffff;
                -webkit-box-shadow: none;
                box-shadow: none;
            }

            /* TOPBAR */
            @media only screen and (min-width: 720px) {
              #nav-topbar .nav li a {
                  color: #202020;
              }
            }
            .is-scrolled #header .nav-container.desktop-sticky, .is-scrolled #header .nav-container.mobile-sticky, .is-scrolled #header .search-expand {
                background-color: rgba(255, 255, 255, 0.95);
            }
            @media only screen and (min-width: 720px) {
              #nav-topbar .nav li.current-menu-ancestor>a, #nav-topbar .nav li.current-menu-item>a, #nav-topbar .nav li.current-post-parent>a, #nav-topbar .nav li.current_page_item>a, #nav-topbar .nav li:hover>a, #nav-topbar .nav li>a:hover {
                  color: #000;
              }
            }


            /* SEARCH BAR */
            .toggle-search {
                color: #202020;
                -webkit-box-shadow: -1px 0 0 rgba(0, 0, 0, 0.1);
                box-shadow: -1px 0 0 rgba(0, 0, 0, 0.1);
            }


            /* LINKS AND LINK ON HOVER : PRIMARY 1 */
            a, .themeform label .required, #flexslider-featured .flex-direction-nav .flex-next:hover, #flexslider-featured .flex-direction-nav .flex-prev:hover, .post-hover:hover .post-title a, .post-title a:hover, .s1 .post-nav li a:hover i, .content .post-nav li a:hover i, .post-related a:hover, .s1 .widget_rss ul li a, #footer .widget_rss ul li a, .s1 .widget_calendar a, #footer .widget_calendar a, .s1 .alx-tab .tab-item-category a, .s1 .alx-posts .post-item-category a, .s1 .alx-tab li:hover .tab-item-title a, .s1 .alx-tab li:hover .tab-item-comment a, .s1 .alx-posts li:hover .post-item-title a, #footer .alx-tab .tab-item-category a, #footer .alx-posts .post-item-category a, #footer .alx-tab li:hover .tab-item-title a, #footer .alx-tab li:hover .tab-item-comment a, #footer .alx-posts li:hover .post-item-title a, .comment-tabs li.active a, .comment-awaiting-moderation, .child-menu a:hover, .child-menu .current_page_item > a, .wp-pagenavi a {
                color: #000;
            }

            /* COMMENTS*/
            .post-comments {
              color: #202020;
            }
            .post-comments:hover {
                color: #000!important;
            }
            i.fa.fa-comments-o {
                color: #000;
            }

            #commentform {
                background: #f7f8f9;
            }
            .themeform button[type=submit], .themeform input[type=submit] {
              color: #202020;
            }
            /* MAIN */
            /*Background*/
            .col-3cm .main-inner {
            background:none;
            }
            .col-2cl .main-inner {
                background: none;
            }
            .sidebar-top p {
            color: #202020;
            }
            .sidebar .sidebar-content, .sidebar .sidebar-toggle {
            background:#fff;
            }

            .social-links .social-tooltip {
              color: #202020!important;
            }
            .social-links .social-tooltip:hover {
              color: #000!important;
            }
            /* FLEXSLIDER*/
            #flexslider-featured .flex-control-nav li a.flex-active {
                background: #000;
            }
            /* #FOOTER */
            #footer-bottom #back-to-top {
                background: #f7f8f9;
            }

        </style>
      <?php
    }
} //end of class