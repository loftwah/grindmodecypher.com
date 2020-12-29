<?php
/**
* FRONT END CLASS
* @author Nicolas GUILLAUME
* @since 1.0
*/
class PC_HAPMAS_front {

      //Access any method or var of the class with classname::$instance -> var or method():
      static $instance;
      public $current_effect;
      public $model;

      function __construct () {
            self::$instance     =& $this;
            //TEST ONLY!!! : ADD INLINE JS AND CSS
            add_action( 'wp_head'                    , array( $this, 'hu_add_inline_css' ), 9999 );
            add_action( 'wp_footer'                  , array( $this, 'hu_add_inline_js' ), 9999 );
            //END TEST ONLY

            add_action( 'wp_enqueue_scripts'         , array( $this,  'hu_require_wp_masonry_js' ) );

            //FILTER THE DEFAULT TEMPLATE FOR POST LIST ARTICLES
            //this filter is declared in hu_get_template_part() in functions/init-front.php
            add_filter( 'hu_tmpl_post-list-articles' , array( $this, 'hu_set_masonry_template_path') );

            //Placeholders are not allowed in masonry grid
            add_filter( 'hu_opt_placeholder'         , array( $this, 'hu_unset_masonry_img_placeholders' ) );

            // Compatibility fix for Nimble Builder
            // see https://github.com/presscustomizr/nimble-builder/issues/227
            add_filter( 'pc_hapmas_print_grid_start_wrapper', array( $this, 'hu_maybe_masonry_grid_start_wrapper_needs_to_be_printed' ) );
            add_filter( 'pc_hapmas_print_grid_end_wrapper', array( $this, 'hu_maybe_masonry_grid_end_wrapper_needs_to_be_printed' ) );
      }


      //hook : hu_tmpl_post-list-articles
      function hu_set_masonry_template_path( $path ) {
            return ha_locate_template( 'addons/pro/grids/masonry/front/tmpl/masonry-article-list.php' );
      }


      //hook: 'hu_opt_placeholder'
      function hu_unset_masonry_img_placeholders( $bool ) {
            //allows placeholder outside the loop
            return 'masonry-grid' == hu_get_option( 'pro_post_list_design' ) ? !in_the_loop() && $bool : $bool;
      }


