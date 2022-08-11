<?php
// $default_options = array(
//     'is_meta'           => true,
//     'module_id'         => '',
//     'slider-speed'      => '',
//     'skin'     => '',
//     'lazy-load'         => true
// );
//
// $default_slide = array(
//     'id'                => '',
//     'title'             => '',
//     'slide-background'  => '',
//     'slide-src'         => '',
//     'slide-title'       => '',
//     'slide-subtitle'    => ''
// );



//METAS OPTIONS TO ADD
// use fixed title / subtitle / calltoaction : bool
// if use fixed titles => use current context title : bool
// if use fixed titles and ! use current context title :
//    set custom title
//    set custom subtitle
//    set custom calltoaction
//    set custom link
//  else if use_current context title
//    show meta
//    show
// show carousel dots
// load img / video on slide
// resize fonts in %

?>

<?php
/////////////////// TEST //////////////
// $default_slide_model = HU_AD() -> pro_header -> default_slide_model;
// $default_slider_options_model = HU_AD() -> pro_header -> default_slider_option_model;
// $pro_header_slider_short_opt_name = HU_AD() -> pro_header -> pro_header_slider_short_opt_name;//'pro_slider_header_bg'
// $db_opt = hu_get_option( $pro_header_slider_short_opt_name );
// ha_error_log( "////DB OPTION" );
// ha_error_log( print_r( $db_opt, true ) );
// ha_error_log( "////END OF DB OPTION" );


function ph_has_bg_class() {
  $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
  if ( ! is_array( $model ) || ! array_key_exists( 'slides', $model ) )
    return 'no-bg';

  return 0 == count( $model['slides'] ) ? 'no-bg' : '';
}

//@return bool
function _has_slides( $model ) {
    if ( ! is_array( $model ) || ! array_key_exists( 'slides', $model ) )
      return false;

    return count( $model['slides'] ) > 0;
}

//@return bool
function _has_single_slide_bg( $slide_src, $slide_model ) {
    return '_not_set_' != $slide_src && ! empty( $slide_src ) && array_key_exists( 'slide-src' , $slide_model );
}

//hook : the_category_list @see wp-includes/category-template.php
function _limit_number_of_cats_in_caption( $cats ) {
  if ( ! is_array( $cats ) )
    return $cats;
  return array_slice( $cats, 0, 4 );
}







