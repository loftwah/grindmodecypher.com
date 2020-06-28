<?php
error_log('SLIDER JS TMPL');
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
$model = HU_AD() -> ha_get_model( 'slider', array( PC_HAP_front::$instance , '_get_pro_header_model') );
$slider_opts  = $model['options'];
if ( ! is_array( $slider_opts ) || empty( $slider_opts ) ) {
  ha_error_log( 'In slider-js-tmpl.php : invalid $slider_opts' );
  return;
}
$module_id    = ( array_key_exists( 'module_id', $slider_opts ) && ! empty( $slider_opts['module_id'] ) ) ? $slider_opts['module_id'] : 'pro_large_header';
$slider_speed = ( array_key_exists( 'slider-speed', $slider_opts ) && is_numeric( $slider_opts['slider-speed'] ) ) ? $slider_opts['slider-speed'] : 4;
$is_single_slide = 1 >= count( $model['slides'] ) * 1;
$is_lazy_load = hu_booleanize_checkbox_val( $slider_opts['lazyload'] );
$is_free_scroll = hu_booleanize_checkbox_val( $slider_opts['freescroll'] );

$is_parallax_on = hu_booleanize_checkbox_val( $slider_opts['parallax'] );
$parallax_speed = ( array_key_exists( 'parallax-speed', $slider_opts ) && is_numeric( $slider_opts['parallax-speed'] ) ) ? $slider_opts['parallax-speed'] : 55;

$is_autoplay_on = hu_booleanize_checkbox_val( $slider_opts['autoplay'] );
$is_pause_hover_on = hu_booleanize_checkbox_val( $slider_opts['pause-on-hover'] );

$is_fixed_caption = hu_booleanize_checkbox_val( $slider_opts['fixed-content'] );

$caption_font_ratio = is_numeric( intval( $slider_opts['font-ratio'] ) ) ? intval( $slider_opts['font-ratio'] ) : 0;

//Slider options to add
// is video ?
// is google map
?>

