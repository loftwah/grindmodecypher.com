/***************************
* PRO HEADER SLIDER METHODS
****************************/
// proHeaderSlid : {
//       ctor : czrapp.Base.extend( czrapp.methods.ProHeaderSlid ),
//       ready : [ 'fire' ]
// }
( function() {
    var _fireWhenCzrAppReady = function() {
        jQuery( function($) {


        var _methods =  {
            //@return void()
            //can be invoked on init when DOM ready by the usual czrapp loop, and on partial refresh when customizing @see slider php template
            fire : function( args ) {
                  var self = this;
                  self.hasBeenInit = self.hasBeenInit || $.Deferred();//resolved on _init(). Can be already defined when partially refreshing in a customizer session

                  //Overrides the instance properties by the one provided in args
                  //=> used when customizing
                  if ( _.isObject( args ) ) {
                        args = _.extend(
                              {
                                    module_id          : '',
                                    isSingleSlide    : false,
                                    isLazyLoad       : true,
                                    isFreeScroll     : false,
                                    isParallaxOn     : true,
                                    parallaxRatio    : 0.55,//parallax-speed
                                    isFixedCaption   : false,
                                    timeInterval       : 5000,//in ms
                                    isAutoplay     : false,
                                    pauseAutoPlayOnHover  : true,
                                    captionFontRatio : 0,
                                    isDoingPartialRefresh : false
                              },
                              args || {}
                        );

                        //overrides prototype properties with the new ones
                        $.extend( self, args );
                  }

                  if ( _.isEmpty( self.module_id ) ) {
                        throw new Error( 'proHeaderSlid, missing module_id' );
                  }

                  self.loadingIconVisible = new czrapp.Value( false );
                  self.loadingIconVisible.bind( function( visible ) {
                        var $_icon = $('#ha-large-header').find('.czr-css-loader');
                        if ( 1 != $_icon.length )
                          return;
                        if ( visible ) {
                              $.when( $_icon.css( { display : 'block', opacity : 0 } ) ).done( function() {
                                    $_icon.css( { opacity : 1 } );
                              });
                        } else {
                              $_icon.css( { opacity : 0 } );
                              _.delay( function() {
                                    $_icon.css( { display : 'none'});
                              }, 800 );
                        }
                        //Always auto set to false after 2 seconds
                        clearTimeout( $.data( this, 'loadIconTimer') );
                        $.data( this, 'loadIconTimer', _.delay( function() {
                              self.loadingIconVisible( false );
                        }, 2000 ) );
                  });
                  self.loadingIconVisible( true );

                  //pro_header_slider_short_opt_name = '<?php echo HU_AD() -> pro_header -> pro_header_slider_short_opt_name ?>',//'pro_slider_header_bg'


                  //CAPTION FONT RATIO
                  self.captionFontRatio = Math.abs( parseInt( self.captionFontRatio, 10 ) ) > 50 ? 0 : parseInt( self.captionFontRatio, 10 );
                  self.captionFontRatio = 1 + ( Math.round( self.captionFontRatio * 100.0 / 100 ) / 100 );


                  //FIT TEXT
                  //Add $.plugin on first load
                  if ( _.isUndefined( $.fn.proHeaderFitText ) ) {
                        self._addJqueryFitText();
                  }

                  czrapp.bind( 'flickity-slider-fired', function( $flickityEl ) {
                        if ( 1 <= $flickityEl.find('.carousel-caption .hph-title').length ) {
                              $flickityEl.find('.carousel-caption .hph-title').proHeaderFitText(
                                    1.5,//<=kompressor
                                    {
                                          maxFontSize : 65 * self.captionFontRatio,//the default max font-size must also be modified in the hph-front.css stylesheet
                                          minFontSize : 30,
                                          captionFontRatio : self.captionFontRatio
                                    }
                              );
                        }
                        if ( 1 <= $flickityEl.find('.carousel-caption .hph-subtitle').length ) {
                              $flickityEl.find('.carousel-caption .hph-subtitle').proHeaderFitText(
                                    1.9,
                                    {
                                          maxFontSize : 35 * self.captionFontRatio,//the default max font-size must also be modified in the hph-front.css stylesheet
                                          minFontSize : 20,
                                          captionFontRatio : self.captionFontRatio
                                    }
                              );
                        }
                        if ( 1 <= $flickityEl.find('.carousel-caption .meta-single').length ) {
                              $flickityEl.find('.carousel-caption .meta-single').proHeaderFitText(
                                    2.5,
                                    {
                                          maxFontSize : 16.5 * self.captionFontRatio,//the default max font-size must also be modified in the hph-front.css stylesheet
                                          minFontSize : 14,
                                          captionFontRatio : self.captionFontRatio
                                    }
                              );
                        }
                  });

                  //$('.carousel-caption .hph-cta').fitText( 1.2, { maxFontSize : 16 * self.captionFontRatio } );


                  //LAZYLOAD OPTION
                  // => if freescroll is on, we need to load the images in more than 1 adjacent cells
                  // if not, then let's only load the image of the current cell
                  // @see http://flickity.metafizzy.co/options.html#lazyload
                  self.lazyLoadOpt = false;
                  if ( self.isLazyLoad ) {
                        self.lazyLoadOpt = self.isFreeScroll ? 2 : true;
                        // load images in selected slide
                        // and next 2 slides
                        // and previous 2 slides
                  }


                  ///NORMAL FRONT END SCENARIO : FLICKITY IS INSTANTIATED ONCE
                  ///CUSTOMIZE PARTIAL REFRESH SCENARIO : FLICK. IS INSTANTIATED ONCE, AND DESTROYED + RE-INSTANTIATED ON REFRESH
                  //=> in partial refresh scenarios :
                  //1) flickity instance is first destroyed on 'pre_setting' event send to the preview (@ see the js preview actions written on 'customize_preview_init' )
                  //2) then re-instantiated on 'partial-content-rendered'

                  //Fire when czr app ready
                  czrapp.ready.then( function() {
                        var _doFire = function() {
                              //fire and assign the $ el to a property
                              self.flickityEl = self._fire_();

                              if ( ! self.flickityEl || ! _.isObject( self.flickityEl ) || 1 > self.flickityEl.length ) {
                                    czrapp.errorLog( 'Pro Header Flickity slider not properly fired' );
                              } else {
                                    czrapp.trigger( 'flickity-slider-fired', self.flickityEl );
                              }
                        };

                        if ( 'pending' == self.hasBeenInit.state() ) {
                              self._init().done( function() {
                                    _doFire();
                              });
                        } else {
                              //THIS IS THE PARTIAL REFRESH CASE
                              //The flick. slider is always instanciated based on the db module id,
                              //Do we have an element and has flickity been instantiated ?
                              //if so, bail here
                              if ( 1 >= self.flickityEl.length && ! _.isUndefined( self.flickityEl.data('flickity') ) )
                                return;

                              //if not, fire again
                              //=> It has been destroyed on 'pre_setting' event sent by the preview
                              //$('.carousel-inner','#' + args.data.module_id ).flickity( 'destroy' );
                              _doFire();
                        }
                  });
            },//fire()


            ///NORMAL FRONT END SCENARIO : FLICKITY IS INSTANTIATED ONCE
            ///CUSTOMIZE PARTIAL REFRESH SCENARIO : FLICK. IS INSTANTIATED ONCE, AND DESTROYED + RE-INSTANTIATED ON REFRESH
            //_init should be fired once on on( 'czrapp-is-ready', _init );
            //=> in partial refresh scenarios :
            //1) flickity instance is first destroyed on 'pre_setting' event send to the preview (@ see the js preview actions written on 'customize_preview_init' )
            //2) then re-instantiated on 'partial-content-rendered'
            //@return promise()
            _init : function() {
                  var self = this;
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

                        //extend the original lazyload to emit an event on start
                        //=> this will be listen to by the loading icon
                        var originalLazyLoad = Flickity.LazyLoader.prototype.load;
                        Flickity.LazyLoader.prototype.load = function() {
                              var self = this;
                              this.flickity.dispatchEvent( 'lazyLoad-start', null, self.img.getAttribute('data-flickity-lazyload') );
                              // set srcset attribute from temporary attribute
                              // implemented for
                              $img = $(self.img);
                              // Feb 2021 => Removed srcset and imgsizes attributes server side to prevent poor image quality on mobiles when using on chrome ( and potentially other browsers )
                              // see https://github.com/presscustomizr/hueman-pro-addons/issues/217
                              //$img.attr('srcset', $img.attr('data-flickity-srcset') ).attr('sizes', $img.attr('data-flickity-imgsizes') );
                              //$img.removeAttr('data-flickity-srcset').removeAttr('data-flickity-imgsizes');
                              originalLazyLoad.apply( this, arguments );
                        };

                        /*Handle custom nav */
                        // previous
                        czrapp.$_body.on( 'click tap prev.hu-slider', '.slider-prev', function(e) { self._slider_arrows.apply( this , [ e, 'previous' ] );} );
                        // next
                        czrapp.$_body.on( 'click tap next.hu-slider', '.slider-next', function(e) { self._slider_arrows.apply( this , [ e, 'next' ] );} );

                        self.hasBeenInit.resolve();

                        dfd.resolve();
                  } ).promise();
            },//_init()


            //@return $_flickEl
            _fire_ : function() {
                  var self = this,
                      $_flickEl = $('.carousel-inner', '#' + self.module_id );

                  if ( 1 > $_flickEl.length ) {
                        czrapp.errorLog( 'Flickity slider dom element is empty : ' + self.module_id );
                        return;
                  } else if ( 1 < $_flickEl.length ) {
                        czrapp.errorLog( 'Header Slider Aborted : more than one flickity slider dom element : ' + self.module_id );
                        return;
                  }

                  $_flickEl.on( 'hu-flickity-ready.flickity', function( evt ) {
                        //slider parallax on flickity ready
                        //we parallax only the flickity-viewport, so that we don't parallax the carouasel-dots
                        if ( self.isParallaxOn ) {
                            //will-change : @see https://developer.mozilla.org/en-US/docs/Web/CSS/will-change
                            $( evt.target )
                                .children( '.flickity-viewport' )
                                    .css('will-change', 'transform')
                                    .czrParallax( { parallaxRatio : self.parallaxRatio });
                        }
                        //move the caption wrapper inside the flickity viewport if fixed caption on
                        if ( self.isFixedCaption ) {
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
                  };

                  //Fire and center
                  self._flickityse();
                  self._centerSlidise();

                  //reveal and listen to user actions to fix visual inconsitencies
                  var _isSettle = true, _isScrolling = false;

                  _.delay( function() {
                        $_flickEl.on( 'scroll.flickity', _.throttle( function( evt ) {
                              _isScrolling = true;
                              $_flickEl.find('.carousel-caption').css( _getTranslateProp( _isSettle ? 'settle' : 'select' ) );
                        }, 250 ) );

                        $_flickEl.on( 'select.flickity', function( evt ) {
                              _isSettle = false;
                        } );
                        $_flickEl.on( 'settle.flickity', function( evt ) {
                              _isSettle = true;
                              _isScrolling = false;
                              $_flickEl.find('.carousel-caption').css( _getTranslateProp( 'settle' ) );
                        } );
                        if ( czrapp.userXP && czrapp.userXP.isResizing ) {
                              //@see windowWidth listener in czrapp utils
                              ///"real" horizontal resize reaction : refreshed every 50 ms
                              //debounced because flickity can fires scroll events when resizing
                              czrapp.userXP.isResizing.bind( _.debounce( function( isResizing ) {
                                    $_flickEl.find('.carousel-caption').css( _getTranslateProp( isResizing ? 'select' : 'settle' ) );
                              }, 700 ) );
                        }

                        //LAZYLOAD
                        //display the loading icon if an image is not lazyloaded after 100ms
                        self.imgLazyLoaded = [];
                        var _setIconVisibility = function( visible, imgSrc ) {
                              self.loadingIconVisible( visible );
                        };

                        _setIconVisibility = _.debounce( _setIconVisibility, 100 );

                        $_flickEl.on( 'lazyLoad-start.flickity', function( evt, imgSrc ) {
                              _setIconVisibility( true, imgSrc );
                        } );
                        $_flickEl.on( 'lazyLoad.flickity', function( evt, cellElem ) {
                              if ( 1 == $( cellElem ).length ) {
                                    var $img = $( cellElem ).find('img');
                                    if ( 1 == $img.length  ) {
                                          self.imgLazyLoaded.push( $img.attr('src') );
                                    }
                                    _setIconVisibility( false, $img.attr('src') );
                              } else {
                                    _setIconVisibility( false );
                              }
                        });

                        //hide the loading icon
                        self.loadingIconVisible( false );
                        //reveal the slider
                        $_flickEl.css({ opacity : 1 });

                        $('#ha-large-header').find( '#' + self.module_id ).addClass('slider-ready');
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
                  return $_flickEl;
            },//_fire_()

            //@return void()
            _flickityse : function() {
                  //AUTO-PLAY
                  //@see http://flickity.metafizzy.co/options.html#autoplay
                  var self = this,
                      _autoPlay = false;

                  if ( self.isAutoplay ) {
                        _autoPlay = ( _.isNumber( self.timeInterval ) && self.timeInterval > 0 ) ? self.timeInterval : true;
                  }
                  $('.carousel-inner', '#' + self.module_id ).flickity({
                        prevNextButtons: false,
                        pageDots: ! self.isSingleSlide,
                        wrapAround: true,
                        imagesLoaded: true,
                        setGallerySize: false,
                        cellSelector: '.carousel-cell',
                        dragThreshold: 10,
                        autoPlay: _autoPlay, // {Number in milliseconds }
                        pauseAutoPlayOnHover: self.pauseAutoPlayOnHover,
                        accessibility: false,
                        lazyLoad: self.lazyLoadOpt,//<= load images up to 3 adjacent cells when freescroll enabled
                        draggable: ! self.isSingleSlide,
                        freeScroll: self.isFreeScroll,
                        freeScrollFriction: 0.03,// default : 0.075
                  });
            },


            //SLIDER ARROW UTILITY
            //@return void()
            _slider_arrows : function ( evt, side ) {
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
            },

            //@return void()
            _centerSlidise : function() {
                  var self = this;
                  //center slider
                  //SLIDER IMG + VARIOUS
                  setTimeout( function() {
                        //centering per slider
                        $.each( $( '.carousel-inner', '#' + self.module_id ) , function() {
                              $( this ).centerImages( {
                                    enableCentering : 1, // == HUParams.centerSliderImg,
                                    imgSel : '.carousel-image img',
                                    /* To check settle.flickity is working, it should according to the docs */
                                    oncustom : [ 'lazyLoad.flickity', 'settle.flickity', 'simple_load'],
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


            //React on partial refresh events
            //DEPRECATED
            // <?php //global $wp_customize; ?>
            // <?php //if ( is_customize_preview() && isset( $wp_customize->selective_refresh ) ) : ?>
            //       // //partial-content-rendered
            //       // czrapp.partials = czrapp.partials || {};
            //       // //only bind once.
            //       // if ( ! _.has( czrapp.partials, pro_header_slider_short_opt_name ) ) { // pro_header_slider_short_opt_name = pro_header_bg
            //       //     wp.customize.selectiveRefresh.bind( 'partial-content-rendered', function( params ) {
            //       //         if ( ! _.has( params, 'partial' ) || pro_header_slider_short_opt_name != params.partial.id )
            //       //           return;
            //       //         czrapp.partials[pro_header_slider_short_opt_name] = true;
            //       //         //Fire again
            //       //         //=> It has been destroyed on 'pre_setting' event sent by the preview
            //       //         if ( 'pending' == flickityFired.state() )
            //       //           _fire_();
            //       //     });
            //       // }
            // <?php //endif; ?>

            //This is an adapted version of fit text
            //1) a user defined caption font ratio has been added
            //2) the resizer takes into account not only the element width, but the flickity slider height. => this solves the problem of fonts not properly resized on landscape mobile devices, or slider too short (user can set the slider's height)
            //@return void()
            _addJqueryFitText : function() {
                  if ( $.fn.proHeaderFitText )
                    return;
                  var self = this;
                  $.fn.proHeaderFitText = function( kompressor, options ) {
                        // Setup options
                        var compressor = kompressor || 1,
                            settings = $.extend({
                                'minFontSize' : Number.NEGATIVE_INFINITY,
                                'maxFontSize' : Number.POSITIVE_INFINITY,
                                'captionFontRatio' : 1
                            }, options),
                            _captionFontRatio = settings.captionFontRatio;

                        return this.each(function(){
                              // Store the object
                              var $this = $(this);

                              // Resizer() resizes items based on the object width divided by the compressor * 10
                              var resizer = function () {
                                    var _font_size = Math.max(
                                          Math.min(
                                                $this.width() / (compressor*10),
                                                ( self.flickityEl && self.flickityEl.length >= 1 ) ? self.flickityEl.height() / (compressor*8) : $this.width() / (compressor*10),
                                                parseFloat( settings.maxFontSize )
                                          ),
                                          parseFloat( settings.minFontSize )
                                    );
                                    _font_size = Math.max( _font_size * _captionFontRatio, parseFloat( settings.minFontSize ) );

                                    $this.css('font-size', _font_size  + 'px' );
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
                  };//$.fn.fitText
            }//_addJqueryFitText
      };//_methods{}

      czrapp.methods.ProHeaderSlid = czrapp.methods.ProHeaderSlid || {};
      $.extend( czrapp.methods.ProHeaderSlid , _methods );
      var _evt = document.createEvent('Event');
      _evt.initEvent('hu-hph-front-loaded', true, true); //can bubble, and is cancellable
      document.dispatchEvent(_evt);
      });//jQuery()
    };//_fireWhenCzrAppReady

    if ( window.czrapp && czrapp.ready && 'resolved' === czrapp.ready.state() ) {
          _fireWhenCzrAppReady();
    } else {
          document.addEventListener('czrapp-is-ready', _fireWhenCzrAppReady );
    }

})();