// SLIDER CAPTION CONTENT
// => uses the contextual informations no slide model has been provided
//slide_data = array(
//  'title' => ''
//  'subtitle' => ''
//  'cta' =>
//  'link' =>
//)
function _print_slider_caption( $slider_opts, $slide_data = array() ) {
    //Normalizes
    $slide_data = ! is_array( $slide_data ) ? array() : $slide_data;
    $defaults  = array(
        'title'     => null,
        'is_title_linked' => false,
        'is_h_one_heading_tag_for_this_slide' => false,//added for https://github.com/presscustomizr/hueman-pro-addons/issues/195
        'subtitle'  => null,
        'cta'       => null,
        'link'      => null,
        'target'    => null,
        'custom-link' => null,
        'is_default' => false,
    );
    $slide_data = wp_parse_args( $slide_data, $defaults );
    $slider_opts = ! is_array( $slider_opts ) ? array() : $slider_opts;

    $default_opts  = array(
        'use-hone-title-tag-globally' => false,//added january 2020 for https://github.com/presscustomizr/hueman-pro-addons/issues/199
        'title-max-length'     => 70,//set in init-pro-header
        'subtitle-max-length'  => 100,//set in init-pro-header
    );

    $slider_opts = wp_parse_args( $slider_opts, $default_opts );

    //Setup the element to print
    //The display property allow us to target the elements when customizing, even if initially empty.
    $title     = html_entity_decode( esc_attr( $slide_data['title'] ) );
    $is_title_linked = hu_booleanize_checkbox_val( $slide_data['is_title_linked'] );

    // Slide title tag : h1 or h2 ?
    // By default h2
    // can be set by slide or globally
    // note : if set globally true ( h1 ), there's no way to unset it on a per-slide basis
    $is_h_one_heading_tag_globally = hu_booleanize_checkbox_val( $slider_opts['use-hone-title-tag-globally'] );
    $is_h_one_heading_tag_for_current_slide = hu_booleanize_checkbox_val( $slide_data['is_h_one_heading_tag_for_this_slide'] );//added for https://github.com/presscustomizr/hueman-pro-addons/issues/195
    $is_h_one_heading_tag_for_this_slide = false;
    if ( $is_h_one_heading_tag_for_current_slide ) {
        $is_h_one_heading_tag_for_this_slide = true;
    } else if ( $is_h_one_heading_tag_globally ) {
        $is_h_one_heading_tag_for_this_slide = true;
    }


    $subtitle  = html_entity_decode( esc_attr( $slide_data['subtitle'] ) );
    $cta       = esc_attr( $slide_data['cta'] );
    $target    = esc_attr( $slide_data['target'] );
    $is_default = hu_booleanize_checkbox_val( $slide_data['is_default'] );
    $is_meta_on = hu_booleanize_checkbox_val( $slider_opts['post-metas'] );
    $is_fixed_caption = hu_booleanize_checkbox_val( $slider_opts['fixed-content'] );

    //metas are displayed if is_meta_on and
    //1) content is not fixed and is default slide
    //2) content is fixed
    if ( ! $is_fixed_caption ) {
      $is_meta_on = $is_meta_on && $is_default;
    }

    $is_cat_on = $is_meta_on && hu_booleanize_checkbox_val( $slider_opts['display-cats'] );
    $is_comment_on = $is_meta_on && hu_booleanize_checkbox_val( $slider_opts['display-comments'] );
    $is_auth_date_on = $is_meta_on && hu_booleanize_checkbox_val( $slider_opts['display-auth-date'] );

    //LINK is structured this way :
    // Array
    // (
    //     [id] => 1 <= if custom link, this is _custom_
    //     [type_label] => Post
    //     [title] => Hello world!
    //     [object_type] => post
    //     [url] => http://customizr-dev.dev/?p=1
    // )

    $link      = is_array( $slide_data['link'] ) ? $slide_data['link'] : null;
    if ( is_array( $link ) && array_key_exists( 'id' , $link ) ) {
        if ( '_custom_' == $link['id'] ) {
            $link = esc_url( $slide_data['custom-link'] );
        } else if ( array_key_exists( 'url' , $link ) ) {
            $link = esc_url( $link['url'] );
        }
    }
    $link = is_string( $link ) ? $link : null;

    //setup filters before print
    $title     = is_null( $title ) ? apply_filters( 'hph_title', get_bloginfo('name') ) : $title;
    $subtitle  = is_null( $subtitle ) ? apply_filters( 'hph_subtitle', get_bloginfo('description') ) : $subtitle;
    $cta       = is_null( $cta ) ? apply_filters( 'hph_cta_text', '') : $cta;
    $link      = is_null( $link ) ? apply_filters( 'hph_cta_link', 'javascript:void(0)') : $link;
    $target    = is_null( $target ) ? apply_filters( 'hph_cta_target', false ) : $target;

    //filter with max number of word option
    //limit text to 200 car
    $max_title_length         = $slider_opts['title-max-length'] > 4 ? $slider_opts['title-max-length'] : 4;
    $max_subtitle_length      = $slider_opts['subtitle-max-length'] > 4 ? $slider_opts['subtitle-max-length'] : 4;



    $title = strlen( $title ) > $max_title_length ? substr( $title , 0 , $max_title_length - 4 ) . ' ...' : $title;
    $subtitle = strlen( $subtitle ) > $max_subtitle_length ? substr( $subtitle , 0 , $max_subtitle_length - 4 ) . ' ...' : $subtitle;

    //ha_error_log( $title  );
    ?>
      <div class="carousel-caption-wrapper">
          <div class="carousel-caption">
              <?php if ( is_single() && ( $is_cat_on || $is_comment_on ) ) : ?>
                  <ul class="meta-single group">
                      <?php if ( $is_cat_on ) : ?>
                          <?php add_filter('the_category_list', '_limit_number_of_cats_in_caption', 100 ); ?>
                          <li class="category"><?php the_category(' <span>/</span> '); ?></li>
                          <?php remove_filter('the_category_list', '_limit_number_of_cats_in_caption', 100 ); ?>
                      <?php endif; ?>
                      <?php if ( $is_comment_on && comments_open() && ( hu_is_checked( 'comment-count' ) ) ): ?>
                          <li class="comments"><a href="<?php comments_link(); ?>"><i class="far fa-comments"></i><?php comments_number( '0', '1', '%' ); ?></a></li>
                      <?php endif; ?>
                  </ul>
              <?php endif; ?>
              <?php
                  printf( '<%3$s class="hph-title display-1 thick very-big" style="display:%1$s">%2$s</%3$s>',
                      empty( $title ) ? 'none' : 'block',
                      $is_title_linked ? sprintf('<a href="%1$s" target="%2$s" title="%3$s">%3$s</a>',
                          $link,
                          hu_booleanize_checkbox_val( $target ) ? 'target="_blank"' : '',
                          $title
                          ) : $title,
                      $is_h_one_heading_tag_for_this_slide ? 'h1' : 'h2'//added for https://github.com/presscustomizr/hueman-pro-addons/issues/195
                  );
                  printf( '<h3 class="hph-subtitle semi-bold" style="display:%1$s">%2$s</h3>',
                      empty( $subtitle ) ? 'none' : 'block',
                      $subtitle
                  );
                  printf( '<a href="%1$s" %2$s class="hph-cta btn btn-fill btn-skin btn-large" style="display:%3$s" title="%4$s">%4$s</a>',
                      $link,
                      hu_booleanize_checkbox_val( $target ) ? 'target="_blank"' : '',
                      empty( $cta ) ? 'none' : 'inline-block',
                      $cta
                  );
              ?>
              <?php if ( is_single() && $is_auth_date_on ): ?>
                <div class="hph-single-author-date"><?php get_template_part( 'parts/single-author-date' ); ?></div>
              <?php endif; ?>
          </div>
      </div>
    <?php
}








