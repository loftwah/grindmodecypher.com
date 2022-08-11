<?php
function hu_register_header_slider_module( $args ) {
    $defaults = array(
        'setting_id' => '',

        'base_url_path' => '',
        'version' => '',

        'option_value' => array(), //<= will be used for the dynamic registration

        'setting' => array(),
        'control' => array(),
        'section' => array(), //array( 'id' => '', 'label' => '' ),

        'sanitize_callback' => '',
        'validate_callback' => ''
    );
    $args = wp_parse_args( $args, $defaults );

    if ( ! isset( $GLOBALS['czr_base_fmk_namespace'] ) ) {
        error_log( __FUNCTION__ . ' => global czr_base_fmk not set' );
        return;
    }

    $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
    //czr_fn\czr_register_dynamic_module
    $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
    if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
        error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
        return;
    }


    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_setting( array(
        'dynamic_registration' => false,
        'setting_id' => $args['setting_id'],
        'module_type' => 'czr_slide_module',
        'option_value' => ! is_array( $args['option_value'] ) ? array() : $args['option_value'],

        'setting' => $args['setting'],

        'section' => $args['section'],

        'control' => $args['control']
    ));

    // DEFAULT MODEL
    $defaults_model = HU_AD() -> pro_related_posts -> related_post_model;

    // czr_fn\czr_register_dynamic_module()
    $CZR_Fmk_Base_fn() -> czr_pre_register_dynamic_module( array(
        'dynamic_registration' => false,// not automatically. We will defer the dynamic registration on skope set. And refresh on skope update.
        'module_type' => 'czr_slide_module',

        // 'sanitize_callback' => 'hu_sanitize_callback__czr_social_module',
        // 'validate_callback' => 'hu_validate_callback__czr_social_module',

        'customizer_assets' => array(
            'control_js' => array(
                // handle + params for wp_enqueue_script()
                // @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
                'czr_slide_module' => array(
                    'src' => sprintf(
                        '%1$s/assets/js/%2$s',
                        $args['base_url_path'],
                        'czr_slide_module.js'
                    ),
                    'deps' => array('customize-controls' , 'jquery', 'underscore'),
                    'ver' => ( defined('WP_DEBUG') && true === WP_DEBUG ) ? time() : $args['version'],
                    'in_footer' => true
                )
            ),
            'localized_control_js' => array(
                'deps' => 'czr-customizer-fmk',
                'global_var_name' => 'huemanSlideModuleParams',
                'params' => array(
                    'i18n' => array(
                        'Set a custom url' => __('Set a custom url','hueman'),
                        'New Slide created ! Scroll down to edit it.' => __('New Slide created ! Scroll down to edit it.', 'hueman'),
                        'Slide'   => __( 'Slide', 'hueman'),
                        'The caption content is currently fixed and set in' => __( 'The caption content is currently set in', 'hueman' ),
                        'the general options' => __( 'the general options', 'hueman'),
                        'You can display or hide the post metas ( categories, author, date ) in' => __( 'You can display or hide the post metas ( categories, author, date ) in', 'hueman' ),
                        'You can set the global options of the slider here by clicking on the gear icon : height, font size, effects...' =>  __('You can set the global options of the slider here by clicking on the gear icon : height, font size, effects...', 'hueman'),
                        'Those settings will be inherited by the more specific options levels.' =>  __('Those settings will be inherited by the more specific options levels.', 'hueman')
                    ),
                    'defaultSlideMod'     => HU_AD() -> pro_header -> default_slide_model,//<= The model is declared once in init-pro-header
                    'defaultModOpt'       => HU_AD() -> pro_header -> default_slider_option_model,//<= The model is declared once in init-pro-header
                    'defaultThumb'        => sprintf( '%1$saddons/assets/czr/img/slide-placeholder.png', HU_AD() -> ha_get_base_url() ),
                    'sliderSkins'         => array( 'dark' => __('Dark', 'hueman'), 'light' => __('Light', 'hueman') )
                )
            )
        ),

        //'tmpl' => array()//tmpl
    ));
}//hu_register_body_bg_module()


add_action( 'customize_controls_print_footer_scripts', 'hu_print_slider_mod_js_templates' , 1 );

