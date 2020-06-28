<?php

//exports some wp_query informations. Updated on each preview refresh.
add_action( 'customize_preview_init' , 'ha_add_preview_footer_action', 20 );

//hook : customize_preview_init
function ha_add_preview_footer_action() {
    //Add the postMessages actions
    add_action( 'wp_footer', 'ha_extend_postmessage_cbs', 1000 );
}
/* HEADER CUSTOMIZER PREVIEW */
//hook : wp_footer in the preview
function ha_extend_postmessage_cbs() {
  ?>
  <script id="preview-settings-cb" type="text/javascript">
    (function (api, $, _ ) {
          var $_body    = $( 'body' ),
            pre_setting_cbs = api.CZR_preview.prototype.pre_setting_cbs || {},
            setting_cbs = api.CZR_preview.prototype.setting_cbs || {},
            input_cbs = api.CZR_preview.prototype.input_cbs || {},
            pro_header_slider_short_opt_name = '<?php echo is_object( HU_AD() -> pro_header ) ? HU_AD() -> pro_header -> pro_header_slider_short_opt_name : ''; ?>',//'pro_slider_header_bg'
            pro_related_posts_short_opt_name = '<?php echo HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name ?>',//'pro_related_posts'
            preSettingCbExtension = {},
            inputCbExtension = {};


          /////////////////////////////////////
          // DESTROY FLICKITY INSTANCES ON PARTIAL REFRESH : FOR PRO HEADER AND PRO RELATED POSTS
          ///////////////////////////
          //Pre setting callbacks are fired on 'pre_setting' event sent to the preview just before the WP native 'setting' postMessage event
          //in partial refresh scenarios, this allows us to execute actions before the re-rendering of the html markup
          //typically here we need to clean a jQuery plugin instance
          preSettingCbExtension[ pro_header_slider_short_opt_name ] = function( args ) {
              if ( ! args.data || ! args.data.module_id )
                return;
              var _flickEl = $('.carousel-inner','#' + args.data.module_id );
              //Destroy the flickity instance if any
              //The flick. slider is always instanciated based on the db module id,
              //which allows us to target it here with the customizer module_id

              //do we have an element and has flickity been instantiated ?
              if ( ! _flickEl.length || _.isUndefined( _flickEl.data('flickity') ) )
                return;
              //destroy the instance
              $('.carousel-inner','#' + args.data.module_id ).flickity( 'destroy' );
              //=> after this, the flickity slider can be safely re-instantiated in the front-end tmpl when partially refreshed
          };


          preSettingCbExtension[ pro_related_posts_short_opt_name ] = function( args ) {
              if ( ! args.data || ! args.data.module_id )
                return;
              var $_flickEl = $('.pro-rel-posts-wrap', '#pro-related-posts');

              //do we have an element and has flickity been instantiated ?
              if ( ! $_flickEl.length || _.isUndefined( $_flickEl.data('flickity') ) )
                return;

              //destroy the instance
              $_flickEl.flickity( 'destroy' );
              $_flickEl.css( 'opacity', 0 );
              //=> after this, the flickity slider can be safely re-instantiated in the front-end tmpl
          };




          // SOME USEFUL UTILITIES
          //@return void()
          var //var args = { module_id : '',  model : { all mod opts }, rgb : [], transparency = 0.65 }
              _writeCurrentRgbaSkin = function( args ) {
                  //What is provided ?
                  args = _.extend( {
                      is_item : false,
                      item_id : '',
                      module_id : '',
                      skin : 'dark',
                      custom_color : 'rgb( 34,34,34 )',
                      transparency : 65
                  }, args );

                  //Assign default values
                  var _rgb = [ 34, 34, 34 ],//dark
                      _transparency = 0.65,
                      _rgba = [],
                      _formatTransparency = function( rawVal ) {
                          if ( _.isNumber( rawVal ) && rawVal < 1 && rawVal > 0 )
                            return rawVal;
                          rawVal = parseInt( rawVal, 10 );
                          return ( ! _.isNumber( rawVal ) || rawVal > 100 || rawVal < 0 ) ? 0.65 : Math.round( rawVal * 100.0 / 100) / 100;
                      };

                  //is the skin provided ?
                  //if not get it from the model
                  if ( args.skin ) {
                      //get the rgb from current model
                      switch( args.skin ) {
                          case 'dark' :
                                _rgb = [ 34, 34, 34 ];
                          break;
                          case 'light' :
                                _rgb = [ 255, 255, 255 ];
                          break;
                          // case 'custom' :
                          //       //the custom skin is sent as a rgb string
                          //       // => normalizes it to an array
                          //       var _candidate = [],
                          //           _customRgb = args.custom_color ? args.custom_color : [ 34, 34, 34 ];
                          //       if ( ! _.isArray( _customRgb ) ) {
                          //           _customRgb = _customRgb.replace('rgba', '').replace('(', '').replace(')', '').replace('rgb','');
                          //           _customRgb =  _customRgb.split(',');
                          //           //removes the a part if any
                          //           if ( 4 == _customRgb.length )
                          //             _customRgb.pop();

                          //           //clean spaces
                          //           _.each( _customRgb, function( _d ) {
                          //               _candidate.push( $.trim( _d ) );
                          //           });
                          //       } else {
                          //           _candidate = _customRgb;
                          //       }
                          //       _rgb = _candidate;
                          // break;
                      }//switch
                  }

                  //is the transparency provided ?
                  if ( args.transparency ) {
                      _transparency = _formatTransparency( args.transparency )
                  }

                  //build rgba
                  _rgba = _rgb;
                  _rgba.push( _transparency );

                  var _selector = args.is_item ? args.item_id : args.module_id,
                      _styleId = _selector + '-custom-skin';
                  //Remove any dyn style set live previously for the same module or item
                  if ( false !== $( _styleId ).length ) {
                      $( '#' + _styleId ).remove();
                  }
                  $('head').append( $('<style>' , {
                      id : _styleId,
                      //html : '#' + _selector + ' .filter::before {  background:rgba(' + _rgba.join([',']) + '); }'
                      html : '#' + _selector + ' .carousel-caption-wrapper {  background:rgba(' + _rgba.join([',']) + ')!important; }'
                  }) );
              };

              //Jump to the currently edited slide, based on the input_parent_id
              //$carousel.flickity( 'select', index );
              //@return void()
              // The 'czr_input' event send a data object looking like :
              // {
              //       set_id        : module.control.id,
              //       module        : { items : $.extend( true, {}, module().items) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
              //       module_id     : module.id,//<= will allow us to target the right dom element on front end
              //       input_id      : input.id,
              //       input_parent_id : input.input_parent.id,//<= can be the mod opt or the item
              //       value         : to,
              //       isPartialRefresh : args.isPartialRefresh//<= let us know if it is a full wrapper refresh or a single input update ( true when fired from sendModuleInputsToPreview )
              // }
              var _jumpToSlide = function( data ) {
                  //bail if this is a partial refresh update. In this case all inputs are being send and we don't want to jump to the last slide
                  if ( data.isPartialRefresh )
                    return;
                  if ( _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) || _.isUndefined( data.module ) )
                    return;
                  //We assume that there's only one flickity slider in the #ha-large-header
                  var _flickEl = $('.carousel-inner', '#ha-large-header'); //$('.carousel-inner','#' + data.module_id );
                  //Destroy the flickity instance if any
                  //The flick. slider is always instanciated based on the db module id,
                  //which allows us to target it here with the customizer module_id

                  //do we have an element and has flickity been instantiated ?
                  if ( ! _flickEl.length || _.isUndefined( _flickEl.data('flickity') ) )
                    return;

                  if ( data.module && data.module.items && ! _.isEmpty( data.module.items ) ) {
                      var _index = _.findKey( data.module.items, function( _item ) {
                          return _item.id == data.input_parent_id;
                      });
                      _flickEl.flickity( 'select', _index );
                  }
              };

              var _isChecked = function( v ) {
                  return 0 !== v && '0' !== v && false !== v && 'off' !== v;
              };





          // The 'czr_input' event send a data object looking like :
          // {
          //       set_id        : module.control.id,
          //       module        : { items : $.extend( true, {}, module().items) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
          //       module_id     : module.id,//<= will allow us to target the right dom element on front end
          //       input_id      : input.id,
          //       input_parent_id : input.input_parent.id,//<= can be the mod opt or the item
          //       value         : to,
          //       isPartialRefresh : args.isPartialRefresh//<= let us know if it is a full wrapper refresh or a single input update ( true when fired from sendModuleInputsToPreview )
          // }
          inputCbExtension[ pro_header_slider_short_opt_name ] = {
                ////////////////////////////////////////////////
                /// SLIDER DESIGN OPTIONS
                skin : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) || _.isUndefined( data.value ) || _.isUndefined( data.module ) )
                        return;
                      var _model = data.module.modOpt;
                      _writeCurrentRgbaSkin( {
                          module_id     : data.module_id,
                          skin          : _model['skin'],
                          //custom_color  : _model['skin-custom-color'],
                          transparency  : _model['skin-opacity']
                      });

                      $('body' ).removeClass('header-skin-dark header-skin-light header-skin-custom').addClass('header-skin-' + data.value );
                },
                'skin-opacity' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) || _.isUndefined( data.value ) ||  _.isUndefined( data.module ) )
                        return;

                      var _model = data.module.modOpt;
                      _writeCurrentRgbaSkin( {
                          module_id     : data.module_id,
                          skin          : _model['skin'],
                          //custom_color  : _model['skin-custom-color'],
                          transparency  : _model['skin-opacity']
                      });
                },
                'skin-custom-color' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) || _.isUndefined( data.value ) ||  _.isUndefined( data.module ) )
                        return;

                      var _model = data.module.modOpt;
                      _writeCurrentRgbaSkin( {
                          module_id     : data.module_id,
                          skin          : _model['skin'],
                          //custom_color  : _model['skin-custom-color'],
                          transparency  : _model['skin-opacity']
                      });

                },
                'slider-height' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) || _.isUndefined( data.value ) )
                        return;
                      $('#ha-large-header' ).find('.pc-section-slider').css( 'height', '' );//reset height
                      var _currentStyle = $('#' + data.module_id ).attr('style');
                      _currentStyle = _.isUndefined( _currentStyle ) ? [] : _currentStyle.split();
                      _currentStyle.push( 'height:' + data.value + 'vh!important' );
                      _currentStyle = _currentStyle.join('');
                      $('#' + data.module_id ).attr( 'style', _currentStyle );
                      $('body').trigger( 'resize' );
                },
                'default-bg-color' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) || _.isUndefined( data.value ) )
                        return;
                      $('#ha-large-header' ).find('.pc-section-slider').css('background-color', data.value );
                },

                ////////////////////////////////////////////////
                /// SLIDER CONTENT OPTIONS
                'caption-vertical-pos' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! _.isString( data.value ) && ! _.isNumber( data.value ) )
                        return;
                      var _offset = parseInt( data.value, 10 );
                      _offset = Math.abs( _offset ) > 50 ? 0 : _offset;
                      _offset = 50 - _offset;
                      $('#ha-large-header' ).find('.carousel-caption').css( { top : _offset + '%'});
                },
                'fixed-title' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#' + data.module_id ).find('.fixed-caption-on .hph-title').length )
                        return;
                      if ( !  _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                      if ( ! _.isString( data.value ) )
                        return;

                      var _maxLength = data.module.modOpt['title-max-length'] || 50,
                          _text = data.value;

                      _text = data.value.length > _maxLength ? _text.substring( 0, _maxLength - 4 ) + ' ...' : _text;
                      $('#ha-large-header' ).find('.fixed-caption-on .hph-title').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'block' );
                },
                'fixed-subtitle' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#' + data.module_id ).find('.fixed-caption-on .hph-subtitle').length )
                        return;
                      if ( !  _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                      if ( ! _.isString( data.value ) )
                        return;

                      var _maxLength = data.module.modOpt['subtitle-max-length'] || 50,
                          _text = data.value;

                      _text = data.value.length > _maxLength ? _text.substring( 0, _maxLength - 4 ) + ' ...' : _text;
                      $('#ha-large-header' ).find('.fixed-caption-on .hph-subtitle').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'block' );
                },
                'fixed-cta' : function( data ) {
                      if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#' + data.module_id ).find('.fixed-caption-on .hph-cta').length )
                        return;
                      if ( !  _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                      if ( ! _.isString( data.value ) )
                        return;

                      var _text = data.value;
                      $('#ha-large-header' ).find('.fixed-caption-on .hph-cta').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'inline-block' );
                },
                // 'font-ratio' : function( data ) {
                //       if ( ! _.isObject( data ) || _.isUndefined( data.module_id ) )
                //         return;
                //       if ( ! _.isString( data.value ) && ! _.isNumber( data.value ) )
                //         return;
                //       var _ratio = parseInt( data.value, 10 );
                //       _ratio = Math.abs( _ratio ) > 50 ? 0 : _ratio;
                //       _ratio = 1 + ( Math.round( _ratio * 100.0 / 100 ) / 100 );

                //       var $title = $('#' + data.module_id ).find('.carousel-caption .hph-title'),
                //           $subtitle = $('#' + data.module_id ).find('.carousel-caption .hph-subtitle'),
                //           $cta = $('#' + data.module_id ).find('.carousel-caption .hph-cta'),
                //           _currentFontSize,
                //           _titleFontSize = 80 * _ratio,
                //           _subtitleFontSize = 30 * _ratio,
                //           _ctaFontSize = 16 * _ratio;

                //       if ( $title.length >= 1 ) {
                //           _currentFontSize = _.isString( $title.css('font-size') ) ? parseInt( $title.css('font-size').replace( 'px', '' ), 10 ) : _titleFontSize;
                //           _titleFontSize = Math.round( _currentFontSize * _ratio );
                //       }
                //       if ( $subtitle.length >= 1 ) {
                //           _currentFontSize = _.isString( $subtitle.css('font-size') ) ? parseInt( $subtitle.css('font-size').replace( 'px', '' ), 10 ) : _subtitleFontSize;
                //           _subtitleFontSize = Math.round( _currentFontSize * _ratio );
                //       }
                //       if ( $cta.length >= 1 ) {
                //           _currentFontSize = _.isString( $cta.css('font-size') ) ? parseInt( $cta.css('font-size').replace( 'px', '' ), 10 ) : _ctaFontSize;
                //           _ctaFontSize = Math.round( _currentFontSize * _ratio );
                //       }
                //       $('#' + data.module_id ).find('.carousel-caption .hph-title').css( { 'font-size' : _titleFontSize + 'px' } );
                //       $('#' + data.module_id ).find('.carousel-caption .hph-subtitle').css( { 'font-size' : _subtitleFontSize + 'px' } );
                //       $('#' + data.module_id ).find('.carousel-caption .hph-cta').css( { 'font-size' : _ctaFontSize + 'px' } );
                // },


                ////////////////////////////////////////////////
                /// SLIDE ITEMS
                'slide-title' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-title').length )
                        return;
                      if ( _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                      if ( ! _.isString( data.value ) )
                        return;
                      var _maxLength = data.module.modOpt['title-max-length'] || 50,
                          _text = data.value;

                      _text = data.value.length > _maxLength ? _text.substring( 0, _maxLength - 4 ) + ' ...' : _text;
                      $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-title').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'block' );
                },
                'slide-subtitle' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-subtitle').length )
                        return;
                      if ( _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                       if ( ! _.isString( data.value ) )
                        return;
                      var _maxLength = data.module.modOpt['subtitle-max-length'] || 50,
                          _text = data.value;

                      _text = data.value.length > _maxLength ? _text.substring( 0, _maxLength - 4 ) + ' ...' : _text;
                      $('#' + data.input_parent_id ).find('.hph-subtitle').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'block' );
                },
                'slide-cta' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-cta').length )
                        return;
                      if ( _isChecked( data.module.modOpt['fixed-content'] ) )
                        return;
                      if ( ! _.isString( data.value ) )
                        return;
                      var _text = data.value;

                      $('#' + data.input_parent_id ).find('.hph-cta').html( _text ).css('display' , _.isEmpty( _text ) ? 'none' : 'inline-block' );
                },
                'slide-link' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-cta').length )
                        return;
                      $('#' + data.input_parent_id ).find('.hph-cta').attr( 'href', data.value.url || '' );
                },
                'slide-link-target' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-cta').length )
                        return;
                      $('#' + data.input_parent_id ).find('.hph-cta').attr( 'target', _isChecked( data.value ) ? '_blank' : '' );
                },
                'slide-custom-link' : function( data ) {
                      _jumpToSlide( data );
                      if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.module_id ) )
                        return;
                      if ( ! $('#ha-large-header' ).find('#' + data.input_parent_id).find('.hph-cta').length )
                        return;
                      $('#' + data.input_parent_id ).find('.hph-cta').attr( 'href', data.value );
                }
                // 'slide-skin-color' : function( data ) {
                //       if ( ! _.isObject( data ) || _.isUndefined( data.input_parent_id ) || _.isUndefined( data.value ) || _.isUndefined( data.module ) )
                //         return;
                //       if ( ! _.has( data.module, 'items') )
                //         return;

                //       var _items = data.module.items,
                //           _model = {};
                //       if ( _.isEmpty( _items ) )
                //         return;

                //       _model = _.findWhere( _items, { id : data.input_parent_id } );

                //       if ( ! _.isUndefined( _model ) ) {
                //           _writeCurrentRgbaSkin( {
                //               is_item : true,
                //               item_id : data.input_parent_id,
                //               skin          : _model['slide-skin'],
                //               custom_color  : _model['slide-skin-color'],
                //               transparency  : _model['slide-skin-opacity']
                //           });
                //       }

                // },

          };

          //EXTEND PARENT PROPERTIES
          $.extend( api.CZR_preview.prototype, {
              //PRE SETTINGS
              pre_setting_cbs : $.extend( pre_setting_cbs, preSettingCbExtension ),

              //SETTINGS : 'setting' event sent to preview
              setting_cbs : $.extend( setting_cbs, {} ),//_.extend()

              //INPUTS : 'czr_input' event sent to preview
              input_cbs : $.extend( input_cbs, inputCbExtension )
          });


          //jump to relevant slide on item expansion
          api.bind( 'preview-ready', function() {
                var _focusOnSlide = function( params ) {
                    //the data send should look like this :
                    //{
                    //  module_id : item.module.id,
                    //  module : { items : {}, modOpt : {} },
                    //  item_id : item.id
                    //}
                    params = _.isObject( params ) ? params : {};
                    params['input_parent_id'] = params.item_id;
                    var _params = _.extend({ module_id : '', module : {}, input_parent_id : '' }, params );
                    _jumpToSlide( _params );
                };

                api.preview.bind( 'item_expanded', _focusOnSlide );
                api.preview.bind( 'slide_focus', _focusOnSlide );
          });
    }) ( wp.customize, jQuery, _);
  </script>
  <?php
}