      //hook : wp_footer
      function hu_add_inline_js() {
            $columns = esc_attr( hu_get_option( 'pro_grid_columns' ) );
            ?>
            <script id="masonry-js" type="text/javascript">
              ( function() {
                  var _fireWhenCzrAppAndMasonryReady = function() {
                      jQuery( function($) {
                        var initOnCzrReady = function() {
                            var $grid_container = $('#grid-wrapper.masonry'),
                                masonryReady = $.Deferred(),
                                _debouncedMasonryLayoutRefresh = _.debounce( function(){
                                          if ( masonryActive ) {
                                                $grid_container.masonry( 'layout' );
                                          }
                                }, 200 ),
                                masonryActive = false;

                              if ( 1 > $grid_container.length ) {
                                    czrapp.errorLog('Masonry container does not exist in the DOM.');
                                    return;
                              }
                              $grid_container.on( 'masonry-init.hueman', function() {
                                    masonryReady.resolve();
                              });

                              function isMobile() {
                                    return czrapp.base.matchMedia && czrapp.base.matchMedia(575);
                              }

                              function masonryInit() {
                                    $grid_container.masonry({
                                          itemSelector: '.grid-item',
                                          //to avoid scale transition of the masonry elements when revealed (by masonry.js) after appending
                                          hiddenStyle: { opacity: 0 },
                                          visibleStyle: { opacity: 1 },
                                          isOriginLeft: 'rtl' === $('html').attr('dir') ? false : true
                                    })
                                    //Refresh layout on image loading
                                    .on( 'smartload simple_load', 'img', function(evt) {
                                          //We don't need to refresh the masonry layout for images in containers with fixed aspect ratio
                                          //as they won't alter the items size. These containers are those .grid-item with full-image class
                                          if ( $(this).closest( '.grid-item' ).hasClass( 'full-image' ) ) {
                                                return;
                                          }
                                          _debouncedMasonryLayoutRefresh();
                                    });
                                    masonryActive = true;
                              }

                              function masonryDestroy() {
                                    $grid_container.masonry('destroy');
                                    masonryActive = false;
                              }

                              // If the grids has only 1 column we don't init nor need to bind any masonry code.
                              if ( 1 === <?php echo $columns; ?> ) {
                                    $( '.post-inner', $grid_container ).css( 'opacity', 1 );
                                    masonryActive = false;
                                    masonryReady.resolve();
                              } else {
                                    //Init Masonry on imagesLoaded
                                    //@see https://github.com/desandro/imagesloaded
                                    //
                                    //Even if masonry is not fired, let's emit the event anyway
                                    //It might be listen to !
                                    $grid_container.imagesLoaded( function() {
                                          if ( ! isMobile() ) {
                                                // init Masonry after all images have loaded
                                                masonryInit();
                                          }
                                          //Even if masonry is not fired, let's emit the event anyway
                                          $grid_container.trigger( 'masonry-init.hueman' );
                                    });

                                    czrapp.userXP.isResizing.bind( function( is_resizing ) {
                                          if ( ! is_resizing ) {//resize finished
                                                var _isMobile = isMobile();
                                                if ( _isMobile  && masonryActive ) {
                                                      masonryDestroy();
                                                } else if ( !_isMobile && !masonryActive ) {
                                                      masonryInit();
                                                }
                                          }
                                    });
                              }

                              //Reacts to the infinite post appended
                              czrapp.$_body.on( 'post-load', function( evt, data ) {
                                    var _do = function( evt, data ) {
                                        if( data && data.type && 'success' == data.type && data.collection && data.html ) {
                                              if ( masonryActive ) {
                                                    //get jquery items from the collection which is like

                                                    //[ post-ID1, post-ID2, ..]
                                                    //we grab the jQuery elements with those ids in our $grid_container
                                                    var $_items = $( data.collection.join(), $grid_container );

                                                    if ( $_items.length > 0 ) {
                                                          $_items.imagesLoaded( function() {
                                                                //inform masonry that items have been appended: will also re-layout
                                                                $grid_container.masonry( 'appended', $_items )
                                                                               //fire masonry done passing our data (we'll listen to this to trigger the animation)
                                                                               .trigger( 'masonry.hueman', data );

                                                                setTimeout( function(){
                                                                      //trigger scroll
                                                                      $(window).trigger('scroll.infinity');
                                                                }, 150);
                                                          });
                                                    }
                                              } else {
                                                  //even if masonry is disabled we still need to emit 'masonry.customizr' because listened to by the infinite code to trigger the animation
                                                  //@see pc-pro-bundle/infinite/init-pro-infinite.php
                                                  if ( $.fn.imagesLoaded ) {
                                                      $grid_container.imagesLoaded( function() { $grid_container.trigger( 'masonry.hueman', data ); } );
                                                  } else {
                                                      $grid_container.trigger( 'masonry.hueman', data );
                                                  }
                                              }
                                        }//if data
                                  };
                                  if ( 'resolved' == masonryReady.state() ) {
                                        _do( evt, data );
                                  } else {
                                        masonryReady.then( function() {
                                              _do( evt, data );
                                        });
                                  }
                              });
                        };//initOnCzrReady

                        initOnCzrReady();

                      });//jQuery
                      //czrapp.ready.done( initOnCzrReady );
                  };//_fireWhenCzrAppAndMasonryReady()


                jQuery( function($) {
                      var _fireWhenCzrAppReady = function() {
                          if ( $.fn.masonry ) {
                              _fireWhenCzrAppAndMasonryReady();
                          } else {
                              // if masonry has already be printed, let's listen to the load event
                              var masonry_script_el = document.querySelectorAll('[src*="wp-includes/js/jquery/masonry"]');

                              if ( masonry_script_el[0] ) {
                                  masonry_script_el[0].addEventListener('load', function() {
                                      _fireWhenCzrAppAndMasonryReady();
                                  });
                              }
                          }
                      };

                      if ( window.czrapp && czrapp.ready && 'resolved' === czrapp.ready.state() ) {
                            _fireWhenCzrAppReady();
                      } else {
                            document.addEventListener('czrapp-is-ready', _fireWhenCzrAppReady );
                      }
                });//jQuery

              })();
            </script>
      <?php
      }




