<?php
add_action( 'customize_controls_print_footer_scripts', 'hu_print_wfc_mod_js_templates' , 1 );

function hu_print_wfc_mod_js_templates() {
  $css_attr = TC_admin_font_customizer::$instance -> wfc_get_controls_css_attr();
  ?>

  <?php //PRE ITEM => PRINTED ON ADD NEW ?>
  <script type="text/html" id="tmpl-czr-module-wfc-pre-item-input-list">
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
      <div class="customize-control-title"><?php _e('Select', 'hueman-pro'); ?></div>
      <div class="czr-input">
        <select data-czrtype="id"></select>
      </div>
      <span class="czr-notice"><?php _e('Pick a predefined text element to customize or define a custom selector.', 'hueman-pro'); ?></span>
    </div>
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
      <div class="customize-control-title"><?php _e('CSS Selector', 'hueman-pro'); ?></div>
      <div class="czr-input">
        <input data-czrtype="selector" type="text" value=""></input>
      </div>
      <span class="czr-notice"><?php _e("Ex : #my-id > .my-class", 'hueman-pro'); ?></span>
    </div>
    <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
      <div class="czr-input">
        <input data-czrtype="title" type="hidden" value="" />
      </div>
    </div>
  </script>


  <?php
  // WFC INPUTS
  // id="tmpl-czr-module-wfc-item-input-list" must match the id declared in the js customizer api
  ?>
  <script type="text/html" id="tmpl-czr-module-wfc-item-input-list">
  <div class="tabs tabs-style-topline">
      <nav>
        <ul>
          <li data-tab-id="section-topline-1"><a href="#"><span><?php _e( 'Font', 'hueman-pro' ); ?></span></a></li>
          <li data-tab-id="section-topline-2"><a href="#"><span><?php _e( 'Style', 'hueman-pro' ); ?></span></a></li>
          <li data-tab-id="section-topline-3"><a href="#"><span><?php _e( 'Selector', 'hueman-pro' ); ?></span></a></li>
        </ul>
      </nav>

      <div class="content-wrap">
        <section id="section-topline-1">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Languages', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="subset"></select>
              </div>
              <span class="czr-notice"><?php _e("You can narrow down the list of Google fonts available for a particular language.", 'hueman-pro'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Font Family', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="font-family"></select>
              </div>
            </div>
            <span class="czr-notice"><?php
                printf(
                    __('Visit the %1$s to find inspiration.', 'hueman-pro' ),
                    sprintf( '<a href="%1$s" target="_blank">%2$s</a>',
                        'https://fonts.google.com/',
                        __('Google Fonts showcase', 'hueman-pro' )
                    )
                );
                ?>
            </span>
        </section>

        <section id="section-topline-2">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Font size', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="font-size" type="number" step="0.5" min="1" max="200" value="{{ data['font-size'] }}" />
              </div>
              <span class="czr-notice"><?php _e(" ( in pixels, converted in flexible em unit for mobile devices )", 'hueman-pro'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Line height', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="line-height" type="number" step="0.5" min="1" max="200" value="{{ data['line-height'] }}" />
              </div>
              <span class="czr-notice"><?php _e(" ( in pixels, converted in flexible em unit for mobile devices )", 'hueman-pro'); ?></span>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Font weight', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="font-weight"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Font style', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="font-style"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Text alignment', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="text-align"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Text decoration', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="text-decoration"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Text transform', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="text-transform"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Letter spacing', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="letter-spacing" type="number" step="1" min="0" max="200" value="{{ data['letter-spacing'] }}" />
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Apply an effect', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <select data-czrtype="static-effect"></select>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?> width-100" data-input-type="color" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Color', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="color" type="text" value="{{ data['color'] }}"></input>
              </div>
            </div>
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?> width-100" data-input-type="color" data-transport="postMessage">
              <div class="customize-control-title"><?php _e('Color on hover', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="color-hover" type="text" value="{{ data['color-hover'] }}"></input>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="nimblecheck">
              <# var _checked = ( false != data['important'] ) ? "checked=checked" : ''; #>
              <div class="customize-control-title"><?php _e('Override any other style', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <div class="nimblecheck-wrap">
                  <input id="nimblecheck-important" data-czrtype="important" type="checkbox" {{ _checked }} class="nimblecheck-input">
                  <label for="nimblecheck-important" class="nimblecheck-label">Switch</label>
                </div>
              </div>
              <span class="czr-notice"><?php _e( 'When checked, all css customized style properties are flagged with "!important."', 'hueman-pro'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
              <div class="czr-input">
                <input data-czrtype="customized" type="hidden" value="" />
              </div>
            </div>
        </section>

        <section id="section-topline-3">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
              <div class="customize-control-title"><?php _e('Css Selector', 'hueman-pro'); ?></div>
              <div class="czr-input">
                <input data-czrtype="selector" type="text" value="{{ data['selector'] }}"></input>
              </div>
              <span class="czr-notice"><?php _e("Ex : #my-id > .my-class", 'hueman-pro'); ?></span>
            </div>
        </section>
      </div><!-- /content -->
    </div><!-- /tabs -->
  </script>
  <?php
}