// Print the php arguments passed to js (hph-front.js)
//
function _print_script( $args ) {
    $defaults = array(
        'module_id'          => '',
        'is_single_slide'    => false,
        'is_lazy_load'       => true,
        'is_free_scroll'     => false,
        'is_parallax_on'     => true,
        'is_fixed_caption'   => false,
        'slider_speed'       => '',
        'parallax_speed'     => '',
        'is_autoplay_on'     => false,
        'is_pause_hover_on'  => true,
        'caption_font_ratio' => '',
        'is_doing_partial_refresh' => false
    );
    $args = wp_parse_args( $args, $defaults );
    ?>
      <script type="text/javascript" id="<?php echo $args['module_id']; ?>_script">
          var _fireWhenCzrAppReady = function() {
              jQuery( function($) {
                    var args = {};
                    args['module_id']       = "<?php echo $args['module_id']; ?>";
                    args['isSingleSlide']   = <?php echo true == $args['is_single_slide'] ? 'true' : 'false'; ?>;
                    args['isAutoplay']      = <?php echo $args['is_autoplay_on'] ? 'true' : 'false'; ?>;

                    args['pauseAutoPlayOnHover'] = <?php echo $args['is_pause_hover_on'] ? 'true' : 'false'; ?>;
                    args['isLazyLoad']      = <?php echo $args['is_lazy_load'] ? 'true' : 'false'; ?>;
                    args['isFreeScroll']    = <?php echo $args['is_free_scroll'] ? 'true' : 'false'; ?>;
                    args['isParallaxOn']    = <?php echo $args['is_parallax_on'] ? 'true' : 'false'; ?>;
                    args['parallaxRatio']    = _.isNumber( parseInt( <?php echo $args['parallax_speed']; ?>, 10 ) ) ? Math.round( parseInt( <?php echo $args['parallax_speed']; ?>, 10 ) * 100.0 / 100) / 100 : 0.55;

                    //Time interval is saved in seconds and has to be converted into ms
                    args['timeInterval']    = _.isNumber( <?php echo $args['slider_speed']; ?> ) ? <?php echo $args['slider_speed']; ?> * 1000 : 5000;//<= in ms

                    args['isFixedCaption']  = <?php echo $args['is_fixed_caption'] ? 'true' : 'false'; ?>;

                    args['captionFontRatio']  = _.isNumber( parseInt( <?php echo $args['caption_font_ratio']; ?>, 10 ) ) ? parseInt( <?php echo $args['caption_font_ratio']; ?>, 10 ) : 0;

                    args['isDoingPartialRefresh'] = <?php echo $args['is_doing_partial_refresh'] ? 'true' : 'false'; ?>;
                    //instantiate on first run, then on the following runs, call fire statically
                    var _do = function() {
                          if ( czrapp.proHeaderSlid ) {
                                czrapp.proHeaderSlid.fire( args );
                          } else {
                                var _map = $.extend( true, {}, czrapp.customMap() );
                                _map = $.extend( _map, {
                                      proHeaderSlid : {
                                            ctor : czrapp.Base.extend( czrapp.methods.ProHeaderSlid ),
                                            ready : [ 'fire' ],
                                            options : args
                                      }
                                });
                                //this is listened to in xfire.js
                                czrapp.customMap( _map );
                          }
                    };
                    if ( ! _.isUndefined( czrapp ) && czrapp.ready ) {
                          if ( 'resolved' == czrapp.ready.state() ) {
                                _do();
                          } else {
                                czrapp.ready.done( _do );
                          }
                    }
              });
          };//document.addEventListener('czrapp-is-ready'
          if ( window.czrapp && czrapp.methods && czrapp.methods.ProHeaderSlid ) {
                _fireWhenCzrAppReady()
          } else {
                document.addEventListener('hu-hph-front-loaded', _fireWhenCzrAppReady );
          }
      </script>
    <?php
}







