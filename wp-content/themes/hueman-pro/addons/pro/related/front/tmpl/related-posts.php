<?php
  global $post;
  $post_id = $post->ID;

  $db_opts = hu_get_option( HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name );
  //IS AJAX ENABLED ? Yes by default.
  $is_ajax_rel_post_enabled = true;
  if ( is_array( $db_opts ) && array_key_exists( 'ajax_enabled', $db_opts ) ) {
      $is_ajax_rel_post_enabled = true == esc_attr( hu_booleanize_checkbox_val( $db_opts['ajax_enabled'] ) );
  }
  $db_opts = ! is_array( $db_opts ) ? array() : $db_opts;
?>
<?php if ( ! $is_ajax_rel_post_enabled || hu_is_customizing() || ha_is_partial_ajax_request() ) : ?>
    <div id="pro-related-posts-wrapper">
        <?php load_template( HA_BASE_PATH . 'addons/pro/related/front/tmpl/related-posts-content.php' ); ?>
    </div>
<?php else : ?>
    <script type="text/javascript">
        jQuery( function($) {
            var _fireWhenCzrAppReady = function() {
              czrapp.proRelPostsRendered = $.Deferred();
              var waypoint = new Waypoint({
                  element: document.getElementById('pro-related-posts-wrapper'),
                  handler: function(direction) {
                        if ( 'pending' == czrapp.proRelPostsRendered.state() ) {
                              var $wrap = $('#pro-related-posts-wrapper');
                              $wrap.addClass('loading');
                              czrapp.doAjax( {
                                      action: "ha_inject_pro_related",
                                      // => Always get the option from the $_POSTED data in ajax
                                      related_post_id : <?php echo empty( $post_id ) ? '' : $post_id; ?>,
                                      pro_related_posts_opt : <?php echo wp_json_encode( $db_opts ); ?>,
                                      free_related_posts_opt : "<?php echo hu_get_option( 'related-posts' ); ?>",
                                      layout_class : "<?php echo hu_get_layout_class(); ?>"
                                  } ).done( function( r ) {
                                        if ( r && r.data && r.data.html ) {
                                            if ( 'pending' == czrapp.proRelPostsRendered.state() ) {
                                                $.when( $('#pro-related-posts-wrapper').append( r.data.html ) ).done( function() {
                                                      czrapp.proRelPostsRendered.resolve();
                                                      $wrap.find('.czr-css-loader').css('opacity', 0);
                                                      _.delay( function() {
                                                            $wrap.removeClass('loading').addClass('loaded');
                                                      }, 800 );
                                                });
                                            }
                                        }
                                  });
                        }
                  },
                  offset: '110%'
              });
          };//_fireWhenCzrAppReady

          if ( window.czrapp && czrapp.methods && czrapp.methods.ProHeaderSlid ) {
            _fireWhenCzrAppReady()
          } else {
            document.addEventListener('czrapp-is-ready', _fireWhenCzrAppReady );
          }
        });//jQuery()
    </script>
    <div id="pro-related-posts-wrapper"><div class="czr-css-loader czr-mr-loader dark"><div></div><div></div><div></div></div></div>
<?php endif; ?>