<script type="text/javascript" id="<?php echo $module_id; ?>">
  jQuery( function($) {
        /* Handle sliders nav */
        /*Handle custom nav */
        // previous
        var _isSingleSlide = <?php echo true == $is_single_slide ? 'true' : 'false'; ?>,
            _isAutoplay = <?php echo $is_autoplay_on ? 'true' : 'false'; ?>,
            //Time interval is saved in seconds and has to be converted into ms
            _timeInterval = _.isNumber( <?php echo $slider_speed; ?> ) ? <?php echo $slider_speed; ?> * 1000 : 4000,//<= in ms
            _pauseAutoPlayOnHover = <?php echo $is_pause_hover_on ? 'true' : 'false'; ?>,
            _isLazyLoad = <?php echo $is_lazy_load ? 'true' : 'false'; ?>,
            _isFreeScroll = <?php echo $is_free_scroll ? 'true' : 'false'; ?>,
            _isParallaxOn = <?php echo $is_parallax_on ? 'true' : 'false'; ?>,
            _parallaRatio = _.isNumber( parseInt( <?php echo $parallax_speed; ?>, 10 ) ) ? Math.round( parseInt( <?php echo $parallax_speed; ?>, 10 ) * 100.0 / 100) / 100 : 0.55;

            _timeInterval = _.isNumber( <?php echo $slider_speed; ?> ) ? <?php echo $slider_speed; ?> * 1000 : 4000,//<= in ms

            _isFixedCaption = <?php echo $is_fixed_caption ? 'true' : 'false'; ?>,

            _captionFontRatio = _.isNumber( parseInt( <?php echo $caption_font_ratio; ?>, 10 ) ) ? parseInt( <?php echo $caption_font_ratio; ?>, 10 ) : 0,

            pro_header_slider_short_opt_name = '<?php echo HU_AD() -> pro_header -> pro_header_slider_short_opt_name ?>',//'pro_slider_header_bg'
            _slider_arrows = function ( evt, side ) {
                evt.preventDefault();
                var $_this    = $(this),
                    _flickity = $_this.data( 'controls' );

                if ( ! $_this.length )
                  return;

                //if not already done, cache the slider this control controls as data-controls attribute
                if ( ! _flickity ) {
                      _flickity   = $_this.closest('.pc-section-slider').find('.flickity-enabled').data('flickity');
                      $_this.data( 'controls', _flickity );
                }
                if ( 'previous' == side ) {
                      _flickity.previous();
                } else if ( 'next' == side ) {
                      _flickity.next();
                }
            };

        //caption Font ratio
        _captionFontRatio = Math.abs( parseInt( _captionFontRatio, 10 ) ) > 50 ? 0 : parseInt( _captionFontRatio, 10 );
        _captionFontRatio = 1 + ( Math.round( _captionFontRatio * 100.0 / 100 ) / 100 );

        //LAZYLOAD OPTION
        // => if freescroll is on, we need to load the images in more than 1 adjacent cells
        // if not, then let's only load the image of the current cell
        // @see http://flickity.metafizzy.co/options.html#lazyload
        var _lazyLoadOpt = false;
        if ( _isLazyLoad ) {
            _lazyLoadOpt = _isFreeScroll ? 2 : true;
            // load images in selected slide
            // and next 2 slides
            // and previous 2 slides
        };


        ///NORMAL FRONT END SCENARIO : FLICKITY IS INSTANTIATED ONCE
        ///CUSTOMIZE PARTIAL REFRESH SCENARIO : FLICK. IS INSTANTIATED ONCE, AND DESTROYED + RE-INSTANTIATED ON REFRESH
        //_init should be fired once on $('body').on( 'czrapp-ready', _init );
        //=> in partial refresh scenarios :
        //1) flickity instance is first destroyed on 'pre_setting' event send to the preview (@ see the js preview actions written on 'customize_preview_init' )
        //2) then re-instantiated on 'partial-content-rendered'
        var _init = function() {
                return $.Deferred( function() {
                    var dfd = this;


                    /* Flickity ready
                    * see https://github.com/metafizzy/flickity/issues/493#issuecomment-262658287
                    */
                    var activate = Flickity.prototype.activate;
                    Flickity.prototype.activate = function() {
                          if ( this.isActive ) {
                            return;
                          }
                          var self = this;
                          activate.apply( this, arguments );
                          $( self.element ).trigger( 'hu-flickity-ready' );
                          //this.dispatchEvent( 'hu-flickity-ready' );
                    };

                    /*Handle custom nav */
                    // previous
                    czrapp.$_body.on( 'click tap prev.hu-slider', '.slider-prev', function(e) { _slider_arrows.apply( this , [ e, 'previous' ] );} );
                    // next
                    czrapp.$_body.on( 'click tap next.hu-slider', '.slider-next', function(e) { _slider_arrows.apply( this , [ e, 'next' ] );} );
                    dfd.resolve()
                } ).promise();
            },//_init()
            fireFlickity = function() {
                //AUTO-PLAY
                //@see http://flickity.metafizzy.co/options.html#autoplay
                var _autoPlay = false;
                if ( _isAutoplay ) {
                      _autoPlay = ( _.isNumber( _timeInterval ) && _timeInterval > 0 ) ? _timeInterval : true;
                }
                $('.carousel-inner', '#<?php echo $module_id; ?>').flickity({
                      prevNextButtons: false,
                      pageDots: true,
                      wrapAround: true,
                      imagesLoaded: true,
                      setGallerySize: false,
                      cellSelector: '.carousel-cell',
                      dragThreshold: 10,
                      autoPlay: _autoPlay, // {Number in milliseconds }
                      pauseAutoPlayOnHover: _pauseAutoPlayOnHover,
                      accessibility: false,
                      pageDots: ! _isSingleSlide,
                      lazyLoad: _lazyLoadOpt,//<= load images up to 3 adjacent cells when freescroll enabled
                      draggable: ! _isSingleSlide,
                      freeScroll: _isFreeScroll,
                      freeScrollFriction: 0.03,// default : 0.075
                });
            },
            centerSlides = function() {
                //center slider
                //SLIDER IMG + VARIOUS
                setTimeout( function() {
                      //centering per slider
                      $.each( $( '.carousel-inner', '#<?php echo $module_id; ?>' ) , function() {
                            $( this ).centerImages( {
                                  enableCentering : 1, // == HUParams.centerSliderImg,
                                  imgSel : '.carousel-image img',
                                  /* To check settle.flickity is working, it should according to the docs */
                                  oncustom : ['lazyLoad.flickity','settle.flickity', 'simple_load'],
                                  defaultCSSVal : { width : '100%' , height : 'auto' },
                                  useImgAttr : true,
                                  zeroTopAdjust: 0
                            });
                            //fade out the loading icon per slider with a little delay
                            //mostly for retina devices (the retina image will be downloaded afterwards
                            //and this may cause the re-centering of the image)
                            /*
                            var self = this;
                            setTimeout( function() {
                                $( self ).prevAll('.czr-slider-loader-wrapper').fadeOut();
                            }, 500 );
                            */
                      });
                } , 50);
            },
            _fire_ = function() {
                var $_flickEl = $('.carousel-inner','#<?php echo $module_id; ?>' );

                $_flickEl.on( 'hu-flickity-ready.flickity', function( evt ) {
                      //slider parallax on flickity ready
                      //we parallax only the flickity-viewport, so that we don't parallax the carouasel-dots
                      if ( _isParallaxOn ) {
                          //will-change : @see https://developer.mozilla.org/en-US/docs/Web/CSS/will-change
                          $( evt.target )
                              .children( '.flickity-viewport' )
                                  .css('will-change', 'transform')
                                  .czrParallax( { parallaxRatio : _parallaRatio });
                      }
                      //move the caption wrapper inside the flickity viewport if fixed caption on
                      if ( _isFixedCaption ) {
                          var $capWrap = $_flickEl.find('.carousel-caption-wrapper');
                          $_flickEl.find('.flickity-viewport').prepend( $capWrap );
                      }
                });

                //Fix img flickering when dragging or sliding on ios devices
                //=> add translateZ
                var _getTranslateProp = function( event ) {
                    var _translate = 'select' == event ? 'translate3d(-50%, -50%, 0)' : '';
                    return {
                          '-webkit-transform': _translate,
                          '-ms-transform': _translate,
                          '-o-transform': _translate,
                          'transform': _translate
                    };
                }

                //Fire and center
                fireFlickity();
                centerSlides();

                //reveal and listen to user actions to fix visual inconsitencies
                var _isSettle = true, _isScrolling = false;
                _.delay( function() {
                    $_flickEl.css({ opacity : 1 });
                    $('#ha-large-header').find( '#<?php echo $module_id; ?>' ).addClass('slider-ready');
                    $_flickEl.on( 'scroll.flickity', _.throttle( function( evt ) {
                      console.log('SCROLL');
                      _isScrolling = true;
                      $_flickEl.find('.carousel-caption').css( _getTranslateProp( _isSettle ? 'settle' : 'select' ) );
                    }, 250 ) );
                    $_flickEl.on( 'select.flickity', function( evt ) {
                        console.log('ON SELECT FLICKITY');
                        _isSettle = false;
                        // $_flickEl.find('.carousel-caption').css( _getTranslateProp( 'select' ) );
                        // var _do = function() {
                        //     console.log('IS SETTLE ?', _isSettle);
                        //     console.log('IS SCROLLING', _isScrolling );
                        //     $_flickEl.find('.carousel-caption').css( _getTranslateProp( ( _isSettle && ! _isScrolling ) ? 'settle' : 'select' ) );
                        // };
                        // _.debounce( _do , 1000 );
                        // _do();

                    } );
                    $_flickEl.on( 'settle.flickity', function( evt ) {
                        console.log('ON SETTLE FLICKITY');
                        _isSettle = true;
                        _isScrolling = false;
                        //if ( ! _isSelected )
                          $_flickEl.find('.carousel-caption').css( _getTranslateProp( 'settle' ) );
                    } );
                }, 50 );

                //EXPERIMENTAL @see : http://flickity.metafizzy.co/events.html#scroll
                // var flkty = $_flickEl.data('flickity');
                // var $imgs = $('.carousel-cell .carousel-image');

                // $_flickEl.on( 'scroll.flickity', function( event, progress ) {
                //     flkty.slides.forEach( function( slide, i ) {
                //       var img = $imgs[i];
                //       var x = ( slide.target + flkty.x ) * -1/3;
                //       img.style.transform = 'translateX( ' + x  + 'px)';
                //     });
                // });
            };


        //Fire when czr app ready
        if ( czrapp && 'pending' == window.czrapp.ready.state() ) {
            czrapp.ready.done( function() {
                _init().done( function() {
                    _fire_();
                });
            });
        } else {
            if ( _.isUndefined( czrapp ) ) {
                _init().done( function() {
                      _fire_();
                });
            } else {
                var $_flickEl = $('.carousel-inner','#<?php echo $module_id; ?>' );
                //The flick. slider is always instanciated based on the db module id,
                //Do we have an element and has flickity been instantiated ?
                if ( false !== $_flickEl.length && ! _.isUndefined( $_flickEl.data('flickity') ) )
                  return;

                //Fire again
                //=> It has been destroyed on 'pre_setting' event sent by the preview
                //$('.carousel-inner','#' + args.data.module_id ).flickity( 'destroy' );
                _fire_();
            }
        }

        //React on partial refresh events
        //DEPRECATED
        <?php //global $wp_customize; ?>
        <?php //if ( is_customize_preview() && isset( $wp_customize->selective_refresh ) ) : ?>
              // //partial-content-rendered
              // czrapp.partials = czrapp.partials || {};
              // //only bind once.
              // if ( ! _.has( czrapp.partials, pro_header_slider_short_opt_name ) ) { // pro_header_slider_short_opt_name = pro_header_bg
              //     wp.customize.selectiveRefresh.bind( 'partial-content-rendered', function( params ) {
              //         if ( ! _.has( params, 'partial' ) || pro_header_slider_short_opt_name != params.partial.id )
              //           return;
              //         czrapp.partials[pro_header_slider_short_opt_name] = true;
              //         //Fire again
              //         //=> It has been destroyed on 'pre_setting' event sent by the preview
              //         if ( 'pending' == flickityFired.state() )
              //           _fire_();
              //     });
              // }
        <?php //endif; ?>


        (function( $ ){

          $.fn.fitText = function( kompressor, options ) {

            // Setup options
            var compressor = kompressor || 1,
                settings = $.extend({
                  'minFontSize' : Number.NEGATIVE_INFINITY,
                  'maxFontSize' : Number.POSITIVE_INFINITY
                }, options);

            return this.each(function(){

              // Store the object
              var $this = $(this);

              // Resizer() resizes items based on the object width divided by the compressor * 10
              var resizer = function () {
                var _font_size = Math.max(Math.min($this.width() / (compressor*10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize));
                _font_size = _font_size * _captionFontRatio;

                $this.css('font-size', _font_size  );
                $this.css('line-height', ( _font_size  * 1.45 ) + 'px');
              };

              // Call once to set.
              resizer();

              // Call on resize. Opera debounces their resize by default.
              $(window).on('resize.fittext orientationchange.fittext', resizer);

              //When customizing, call on post messaging
              if ( czrapp && czrapp.ready ) {
                  czrapp.ready.then( function() {
                      if ( czrapp.userXP._isCustomizing() ) {
                          var _resizeOnInputChange = function() {
                              wp.customize.preview.bind( 'czr_input', function() {
                                  resizer();
                              });
                          };
                          if ( wp.customize.topics && wp.customize.topics['preview-ready'] && wp.customize.topics['preview-ready'].fired() ) {
                              _resizeOnInputChange();
                          } else {
                              wp.customize.bind( 'preview-ready', _resizeOnInputChange );
                          }
                      }
                  });
              }

            });

          };

        })( jQuery );

        $('.carousel-caption .hph-title').fitText( 1.1, { maxFontSize : 80 * _captionFontRatio } );
        $('.carousel-caption .hph-subtitle').fitText( 1.2, { maxFontSize : 30 * _captionFontRatio } );
        //$('.carousel-caption .hph-cta').fitText( 1.2, { maxFontSize : 16 * _captionFontRatio } );
  });
</script>