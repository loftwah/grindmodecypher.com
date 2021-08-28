<?php
//INSTANTIATED ON 'hu_hueman_loaded'
//AND if ( 'slider' != hu_get_option( 'pro_header_type' ) )


/**
* FRONT END CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
class PC_HAP_front {

    //Access any method or var of the class with classname::$instance -> var or method():
    static $instance;
    public $current_effect;
    public $model;

    function __construct () {
        self::$instance     =& $this;
        add_action('template_redirect', array( $this, 'set_hooks_and_model') );
    }//end of construct


    //hook : template_redirect
    function set_hooks_and_model() {
        // Before June 2018, we checked on the skoped option 'pro_header_type', a select with 2 choices : 'classical' and 'slider'
        // if ( 'slider' != hu_get_option( 'pro_header_type') )
        //     return;

        // Starting from June 2018, the condition is a contextualized select option
        // yes, or inherit => slider can be displayed
        $display_slider_opt = hu_get_option( 'display-a-pro-header-slider');
        if ( 'yes' != $display_slider_opt && 'inherit' != $display_slider_opt )
          return;

        /* store the pro_header_model when skope is ready */
        //add_action( 'template_redirect', array( $this, '_set_pro_header_model') );
        $this -> _set_pro_header_model();

        //add_filter( 'tc_user_options_style'    , array( $this , 'tc_gc_write_inline_css'), 100 );
        add_filter( 'body_class'               , array( $this , 'hu_add_body_class'), 100 );
        //overrides the default template
        add_filter( 'hu_tmpl_header-main'      , array( $this , 'hu_load_custom_header_tmpl'));

        add_action( '__after_header'           , array( $this , 'hu_setup_pro_header_slider_view'));

        //add_filter( 'hph_background'           , array( $this, 'hu_set_slide_background'), 10, 2 );

        // if ( apply_filters('hph_video_background_on', false ) ) {
        //     //Load video slider functions
        //     require_once( HA_BASE_PATH . 'addons/pro/header/front/classes/hap-video-bg.php' );
        // }


        /* DEACTIVATION OF THE DEFAULT TITLE TMPL */
        add_filter( 'hu_is_template_part_on', array( $this, 'hu_deactivate_headings' ), 10, 2 );

        /* Write user option inline style */
        add_filter( 'ha_user_options_style', array( $this, 'ha_write_header_inline_css' ), 100 );

        //
        add_action( 'wp_enqueue_scripts', array( $this, 'ha_enqueue_pro_header_front_assets') );
    }



    /* ------------------------------------------------------------------------- *
     *  CACHE THE HEADER MODEL ON TEMPLATE REDIRECT
    /* ------------------------------------------------------------------------- */
    //hook : 'template_redirect' <= skope is ready and cached at this stage ( reminder, skope ready and cached on 'wp':99999 )
    //@return void(), simple cache the model in HU_AD -> models (array of models)
    function _set_pro_header_model() {
        HU_AD() -> ha_set_model( 'slider', array( $this, '_get_pro_header_model' ) );
    }



    /* ------------------------------------------------------------------------- *
     *  SET THE SLIDE BACKGROUD
    /* ------------------------------------------------------------------------- */
    //hook : hph_background
    function hu_set_slide_background( $slide_src, $slide_model ) {
        //&& current_user_can('edit_theme_options')
        //$_bg_html = ! is_user_logged_in() ? '' : sprintf( '<div><h2>%1$s</h2></div>', __( 'No background set for this slide yet', 'hueman' ) );
        $_bg_html = '';
        if ( apply_filters('hph_video_background_on', false ) && $this -> hu_has_valid_video_bg( $slide_model ) ) {
            $_bg_html = hu_set_ext_video_bg( $slide_src, $slide_model );
        } else if ( '_not_set_' != $slide_src && array_key_exists( 'slide-src' , $slide_model) )  {
            $_bg_html = $slide_model['slide-src'];
        }
        return apply_filters( 'hph_background', $_bg_html, $slide_src, $slide_model );
    }


    //Helper boolean
    function  hu_has_valid_video_bg( $slide_model ) {
        if ( ! is_array( $slide_model ) || ! array_key_exists( 'slide-video-bg', $slide_model ) )
          return;
        return ! empty( $slide_model['slide-video-bg'] );
    }





    //@return the model for our large header slider
    //=> will be get in the template with $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
    //The raw options are built this way :
    //Array(
    // 0 => array of mod_opt options
    // 1
    // 2
    // ... => slides description
    //)
    function _get_pro_header_model() {
        $default_slide_model = HU_AD() -> pro_header -> default_slide_model;
        $default_slider_options_model = HU_AD() -> pro_header -> default_slider_option_model;
        $pro_header_slider_short_opt_name = HU_AD() -> pro_header -> pro_header_slider_short_opt_name;//'pro_slider_header_bg'
        $db_opt = hu_get_option( $pro_header_slider_short_opt_name );
        $_pre_slides = array();
        $_slides = array();
        $use_contextual_data_on = true;
        $_m = array(
            'options' => $default_slider_options_model,
            'slides' => array()
        );

        // sek_error_log( 'DB OPTS', $db_opt);
        // sek_error_log('$this -> ha_get_default_contextual_slide_model()', $this -> ha_get_default_contextual_slide_model() );

        //If the option is not set yet ( this means that it is not locally set and not set by any parents ) , return the default option
        //+ the default slide item model
        if ( ! $db_opt || empty( $db_opt ) ) {
              $_pre_slides[] = $this -> ha_get_default_contextual_slide_model();
        } else {
            //populates the options
            foreach ( $db_opt as $_k => $data ) {
                if ( array_key_exists( 'is_mod_opt', $data ) ) {
                    $_m['options'] = wp_parse_args( $data, $default_slider_options_model );
                }
            }
            //update the use of contextual data now, will be used
            $use_contextual_data_on = true == hu_booleanize_checkbox_val( $_m['options']['use-contextual-data'] );

            //Then populates the slide items without the image src for the moment
            foreach ( $db_opt as $_k => $data ) {
                if ( ! array_key_exists( 'is_mod_opt', $data ) && ! empty( $data ) ) {
                    $data = wp_parse_args( $data, $default_slide_model );
                    //skip if we don't use the contextual data and the current slide is a default
                    // if ( true == $data['is_default'] && ! $use_contextual_data_on )
                    //   continue;
                    $_pre_slides[] = $data;
                }
            }
        }

        $local_skoped_opt = false;
        if ( ha_is_skop_on() ) {
            $local_skoped_opt = ctx_get_cached_skoped_opt_val( $pro_header_slider_short_opt_name, 'local', 'hu_theme_options') ;
        }

        // error_log( '<' . __CLASS__ . '::' . __FUNCTION__ . ' => SKOPED OPTIONS GROUP > LOCAL>' );
        // error_log( print_r( $db_opt , true ) );
        // error_log( '</' . __CLASS__ . '::' . __FUNCTION__ . ' => SKOPED OPTIONS GROUP > LOCAL>' );

        // error_log( '<' . __CLASS__ . '::' . __FUNCTION__ . ' => LOCAL PRO HEADER OPTION>' );
        // error_log( print_r( ctx_get_cached_skoped_opt_val( $local_skoped_opt, 'local', 'hu_theme_options' ), true ) );
        // error_log( '</' . __CLASS__ . '::' . __FUNCTION__ . ' => LOCAL PRO HEADER OPTION>' );

        // error_log( '<' . __CLASS__ . '::' . __FUNCTION__ . ' => GROUP PRO HEADER OPTION>' );
        // error_log( print_r( ctx_get_cached_skoped_opt_val( $pro_header_slider_short_opt_name, 'group', 'hu_theme_options' ), true ) );
        // error_log( '</' . __CLASS__ . '::' . __FUNCTION__ . ' => GROUP PRO HEADER OPTION>' );

        //If the local skope option is not set, let's check the parent setting to see which default slide we must use.
        if ( empty( $_pre_slides ) && ( false == $local_skoped_opt || '_no_set_' == $local_skoped_opt ) ) {
            if ( hu_can_have_default_slide_title() && $use_contextual_data_on ) {
                $_pre_slides[] = $this -> ha_get_default_contextual_slide_model();
            } else if ( hu_can_have_default_slide_title() && ! empty( $_m['options']['default-bg-img'] ) && $use_contextual_data_on  ) {
                $_slide = $this -> ha_get_default_contextual_slide_model();
                $_slide['background'] = $_m['options']['default-bg-img'];
                $_pre_slides[] = $_slide;
            } else if ( ! empty( $_m['options']['default-bg-img'] ) ) {
                $_pre_slides[] = wp_parse_args( array( 'slide-background'  => $_m['options']['default-bg-img'] ), $default_slide_model );
            }
        }


        //Processes the src.
        //sets specific attributes if lazy load is enabled
        if ( false != $_m['options']['lazyload'] ) {
            add_filter( 'wp_get_attachment_image_attributes', array( $this, 'hu_set_lazy_load_attributes'), 999 );
        } else {
            add_filter( 'wp_get_attachment_image_attributes', array( $this, 'hu_remove_srcset_attribute'), 999 );
        }

        foreach ( $_pre_slides as $_k => $s ) {
            $_slides[$_k] = $s;
            $_img_candidate = wp_get_attachment_image( $s['slide-background'] , 'full');
            $_slides[$_k]['slide-src'] = ( ! isset( $_img_candidate ) || empty( $_img_candidate ) ) ? '_not_set_' : $_img_candidate;
        }
        if ( false != $_m['options']['lazyload'] ) {
            remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'hu_set_lazy_load_attributes'), 999 );
        } else {
            remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'hu_remove_srcset_attribute'), 999 );
        }

        $_m['slides'] = $_slides;

        // sek_error_log( 'SLIDER MODEL', $_m);

        return apply_filters( 'ha_slider_model', $_m );
    }











    /* ------------------------------------------------------------------------- *
     *  DEFAULT CONTEXTUAL SLIDE MODEL
     *  => used as fallback on front end
     *  => and in the customizer, used as defaut item when slider not set yet
    /* ------------------------------------------------------------------------- */
    // DEFAULT SLIDE MODEL
    // 'id'                => '',
    // 'is_default'        => true,
    // 'title'             => '',
    // 'slide-background'  => '',
    // 'slide-src'         => '',
    // 'slide-title'       => '',
    // 'slide-subtitle'    => ''
    function ha_get_default_contextual_slide_model() {
        $_model = HU_AD() -> pro_header -> default_slide_model;
        $_model['id'] = 'default_item_pro_slider_header_bg_czr_module';
        $_model['is_default'] = true;
        $_model['slide-title'] = hu_set_hph_title( get_bloginfo('name') );
        $_model['slide-subtitle'] = hu_set_hph_subtitle( get_bloginfo('description') );
        if ( is_singular() &&  has_post_thumbnail() ) {
            $_model['slide-background'] = get_post_thumbnail_id();
        }
        return $_model;
    }



    /* ------------------------------------------------------------------------- *
     *  VIEWS
    /* ------------------------------------------------------------------------- */
    function ph_has_bg_class() {
        $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
        if ( ! is_array( $model ) || ! array_key_exists( 'slides', $model ) )
          return 'no-bg';

        return 0 == count( $model['slides'] ) ? 'no-bg' : '';
    }

    //hook : __after_header
    function hu_setup_pro_header_slider_view() {
        ?>
        <div id="ha-large-header" class="container-fluid section <?php echo $this -> ph_has_bg_class(); ?>">
          <?php
            // load_template( HA_BASE_PATH . 'addons/pro/header/front/tmpl/before-content-section.php', true );//true for require_once
            $this -> hu_print_pro_header_slider_tmpl();
          ?>
        </div>
        <?php
    }

    //hook : __after_header //__before_main or __before_content
    function hu_print_pro_header_slider_tmpl() {
          // load_template( HA_BASE_PATH . 'addons/pro/header/front/tmpl/before-content-section.php', true );//true for require_once
          ha_locate_template( 'addons/pro/header/front/tmpl/slider-tmpl.php', $load = true, $require_once = true );
    }


    //hook : hu_tmpl_header-main
    function hu_load_custom_header_tmpl() {
        $_full_path = ha_locate_template( 'addons/pro/header/front/tmpl/header-main.php' );
        if ( ! file_exists($_full_path) )
          return;
        return $_full_path;
    }



    /* ------------------------------------------------------------------------- *
     *  LAZY LOAD IMG FILTER
    /* ------------------------------------------------------------------------- */
    //hook : wp_get_attachment_image_attributes
    function hu_set_lazy_load_attributes( $attr ) {
        // if ( isset( $attr['class'] ) && 'custom-logo' === $attr['class'] ) {
        //     $attr['class'] = 'custom-logo foo-bar NEW CLASSES HERE';
        // }
        $attr = is_array( $attr ) ? $attr : array();
        $attr = wp_parse_args( $attr, array(
            'src' => '',
            'srcset' => '',
            'sizes' => '',
        ));

        $attr['data-flickity-lazyload'] = $attr['src'];
        // <img> element must have an attribute "src"
        $attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

        // the "img", sizes" and "srcset" attributes will be added back with js
        // @see =>  addons/pro/header/assets/front/hph-front.js
        // Feb 2021 => Removed srcset and imgsizes attributes server side to prevent poor image quality on mobiles when using on chrome ( and potentially other browsers )
        // see https://github.com/presscustomizr/hueman-pro-addons/issues/217
        //$attr['data-flickity-srcset'] = $attr['srcset'];
        //$attr['data-flickity-imgsizes'] = $attr['sizes'];

        unset($attr['srcset']);
        unset($attr['sizes']);
        return $attr;
    }

    /* ------------------------------------------------------------------------- *
     *  IMG FILTER TO REMOVE SRCSET AND IMGSIZES ATTRIBUTES
     * Feb 2021 => Removed srcset and imgsizes attributes server side to prevent poor image quality on mobiles when using on chrome ( and potentially other browsers )
     * see https://github.com/presscustomizr/hueman-pro-addons/issues/217
    /* ------------------------------------------------------------------------- */
    function hu_remove_srcset_attribute( $attr ) {
        $attr = is_array( $attr ) ? $attr : array();
        $attr = wp_parse_args( $attr, array(
            'src' => '',
            'srcset' => '',
            'sizes' => '',
        ));
        unset($attr['srcset']);
        unset($attr['sizes']);
        return $attr;
    }









    /* ------------------------------------------------------------------------- *
     *  DEACTIVATE THE DEFAULT HUEMAN HEADING
    /* ------------------------------------------------------------------------- */
    //hook : 'hu_is_template_part_on'
    function hu_deactivate_headings( $bool, $tmpl_name ) {
        //title case
        if ( 'page-title' == $tmpl_name && apply_filters( 'hph_use_title', true ) )
          return false;

        //page image case => disable the featured image of page post type
        if ( 'page-image' == $tmpl_name && apply_filters( 'hph_use_featured_image', true ) )
          return false;

        //single heading case => disable the default heading for single post
        if ( 'single-heading' == $tmpl_name && apply_filters( 'hph_use_title', true ) )
          return false;

        return $bool;
    }

















    /* ------------------------------------------------------------------------- *
     *  BODY CLASS
    /* ------------------------------------------------------------------------- */
    //hook body_class
    //@param $classes = array
    function hu_add_body_class( $classes) {
        array_push( $classes, 'pro-header-on' );
        $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
        $slider_opts  = $model['options'];
        $skin = 'dark';
        if ( ! is_array( $slider_opts ) || empty( $slider_opts ) ) {
            ha_error_log( 'In PC_HAP_front::hu_add_body_class : invalid model options' );
        } else {
            $skin = $slider_opts['skin'];
        }
        array_push( $classes, 'header-skin-' . $skin );
        return $classes;
    }





















    /* ------------------------------------------------------------------------- *
     *  DYNAMIC INLINE CSS
    /* ------------------------------------------------------------------------- */
    /* hook : ha_user_options_style
    * @return css string
    *  Pro header model :
    *  (
    //     [options] => Array
    //         (
    //             [is_mod_opt] => 1
    //             [module_id] =>
    //             [slider-speed] => 3
    //             [skin] =>
    //             [lazyload] => 1
    //         )

    //     [slides] => Array
    //         (
    //             [0] => Array
    //                 (
    //                     [id] =>
    //                     [title] =>
    //                     [slide-background] =>
    //                     [slide-src] => _not_set_
    //                     [slide-title] => Hueman
    //                     [slide-subtitle] => Inspire and Empower
                            'slide-cta'         : '',
              'slide-link'       : '',
              'slide-custom-link'  : ''
    //                 )
    //         )
    // )
    *
    **/
    function ha_write_header_inline_css( $_previous_css ) {
        $_new_css = '';
        $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
        $slider_opts  = $model['options'];

        if ( ! is_array( $slider_opts ) || empty( $slider_opts ) ) {
          ha_error_log( 'In PC_HAP_front::ha_write_header_inline_css : invalid model options' );
          return;
        }

        $is_full_height = 100 === esc_attr( $slider_opts['slider-height'] );
        $slider_height_style = $is_full_height ? '' : sprintf( 'height:%1$svh!important', is_numeric( $slider_opts['slider-height'] ) ? $slider_opts['slider-height'] : 100 );

        //LARGE HEADER HEIGHT
        if ( array_key_exists('slider-height', $slider_opts ) && 100 != $slider_opts['slider-height'] ) {
            $_new_css = sprintf("%s\n%s",
              $_new_css,
              "#ha-large-header .pc-section-slider { $slider_height_style }"
            );
        }

        // LARGE HEADER MIN HEIGHT ON MOBILE
        // implemented for https://github.com/presscustomizr/hueman-pro-addons/issues/197
        // starts working for tablets in landscape ( 1024px )
        // users can disable the default min-height by setting min height val to 0 or very low
        if ( array_key_exists('slider-min-height', $slider_opts ) ) {
            $min_height = esc_attr( $slider_opts['slider-min-height'] );
            $min_height = intval( $min_height );
            if ( 500 != $min_height ) {
                $_new_css = sprintf("%s\n%s",
                  $_new_css,
                  "@media only screen and (max-width: 1024px) { #ha-large-header .pc-section-slider {min-height:{$min_height}px; } }"
                );
            }
        }

        //CAPTION VERTICAL POSITION
        if ( array_key_exists('caption-vertical-pos', $slider_opts ) && 0 != $slider_opts['caption-vertical-pos'] ) {
            $_offset = $slider_opts['caption-vertical-pos'];
            $_offset = is_numeric( intval( $_offset ) ) ? intval( $_offset ) : 0;
            $_offset = abs( $_offset ) > 50 ? 0 : $_offset;
            $new_top = 50 - $_offset;
            $_new_css = sprintf("%s\n%s",
              $_new_css,
              '#ha-large-header .carousel-caption { top : ' . $new_top . '%}'
            );
        }

        //DEFAULT BACKGROUND COLOR HEIGHT
        if ( array_key_exists('default-bg-color', $slider_opts ) && '#00000' != $slider_opts['default-bg-color'] ) {
            $_new_css = sprintf("%s\n%s",
              $_new_css,
              '#ha-large-header .pc-section-slider { background-color : ' . $slider_opts['default-bg-color'] . '}'
            );
        }

        //SKIN CSS FILTER
        $a                  = array_key_exists( 'skin-opacity' , $slider_opts ) ? esc_attr( $slider_opts['skin-opacity'] ) : 65;
        $a                  = ( is_numeric( $a ) && $a < 100 && $a > 0 ) ? number_format( $a / 100, 2 ) : $a;
        $skin               = array_key_exists('skin' , $slider_opts ) ? esc_attr( $slider_opts['skin'] ) : 'dark';
        $rgb_color          = 'rgb(34,34,34)';
        $skin_custom_color  = array_key_exists( 'skin-custom-color' , $slider_opts ) ? esc_attr( $slider_opts['skin-custom-color'] ) : $rgb_color;

        //The skin custom color must be a rgb string, not a rgba
        $skin_custom_color = ( ! is_string( $skin_custom_color ) || false === strpos( $skin_custom_color, 'rgb' ) || false !== strpos( $skin_custom_color, 'rgba' ) ) ? $rgb_color : $skin_custom_color;

        //assign the rgb value depending on the 'skin' option
        switch( $skin ) {
            case 'dark' :
              $rgb_color = 'rgb(34,34,34)';
            break;
            case 'light' :
              $rgb_color = 'rgb(255,255,255)';
            break;
            case 'custom' :
              $rgb_color = $skin_custom_color;
            break;
        }

        //then set the transparency
        $rgba_color = str_replace( 'rgb', 'rgba' , $rgb_color );
        $rgba_color = str_replace( ')', ',' . $a . ')' , $rgba_color );

        if ( array_key_exists( 'module_id' , $slider_opts ) ) {
            $module_id = $slider_opts['module_id'];
            $_new_css = sprintf("%s\n%s",
              $_new_css,
              //"#{$module_id} .filter::before { background:{$rgba_color}; }"
              "#{$module_id}.slider-ready .carousel-caption-wrapper { background:{$rgba_color}; }"
            );
        }

        //CTA COLOR = PRIMARY THEME COLOR
        $prim_color = maybe_hash_hex_color( hu_get_option('color-1') );
        if ( $prim_color != '#16cfc1' ) {
            $_new_css = sprintf("%s\n%s",
              $_new_css,
              '#ha-large-header .btn-skin { background-color : ' . $prim_color . '}'
            );
        }

        return $_previous_css . $_new_css;
    }









    /* ------------------------------------------------------------------------- *
     * LOAD PRO HEADER JS ASSETS
    /* ------------------------------------------------------------------------- */
    //hook : 'wp_enqueue_scripts'
    /* Enqueue Plugin resources */
    function ha_enqueue_pro_header_front_assets() {
        wp_enqueue_script(
            'hph-js',
            sprintf('%1$saddons/pro/header/assets/front/hph-front%2$s.js' , HU_AD() -> ha_get_base_url() , ( defined('WP_DEBUG') && true === WP_DEBUG ) ? '' : '.min'),
            array(
              'hph-flickity-js',
              hu_is_checked('defer_front_script') ? 'hu-init-js' : 'hu-front-scripts',
            ),
            ( defined('WP_DEBUG') && true === WP_DEBUG ) ? HUEMAN_VER . time() : HUEMAN_VER,
            true
        );
    }

} //end of class