?>


  <?php
    /* <div id="ha-large-header" class="container-fluid section <?php echo ph_has_bg_class(); ?>"> */

    //SETUP MODEL AND VARS
    $model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
    // ha_error_log( 'MODEL///////////////' );
    // ha_error_log( print_R( $model, true ) );

    $slider_opts  = $model['options'];
    if ( ! is_array( $slider_opts ) || empty( $slider_opts ) ) {
      ha_error_log( 'In slider-tmpl.php : invalid model options' );
      return;
    }

    $module_id = ( array_key_exists( 'module_id', $slider_opts ) && ! empty( $slider_opts['module_id'] ) ) ? $slider_opts['module_id'] : 'pro_large_header';

    $is_full_height = 100 == $slider_opts['slider-height'];
    $full_height_class = $is_full_height ? 'full-height' : '';
    $is_fixed_caption = hu_booleanize_checkbox_val( $slider_opts['fixed-content'] );
    $is_single_slide = 1 >= count( $model['slides'] );
    $is_parallax_on = hu_booleanize_checkbox_val( $slider_opts['parallax'] );
    $is_lazy_load = hu_booleanize_checkbox_val( $slider_opts['lazyload'] );
    $is_free_scroll = hu_booleanize_checkbox_val( $slider_opts['freescroll'] );

    //DEFAULT BACKGROUND
    //sets specific attributes if lazy load is enabled
    if ( $is_lazy_load ) {
        add_filter( 'wp_get_attachment_image_attributes', array( PC_HAP_front::$instance, 'hu_set_lazy_load_attributes'), 999 );
    }
    //default bg_img
    $default_bg_img = wp_get_attachment_image( $slider_opts['default-bg-img'] , 'full');

    if ( $is_lazy_load ) {
        remove_filter( 'wp_get_attachment_image_attributes', array( PC_HAP_front::$instance, 'hu_set_lazy_load_attributes'), 999 );
    }


    $has_default_bg_img = false != $default_bg_img && ! empty( $default_bg_img );


    // ha_error_log( '$is_fixed_caption///////////////' . $slider_opts['fixed-content']  );
    // ha_error_log( $is_fixed_caption );


    //Build wrapper class
    $slider_wrapper_classes = implode(
        ' ',
        array(
            'pc-section-slider',
            $full_height_class,
            $is_parallax_on ? 'parallax-wrapper' : '',
            $is_lazy_load ? 'lazy-load-on' : '',
            ! apply_filters( 'pro-header-img-centered', true ) ? 'img-not-js-centered' : ''//<= there's a css rule attached to .img-not-js-centered .carousel-image img
        )
    );

    $carousel_inner_classes = implode(
        ' ',
        array(
            'carousel-inner',
            'center-slides-enabled',
            $is_fixed_caption  ? 'fixed-caption-on' : ''
            //'parallax-slider'//<=test for new jquery parallax plugin
        )
    );

    $slide_caption_data = array();
    if ( $is_fixed_caption ) {
        $slide_caption_data = array(
            'title'    =>  $slider_opts['fixed-title'],
            'is_title_linked' => false,
            'is_h_one_heading_tag_for_this_slide' => false,//added for https://github.com/presscustomizr/hueman-pro-addons/issues/195
            'subtitle' =>  $slider_opts['fixed-subtitle'],
            'cta'      =>  $slider_opts['fixed-cta'],
            'link'     =>  $slider_opts['fixed-link'],
            'target'   =>  $slider_opts['fixed-link-target'],
            'custom-link' => $slider_opts['fixed-custom-link']
        );
    }

  ?>
    <?php
      //If no slide is set, then we apply the default bg img if set and valid
    ?>

    <div id="<?php echo $module_id; ?>" class="<?php echo $slider_wrapper_classes; ?>">
        <div class="czr-css-loader czr-mr-loader"><div></div><div></div><div></div></div>
        <div class="<?php echo $carousel_inner_classes; ?>" data-parallax-ratio="0.55">
          <?php if ( $is_fixed_caption ) { _print_slider_caption( $slider_opts, $slide_caption_data ); } ?>

          <?php if ( empty( $model['slides'] ) && $has_default_bg_img ) : ?>

              <div id="default-bg" class="carousel-cell item">
                      <!-- <div class="filter"></div> -->
                      <div class="carousel-image">
                          <?php echo $default_bg_img; ?>
                      </div> <!-- .carousel-image -->
              </div><!-- /.item -->

          <?php endif; ?>
          <?php foreach ( $model['slides'] as $slide ) : ?>
              <?php
                  $item_classes = array(
                      ( _has_single_slide_bg( $slide['slide-src'], $slide ) || $has_default_bg_img ) ? '' : 'no-bg-img',
                      'carousel-cell',
                      'item',
                      'slide-attachment-id-' . $slide['slide-background'],

                  );
                  $slide_caption_data = array(
                      'title'    =>  $slide['slide-title'],
                      'is_title_linked' => $slide['slide-link-title'],
                      'is_h_one_heading_tag_for_this_slide' => array_key_exists('slide-heading-tag', $slide) ? $slide['slide-heading-tag'] : false,//added for https://github.com/presscustomizr/hueman-pro-addons/issues/195
                      'subtitle' =>  $slide['slide-subtitle'],
                      'cta'      =>  $slide['slide-cta'],
                      'link'     =>  $slide['slide-link'],
                      'target'   =>  $slide['slide-link-target'],
                      'custom-link' => $slide['slide-custom-link'],
                      'is_default' => $slide['is_default']
                  );
                  // ha_error_log( '//IN SLIDER TMPL');
                  // ha_error_log( $slide['slide-src'] );
              ?>
              <div id="<?php echo $slide['id']; ?>" class="<?php echo implode(' ', $item_classes ); ?>">
                      <!-- <div class="filter"></div> -->
                      <div class="carousel-image">
                          <?php
                              if ( ! _has_single_slide_bg( $slide['slide-src'], $slide ) && $has_default_bg_img ) {
                                  echo $default_bg_img;
                              } else {
                                  echo HU_AD() -> pro_header -> front_class -> hu_set_slide_background( $slide['slide-src'], $slide );
                              }
                            ?>
                      </div> <!-- .carousel-image -->
                  <?php if ( ! $is_fixed_caption ) { _print_slider_caption( $slider_opts, $slide_caption_data ); } ?>
              </div><!-- /.item -->
          <?php endforeach; ?>
        </div>

        <?php if ( ! $is_single_slide ) : ?>
          <div class="slider-nav">
              <span class="slider-control slider-prev control-left icn-left-open-big" title="<?php _e( 'previous', 'hueman' ); ?>"><i class="fas fa-chevron-left"></i></span>
              <span class="slider-control slider-next control-right icn-right-open-big" title="<?php _e( 'next', 'hueman' ); ?>"><i class="fas fa-chevron-right"></i></span>
          </div>
        <?php endif; ?>

        <?php //load_template( HA_BASE_PATH . 'addons/pro/header/front/tmpl/slider-js-tmpl.php', true );//true for require_once ?>

        <?php
          _print_script(
              array(
                  'module_id'          => $module_id,
                  'is_single_slide'    => $is_single_slide,
                  'is_lazy_load'       => $is_lazy_load,
                  'is_free_scroll'     => $is_free_scroll,
                  'is_parallax_on'     => $is_parallax_on,
                  'is_fixed_caption'   => $is_fixed_caption,
                  'slider_speed'       => ( array_key_exists( 'slider-speed', $slider_opts ) && is_numeric( $slider_opts['slider-speed'] ) ) ? $slider_opts['slider-speed'] : 4,
                  'parallax_speed'     => ( array_key_exists( 'parallax-speed', $slider_opts ) && is_numeric( $slider_opts['parallax-speed'] ) ) ? $slider_opts['parallax-speed'] : 55,
                  'is_autoplay_on'     => hu_booleanize_checkbox_val( $slider_opts['autoplay'] ),
                  'is_pause_hover_on'  => hu_booleanize_checkbox_val( $slider_opts['pause-on-hover'] ),
                  'caption_font_ratio' => is_numeric( intval( $slider_opts['font-ratio'] ) ) ? intval( $slider_opts['font-ratio'] ) : 0,
                  'is_doing_partial_refresh' => ha_is_partial_ajax_request()
              )
          );//_print_script
        ?>

    </div> <!-- pc-section-slider -->
<!-- </div> id="ha-large-header" -->