      //hook : wp_head
      function hu_add_inline_css() {
      ?>
            <style id="masonry-css" type="text/css">

                  /*Style as cards */
                  .masonry .grid-item  {
                        /* to allow the post-inner border and box shadow */
                        overflow: visible;
                  }
                  /*
                  * We don't display the placeholder, but we still want
                  * to display the format icon and the comments the right way when there is no thumb img
                  */
                  .masonry .grid-item:not(.has-post-thumbnail) .post-thumbnail {
                        text-align: right;
                  }
                  .masonry .grid-item:not(.has-post-thumbnail) .post-comments{
                        position: relative;
                        display: inline-block;
                  }
                  .masonry .grid-item:not(.has-post-thumbnail) .thumb-icon{
                        position: relative;
                        top: 16px;
                        bottom: auto;
                  }

                  .masonry .grid-item .post-inner {
                        background: white;
                        outline: 1px solid #efefef;
                        outline-offset: -1px;
                        -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.025);
                        -moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.025);
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.025);
                        -webkit-backface-visibility: hidden;
                        -moz-backface-visibility: hidden;
                        backface-visibility: hidden;
                        -webkit-transition: transform 0.1s ease-in-out;
                        -moz-transition: transform 0.1s  ease-in-out;
                        -ms-transition: transform 0.1s ease-in-out;
                        transition: transform 0.1s ease-in-out;
                        /* apply the overflow hidden to the post-inner as we had to remove from the article.grid-item
                        * see rule above
                        */
                        overflow: hidden;
                        position: relative;
                  }
                  .content {
                        overflow: hidden;
                  }


                  #grid-wrapper.masonry .post-inner.post-hover:hover {
                        -webkit-box-shadow: 0 6px 10px rgba(0, 0, 0, 0.055);
                        -moz-box-shadow: 0 6px 10px rgba(0, 0, 0, 0.055);
                        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.055);
                        -webkit-transform: translate(0, -4px);
                        -moz-transform: translate(0, -4px);
                        -ms-transform: translate(0, -4px);
                        transform: translate(0, -4px);
                  }
                  /* spacing */
                  .masonry .post-thumbnail {
                        margin: 0;
                  }
                  .masonry .post-inner .post-content{
                       padding:1.5em;
                  }
                  /* end style as cards */

            </style>
            <?php
      }





      //hook: wp_enqueue_script
      function hu_require_wp_masonry_js() {
            // no need to require the masonry script if we have one column only.
            if ( '1' === hu_get_option( 'pro_grid_columns' ) ) {
                  return;
            }
            wp_enqueue_script( 'masonry' );
      }


      //hook : pc_hapmas_print_grid_start_wrapper
      // see https://github.com/presscustomizr/nimble-builder/issues/227
      function hu_maybe_masonry_grid_start_wrapper_needs_to_be_printed( $bool ) {
            global $wp_query;
            return  0 == $wp_query -> current_post;
      }

      //hook : pc_hapmas_print_grid_end_wrapper
      // see https://github.com/presscustomizr/nimble-builder/issues/227
      function hu_maybe_masonry_grid_end_wrapper_needs_to_be_printed( $bool ) {
            global $wp_query;
            return $wp_query -> current_post == $wp_query -> post_count -1;
      }

}
