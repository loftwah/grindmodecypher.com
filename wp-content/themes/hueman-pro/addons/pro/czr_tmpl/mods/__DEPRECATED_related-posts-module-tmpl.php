<?php
//add_action( 'customize_controls_print_footer_scripts', 'hu_print_related_posts_mod_js_templates' , 1 );

function hu_print_related_posts_mod_js_templates() {
  $css_attr = HU_customize::$instance -> css_attr;
  ?>

  <?php //SLIDE ITEM INPUTS  ?>
  <script type="text/html" id="tmpl-czr-module-related-posts-item-input-list">
  <div class="tabs tabs-style-topline">
      <nav>
        <ul>
          <li data-tab-id="section-topline-1"><a href="#"><span><?php _e( 'Design', 'hueman' ); ?></span></a></li>
          <li data-tab-id="section-topline-2"><a href="#"><span><?php _e( 'Related posts', 'hueman' ); ?></span></a></li>
        </ul>
      </nav>

      <div class="content-wrap">

        <section id="section-topline-1">
            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['enable'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display related posts after a single post', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="enable" type="checkbox" {{ _checked }}></input>
              </div>
              <span class="czr-notice"><?php _e('This is a good way to engage your visitors and generate more page views.', 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Max number of columns', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="col_number" type="number" step="1" min="1" max="4" value="{{ data['col_number'] }}" />
              </div>
              <span class="czr-notice"><?php _e("1 to 4 columns. The number of post columns will be adapted depending on the choosen page layout and the user's device.", 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
              <#
                var _checked = ( false != data['display_heading'] ) ? "checked=checked" : '';
              #>
              <div class="customize-control-title"><?php _e('Display a heading for the related posts', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="display_heading" type="checkbox" {{ _checked }}></input>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="text">
              <div class="customize-control-title"><?php _e("Heading's text", 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="heading_text" type="text" value="{{ data['fixed-title'] }}" placeholder="<?php _e('Enter a heading', 'hueman'); ?>"/>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
                <#
                  var _checked = ( false != data['freescroll'] ) ? "checked=checked" : '';
                #>
                <div class="customize-control-title"><?php _e('Free scroll', 'hueman'); ?></div>
                <div class="czr-input">
                  <input data-czrtype="freescroll" type="checkbox" {{ _checked }}></input>
                </div>
                <span class="czr-notice"><?php _e('Enables your posts to be freely scrolled and flicked without being aligned to an end position.', 'hueman'); ?></span>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="check">
                <#
                  var _checked = ( false != data['ajax_enabled'] ) ? "checked=checked" : '';
                #>
                <div class="customize-control-title"><?php _e('Load just in time', 'hueman'); ?></div>
                <div class="czr-input">
                  <input data-czrtype="ajax_enabled" type="checkbox" {{ _checked }}></input>
                </div>
                <span class="czr-notice"><?php _e('When this option is checked, the block of related posts is loaded just before becoming visible on scroll. Enabling this option <strong>makes your single post pages load faster, in particular on mobile devices.</strong><br/> <strong> Note :</strong> this behaviour is not previewable when customizing but will take effect on your published posts.', 'hueman'); ?></span>
            </div>
        </section>

        <section id="section-topline-2">

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="number">
              <div class="customize-control-title"><?php _e('Maximum number of related posts', 'hueman'); ?></div>
              <div class="czr-input">
                <input data-czrtype="post_number" type="number" step="1" min="1" value="{{ data['post_number'] }}" />
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Relationship', 'hueman'); ?></div>
              <div class="czr-input">
                <select data-czrtype="related_by"></select>
              </div>
            </div>

            <div class="<?php echo $css_attr['sub_set_wrapper']; ?>" data-input-type="select">
              <div class="customize-control-title"><?php _e('Choose how to sort your related posts', 'hueman'); ?></div>
              <div class="czr-input">
                <select data-czrtype="order_by"></select>
              </div>
              <span class="czr-notice"><?php _e('The descending order is always used when relevant.', 'hueman'); ?></span>
            </div>
        </section>
      </div><!-- /content -->
    </div><!-- /tabs -->
  </script>
  <?php
}