function hu_print_slider_mod_js_templates() {
  $czrnamespace = $GLOBALS['czr_base_fmk_namespace'];
  //czr_fn\czr_register_dynamic_module
  $CZR_Fmk_Base_fn = $czrnamespace . 'CZR_Fmk_Base';
  if ( ! function_exists( $CZR_Fmk_Base_fn) ) {
      error_log( __FUNCTION__ . ' => Namespace problem => ' . $CZR_Fmk_Base_fn );
      return;
  }
  $css_attr = $CZR_Fmk_Base_fn() -> czr_css_attr;

  ?>
  <?php //SLIDER MOD OPTS  ?>
  <script type="text/html" id="tmpl-czr-module-slide-mod-opt-input-list">
    <div class="modopts-top-buttons">
      <button class="refresh-button modopt-top-btn" title="<?php _e( 'Refresh', 'hueman' ); ?>"><?php _e( 'Refresh the preview', 'hueman' ); ?></button>
    </div>
    <div class="tabs tabs-style-topline">
      <nav>
        <ul>
          <li data-tab-id="section-topline-1"><a title="<?php _e( 'General Design', 'hueman' ); ?>" href="#"><span><?php _e( 'General Design', 'hueman' ); ?></span></a></li>
          <li data-tab-id="section-topline-2"><a title="<?php _e( 'Slider Content', 'hueman' ); ?>" href="#"><span><?php _e( 'Slider Content', 'hueman' ); ?></span></a></li>
          <li data-tab-id="section-topline-3"><a title="<?php _e( 'Effects and performances', 'hueman' ); ?>" href="#"><span><?php _e( 'Effects and performances', 'hueman' ); ?></span></a></li>
        </ul>
      </nav>
      <div class="content-wrap">
        <section id="section-topline-1">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="range_slider" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Slider Height', 'hueman'); ?></div>
              <div class="czr-input">
                  <input data-czrtype="slider-height" type="range" min="20" max="100" value="{{ data['slider-height'] }}" data-unit="%" data-orientation="vertical" />
              </div>
              <span class="czr-notice"><?php _e('Set the height of the slider. In percentage of the viewport : 100% = the full height of the page', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Minimum height of slider (in pixels) on mobile devices', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slider-min-height" type="number" step="1" min="0" value="{{ data['slider-min-height'] }}" />
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Overlay style : dark or light', 'hueman'); ?></div>
              <div class="czr-input">
                <select data-czrtype="skin"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="range_slider" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Skin opacity', 'hueman'); ?></div>
              <div class="czr-input">
                  <input data-czrtype="skin-opacity" type="range" min="0" max="100" value="{{ data['skin-opacity'] }}" data-unit="%" data-orientation="horizontal" />
              </div>
              <span class="czr-notice"><?php _e('Sets the opacity of the skin filter.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?> width-100" data-input-type="wp_color_alpha" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Default background color', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="default-bg-color" type="text" value="{{ data['default-bg-color'] }}"></input>
              </div>
              <span class="czr-notice"><?php _e('This color will be applied when no image has been set to a slide.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="upload">
              <div class="customize-control-title"><?php _e('Default background image', 'hueman'); ?></div>
                <div class="<?php echo $css_attr['sub_set_input']; ?>">
                  <input data-czrtype="default-bg-img" type="hidden" value="{{ data['default-bg-img'] }}"/>
                <div class="<?php echo $css_attr['img_upload_container']; ?>"></div>
              </div>
              <span class="czr-notice"><?php _e('This image will be printed when no image has been set to a slide.', 'hueman'); ?></span>
            </div>
        </section>


        <section id="section-topline-2">
             <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="range_slider" data-transport="postMessage">
              <div class="customize-control-title"><?php _e("Slider's caption position", 'hueman'); ?></div>
              <div class="czr-input">
                  <input data-czrtype="caption-vertical-pos" type="range" min="-50" max="50" value="{{ data['caption-vertical-pos'] }}" data-unit="%" data-orientation="vertical" />
              </div>
              <span class="czr-notice"><?php _e('Set the vertical position of the caption ( 0% = middle ).', 'hueman'); ?></span>
            </div>
             <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['fixed-content'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Set the same fixed title, caption text and button for all slides', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-content" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('By default each slide has a specific title, caption text and button.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Title', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-title" type="text" value="{{ data['fixed-title'] }}" placeholder="<?php _e('Enter a title', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Subtitle', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-subtitle" type="textarea" value="{{ data['fixed-subtitle'] }}" placeholder="<?php _e('Enter a subtitle', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Call to action', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-cta" type="textarea" value="{{ data['fixed-cta'] }}" placeholder="<?php _e('Enter a call to action', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="content_picker">
              <div class="customize-control-title"><?php _e('Select a content to link', 'hueman'); ?></div>
              <div class="czr-input">
                <span data-czrtype="fixed-link"></span>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Custom link url', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-custom-link" type="textarea" value="{{ data['fixed-custom-link'] }}" placeholder="<?php _e('http//...', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check" data-transport="postMessage">
              <#
                var _checked = ( false != data['fixed-link-target'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Open the link in a new browser tab', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="fixed-link-target" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['use-hone-title-tag-globally'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Use a H1 tag (default is H2) for slide titles.', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="use-hone-title-tag-globally" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('On single posts or pages, a default slide will be displayed, based on the post or page thumbnail, title, and metas informations ( author, date, categories).', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Max length of the titles', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="title-max-length" type="number" step="1" min="0" value="{{ data['title-max-length'] }}" />
              </div>
              <span class="czr-notice"><?php _e('In number of characters.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Max length of the subtitles', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="subtitle-max-length" type="number" step="1" min="0" value="{{ data['subtitle-max-length'] }}" />
              </div>
              <span class="czr-notice"><?php _e('In number of characters.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="range_slider">
              <div class="customize-control-title"><?php _e("Titles font size", 'hueman'); ?></div>
              <div class="czr-input">
                  <input data-czrtype="font-ratio" type="range" min="-50" max="50" value="{{ data['font-ratio'] }}" data-unit="%" data-orientation="horizontal" />
              </div>
              <span class="czr-notice"><?php _e('Set the font size of the title and subtitle ( 0% = default ).', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['use-contextual-data'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Use the post thumbnail, title and metas informations as default when available.', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="use-contextual-data" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('On single posts or pages, a default slide will be displayed, based on the post or page thumbnail, title, and metas informations ( author, date, categories).', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['post-metas'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display contextual metas informations when available', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="post-metas" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('On single post pages, this will display the post categories, comment icon, author and publish date on the default image background.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['display-cats'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display categories', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="display-cats" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['display-comments'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display comments bubble', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="display-comments" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['display-auth-date'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display author and date', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="display-auth-date" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
        </section>

        <section id="section-topline-3">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['autoplay'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Autoplay the slider', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="autoplay" type="checkbox" {{ _checked }}></input>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Time interval in seconds', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slider-speed" type="number" step="0.5" min="0.5" value="{{ data['slider-speed'] }}" />
              </div>
              <span class="czr-notice"><?php _e('Autoplay : set the time interval between each slides ( in seconds ).', 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['pause-on-hover'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Pause auto-play on mouse hover', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="pause-on-hover" type="checkbox" {{ _checked }}></input>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['parallax'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Parallax scrolling', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="parallax" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('Parallax scrolling is a technique used in web design, where background move slower than foreground content.', 'hueman'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="range_slider">
              <div class="customize-control-title"><?php _e('Parallax slow-down', 'hueman'); ?></div>
              <div class="czr-input">
                  <input data-czrtype="parallax-speed" type="range" min="0" max="100" value="{{ data['parallax-speed'] }}" data-unit="%" data-orientation="horizontal" />
              </div>
              <span class="czr-notice"><?php _e('Set this value and try scrolling your page down and up to setup your background parallax scrolling speed.', 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
                <#
                  var _checked = ( false != data['freescroll'] ) ? "checked=checked" : '';
                #>
                <div class="customize-control-title"><?php _e('Free Scroll', 'hueman'); ?></div>
                <div class="czr-input">
                  <input data-czrtype="freescroll" type="checkbox" {{ _checked }}></input>
                </div>
                <span class="czr-notice"><?php _e('Enables your slides to be freely scrolled and flicked without being aligned to an end position.', 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
                <#
                  var _checked = ( false != data['lazyload'] ) ? "checked=checked" : '';
                #>
                <div class="customize-control-title"><?php _e('Lazy load backgrounds', 'hueman'); ?></div>
                <div class="czr-input">
                  <input data-czrtype="lazyload" type="checkbox" {{ _checked }}></input>
                </div>
                <span class="czr-notice"><?php _e('Improve your page load performances by deferring the loading of not visible images.', 'hueman'); ?></span>
            </div>
        </section>

      </div><!-- /content -->
    </div><!-- /tabs -->
  </script>


  <?php //PRE ITEM => PRINTED ON ADD NEW ?>
  <script type="text/html" id="tmpl-czr-module-slide-pre-item-input-list">
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="upload">
      <div class="customize-control-title"><?php _e('Slide Background', 'hueman'); ?></div>
        <div class="<?php echo $css_attr['sub_set_input']; ?>">
          <input data-czrtype="slide-background" type="hidden"/>
        <div class="<?php echo $css_attr['img_upload_container']; ?>"></div>
      </div>
    </div>
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
      <div class="customize-control-title"><?php _e('Slide Title', 'hueman'); ?></div>
      <div class="czr-input">
        <input data-czrtype="slide-title" type="text" value="" placeholder="<?php _e('Enter a title', 'hueman'); ?>"/>
      </div>
    </div>
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="textarea">
      <div class="customize-control-title"><?php _e('Slide subtitle', 'hueman'); ?></div>
      <div class="czr-input">
        <input data-czrtype="slide-subtitle" type="textarea" value="" placeholder="<?php _e('Enter a subtitle', 'hueman'); ?>"/>
      </div>
    </div>
  </script>


  <?php //SLIDE ITEM INPUTS  ?>
  <script type="text/html" id="tmpl-czr-module-slide-item-input-list">
  <div class="tabs tabs-style-topline">

      <nav>
        <ul>
          <li data-tab-id="section-topline-1"><a href="#"><span><?php _e( 'Slide Background', 'hueman' ); ?></span></a></li>
          <li data-tab-id="section-topline-2"><a href="#"><span><?php _e( 'Slide Caption', 'hueman' ); ?></span></a></li>
        </ul>
      </nav>

      <div class="content-wrap">

        <section id="section-topline-1">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="upload">
              <div class="customize-control-title"><?php _e('Slide Background', 'hueman'); ?></div>
                <div class="<?php echo $css_attr['sub_set_input']; ?>">
                  <input data-czrtype="slide-background" type="hidden" value="{{ data['slide-background'] }}"/>
                <div class="<?php echo $css_attr['img_upload_container']; ?>"></div>
              </div>
            </div>
        </section>

        <section id="section-topline-2">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Slide Title', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-title" type="text" value="{{ data['slide-title'] }}" placeholder="<?php _e('Enter a title', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['slide-link-title'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Turn the title into a link', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-link-title" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['slide-heading-tag'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Use a H1 tag (default is H2) for this slide title', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-heading-tag" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Slide subtitle', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-subtitle" type="textarea" value="{{ data['slide-subtitle'] }}" placeholder="<?php _e('Enter a subtitle', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Call to action', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-cta" type="textarea" value="{{ data['slide-cta'] }}" placeholder="<?php _e('Enter a call to action', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="content_picker" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Select a content to link', 'hueman'); ?></div>
              <div class="czr-input">
                <span data-czrtype="slide-link"></span>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Custom link url', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-custom-link" type="textarea" value="{{ data['slide-custom-link'] }}" placeholder="<?php _e('http//...', 'hueman'); ?>"/>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check" data-transport="postMessage">
              <#
                var _checked = ( false != data['slide-link-target'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Open the link in a new browser tab', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="slide-link-target" type="checkbox" {{ _checked }}></input>
              </div>
            </div>
        </section>
        <div class="item-bottom-buttons">
          <button class="refresh-button item-bottom-btn" title="<?php _e( 'Refresh', 'hueman' ); ?>"><?php _e( 'Refresh the preview', 'hueman' ); ?></button>
          <button class="focus-button item-bottom-btn" title="<?php _e( 'Focus on slide', 'hueman' ); ?>"><?php _e( 'Focus on slide', 'hueman' ); ?></button>
        </div>
      </div><!-- /content -->
    </div><!-- /tabs -->
  </script>
  <?php
}