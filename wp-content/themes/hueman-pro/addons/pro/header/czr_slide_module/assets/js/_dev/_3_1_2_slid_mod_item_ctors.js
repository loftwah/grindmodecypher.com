//extends api.CZRDynModule

var CZRSlideModuleMths = CZRSlideModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRSlideModuleMths, {
      CZRSliderItemCtor : {
              //overrides the parent ready
              ready : function() {
                    var item = this,
                        module = item.module;
                    //wait for the input collection to be populated,
                    //and then set the input visibility dependencies
                    item.inputCollection.bind( function( col ) {
                          if( _.isEmpty( col ) )
                            return;
                          try { item.setInputVisibilityDeps(); } catch( er ) {
                                api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                          }

                          //typically, hides the caption content input if user has selected a fixed content in the mod opts
                          item.setModOptDependantsVisibilities();

                          //append a notice to the default slide about how to disable the metas in single post
                          if ( item().is_default && item._isSinglePost() ) {
                              item._printPostMetasNotice();
                          }

                          //ITEM REFRESH AND FOCUS BTN
                          //1) Set initial state
                          item.container.find('.refresh-button').prop( 'disabled', true );

                          //2) listen to user actions
                          //add DOM listeners
                          api.CZR_Helpers.setupDOMListeners(
                                [     //toggle mod options
                                      {
                                            trigger   : 'click keydown',
                                            selector  : '.refresh-button',
                                            name :      'slide-refresh-preview',
                                            actions   : function( ev ) {
                                                  //var _setId = api.CZR_Helpers.getControlSettingId( module.control.id );
                                                  // if ( api.has( _setId ) ) {
                                                  //       api( _setId ).previewer.send( 'setting', [ _setId, api( _setId )() ] );
                                                  //       _.delay( function() {
                                                  //             item.container.find('.refresh-button').prop( 'disabled', true );
                                                  //       }, 250 );
                                                  // }
                                                  var _doWhenPreviewerReady = function() {
                                                        api.previewer.unbind( 'ready', _doWhenPreviewerReady );
                                                        _.delay( function() {
                                                              item.container.find('.refresh-button').prop( 'disabled', true );
                                                        }, 250 );
                                                  };
                                                  api.previewer.bind( 'ready', _doWhenPreviewerReady );
                                                  api.previewer.refresh();
                                            }
                                      },
                                      {
                                            trigger   : 'click keydown',
                                            selector  : '.focus-button',
                                            name : 'slide-focus-action',
                                            actions   : function( ev ) {
                                                  api.previewer.send( 'slide_focus', {
                                                        module_id : item.module.id,
                                                        module : { items : $.extend( true, {}, module().items ) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
                                                        item_id : item.id
                                                  });
                                            }
                                      }
                                ],//actions to execute
                                { model : item(), dom_el : item.container },//model + dom scope
                                item //instance where to look for the cb methods
                          );//api.CZR_Helpers.setupDOMListeners()
                    });//item.inputCollection.bind()

                    item.viewState.bind( function( state ) {
                          if ( 'expanded' == state ) {
                                api.previewer.send( 'item_expanded', {
                                      module_id : item.module.id,
                                      module : { items : $.extend( true, {}, module().items ) , modOpt : module.hasModOpt() ?  $.extend( true, {}, module().modOpt ): {} },
                                      item_id : item.id
                                });
                          }
                    });

                    //fire the parent
                    api.CZRItem.prototype.ready.call( item );
              },


              ////////////////////////////// SMALL HELPERS //////////////////
              ///////////////////////////////////////////////////////////////////////////
              //HELPER
              //@return bool
              _isSinglePost : function() {
                    return api.czr_wpQueryInfos && api.czr_wpQueryInfos().conditional_tags && api.czr_wpQueryInfos().conditional_tags.is_single;
              },

              //@return void()
              _printPostMetasNotice : function() {
                    var item = this;
                    //add a DOM listeners
                    api.CZR_Helpers.setupDOMListeners(
                          [     //toggle mod options
                                {
                                      trigger   : 'click keydown',
                                      selector  : '.open-post-metas-option',
                                      name      : 'toggle_mod_option',
                                      //=> open the module option and focus on the caption content tab
                                      actions   : function() {
                                            //expand the modopt panel and focus on a specific tab right after
                                            api.czr_ModOptVisible( true, { module : item.module, focus : 'section-topline-2' } );
                                      }
                                }
                          ],//actions to execute
                          { model : item(), dom_el : item.container },//model + dom scope
                          item //instance where to look for the cb methods
                    );

                    var _html_ = [
                        '<strong>',
                        huemanSlideModuleParams.i18n['You can display or hide the post metas ( categories, author, date ) in'],
                        '<a href="javascript:void(0)" class="open-post-metas-option">' + huemanSlideModuleParams.i18n['the general options'] + '</a>',
                        '</strong>'
                    ].join(' ') + '.';

                    item.czr_Input('slide-title').container.prepend( $('<p/>', { html : _html_, class : 'czr-notice' } ) );
              },


              //////////////////////////////FIXED CONTENT DEPENDENCIES //////////////////
              ///////////////////////////////////////////////////////////////////////////
              //@return void()
              //Fired when module is ready
              setModOptDependantsVisibilities : function() {
                    var item = this,
                        module = item.module,
                        _dependants = [ 'slide-title', 'slide-subtitle', 'slide-cta', 'slide-link', 'slide-custom-link', 'slide-link-target' ],
                        modOptModel = module.czr_ModOpt();

                    _.each( _dependants, function( _inpt_id ) {
                          if ( ! item.czr_Input.has( _inpt_id ) )
                            return;
                          var _input_ = item.czr_Input( _inpt_id );

                          //Fire on init
                          _input_.enabled( ! module._isChecked( modOptModel['fixed-content'] ) );
                    });

                    if ( module._isChecked( modOptModel['fixed-content'] ) ) {
                          //add a DOM listeners
                          api.CZR_Helpers.setupDOMListeners(
                                [     //toggle mod options
                                      {
                                            trigger   : 'click keydown',
                                            selector  : '.open-mod-option',
                                            name      : 'toggle_mod_option',
                                            //=> open the module option and focus on the caption content tab
                                            actions   : function() {
                                                  //expand the modopt panel and focus on a specific tab right after
                                                  api.czr_ModOptVisible( true, { module : module, focus : 'section-topline-2' } );
                                            }
                                      }
                                ],//actions to execute
                                { model : item(), dom_el : item.container },//model + dom scope
                                item //instance where to look for the cb methods
                          );

                          var _html_ = [
                              '<strong>',
                              huemanSlideModuleParams.i18n['The caption content is currently fixed and set in'],
                              '<a href="javascript:void(0)" class="open-mod-option">' + huemanSlideModuleParams.i18n['the general options'] + '</a>',
                              '</strong>'
                          ].join(' ') + '.';

                          item.czr_Input('slide-title').container.prepend( $('<p/>', { html : _html_, class : 'czr-fixed-content-notice' } ) );
                    } else {
                          var $_notice = item.container.find('.czr-fixed-content-notice');
                          if ( false !== $_notice.length ) {
                                $_notice.remove();
                          }
                    }
              },

              //@params : { before : 'slide-title' }
              toggleDisabledNotice : function( params ) {
                    var item = this;
                    params = _.extend( { before : 'slide-title' }, params );
              },
              ////////////////////////////// END OF FIXED CONTENT DEPENDENCIES //////////////////
              ///////////////////////////////////////////////////////////////////////////



              //Fired when the input collection is populated
              //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
              setInputVisibilityDeps : function() {
                    var item = this,
                        module = item.module,
                        modOptModel = module.czr_ModOpt(),
                        _isCustom = function( val ) {
                              return 'custom' == val;
                        };

                    //added january 2020 for https://github.com/presscustomizr/hueman-pro-addons/issues/199
                    item.czr_Input('slide-heading-tag').visible( ! module._isChecked( modOptModel['use-hone-title-tag-globally'] ) );

                    //Internal item dependencies
                    item.czr_Input.each( function( input ) {
                          switch( input.id ) {
                                // case 'slide-title' :
                                //       //Fire on init
                                //       item.czr_Input('slide-subtitle').visible( ! _.isEmpty( input() ) );

                                //       //React on change
                                //       input.bind( function( to ) {
                                //             item.czr_Input('slide-subtitle').visible( ! _.isEmpty( to ) );
                                //       });
                                // break;

                                case 'slide-link-title' :
                                      //Fire on init
                                      item.czr_Input('slide-link').visible( module._isChecked( input() ) || ! _.isEmpty( item.czr_Input('slide-cta')() ) );
                                      item.czr_Input('slide-link-target').visible( module._isChecked( input() ) || ! _.isEmpty( item.czr_Input('slide-cta')() ) );

                                      //React on change
                                      input.bind( function( to ) {
                                            item.czr_Input('slide-link').visible( module._isChecked( to ) || ! _.isEmpty( item.czr_Input('slide-cta')() ) );
                                            item.czr_Input('slide-link-target').visible( module._isChecked( to ) || ! _.isEmpty( item.czr_Input('slide-cta')() ) );
                                      });
                                break;

                                case 'slide-cta' :
                                      //Fire on init
                                      item.czr_Input('slide-link').visible( ! _.isEmpty( input() ) || module._isChecked( item.czr_Input('slide-link-title')() ) );
                                      item.czr_Input('slide-custom-link').visible( ! _.isEmpty( input() ) && module._isCustomLink( item.czr_Input('slide-link')() ) );
                                      item.czr_Input('slide-link-target').visible( ! _.isEmpty( input() ) || module._isChecked( item.czr_Input('slide-link-title')() ) );

                                      //React on change
                                      input.bind( function( to ) {
                                            item.czr_Input('slide-link').visible( ! _.isEmpty( to ) || module._isChecked( item.czr_Input('slide-link-title')() ) );
                                            item.czr_Input('slide-custom-link').visible( ! _.isEmpty( to ) && module._isCustomLink( item.czr_Input('slide-link')() ) );
                                            item.czr_Input('slide-link-target').visible( ! _.isEmpty( to ) || module._isChecked( item.czr_Input('slide-link-title')() ) );
                                      });
                                break;

                                //the slide-link value is an object which has always an id (post id) + other properties like title
                                case 'slide-link' :
                                      //Fire on init
                                      item.czr_Input('slide-custom-link').visible( module._isCustomLink( input() ) );
                                      //React on change
                                      input.bind( function( to ) {
                                            item.czr_Input('slide-custom-link').visible( module._isCustomLink( to ) );
                                      });
                                break;

                                // case 'slide-use-custom-skin' :
                                //       //Fire on init
                                //       item.czr_Input('slide-skin').visible( module._isChecked( input() ) );
                                //       item.czr_Input('slide-skin-color').visible( module._isChecked( input() ) && _isCustom( item.czr_Input('slide-skin')() ) );
                                //       item.czr_Input('slide-opacity').visible( module._isChecked( input() ) );
                                //       item.czr_Input('slide-text-color').visible( module._isChecked( input() ) && _isCustom( item.czr_Input('slide-skin')() ) );

                                //       //React on change
                                //       input.bind( function( to ) {
                                //             item.czr_Input('slide-skin').visible( module._isChecked( to ) );
                                //             item.czr_Input('slide-skin-color').visible( module._isChecked( to ) && _isCustom( item.czr_Input('slide-skin')() ) );
                                //             item.czr_Input('slide-opacity').visible( module._isChecked( to ) );
                                //             item.czr_Input('slide-text-color').visible( module._isChecked( to ) && _isCustom( item.czr_Input('slide-skin')() ) );
                                //       });
                                // break;

                                // case 'slide-skin' :
                                //       //Fire on init
                                //       item.czr_Input('slide-skin-color').visible( module._isChecked( 'slide-use-custom-skin' ) && _isCustom( input() ) );
                                //       item.czr_Input('slide-text-color').visible( module._isChecked( 'slide-use-custom-skin' ) && _isCustom( input() ) );

                                //       //React on change
                                //       input.bind( function( to ) {
                                //             item.czr_Input('slide-skin-color').visible( module._isChecked( 'slide-use-custom-skin' ) && _isCustom( to ) );
                                //             item.czr_Input('slide-text-color').visible( module._isChecked( 'slide-use-custom-skin' ) && _isCustom( to ) );
                                //       });
                                // break;
                          }
                    });
              },

              //overrides the default parent method by a custom one
              //at this stage, the model passed in the obj is up to date
              writeItemViewTitle : function( model, data ) {

                    var item = this,
                        index = 1,
                        module  = item.module,
                        _model = model || item(),
                        _title,
                        _slideBg,
                        _src = 'not_set',
                        _areDataSet = ! _.isUndefined( data ) && _.isObject( data );

                    //When shall we update the item title ?
                    //=> when the slide title or the thumbnail have been updated
                    //=> on module model initialized
                    if ( _areDataSet && data.input_changed && ! _.contains( ['slide-title', 'slide-background' ], data.input_changed ) )
                      return;

                    //set title with index
                    if ( ! _.isEmpty( _model.title ) ) {
                          _title = _model.title;
                    } else {
                          //find the current item index in the collection
                          var _index = _.findIndex( module.itemCollection(), function( _itm ) {
                                return _itm.id === item.id;
                          });
                          _index = _.isUndefined( _index ) ? index : _index + 1;
                          _title = [ huemanSlideModuleParams.i18n['Slide'], _index ].join( ' ' );
                    }

                    //if the slide title is set, use it
                    _title = _.isEmpty( _model['slide-title'] ) ? _title : _model['slide-title'];
                    _title = api.CZR_Helpers.truncate( _title, 15 );

                    //make sure the slide bg id is a number
                    _slideBg = ( _model['slide-background'] && _.isString( _model['slide-background'] ) ) ? parseInt( _model['slide-background'], 10 ) : _model['slide-background'];

                    // _title = [
                    //       '<div class="slide-thumb"></div>',
                    //       '<div class="slide-title">' + _title + '</div>',,
                    // ].join('');

                    var _getThumbSrc = function() {
                          return $.Deferred( function() {
                                var dfd = this;
                                //try to set the default src
                                if ( huemanSlideModuleParams && huemanSlideModuleParams.defaultThumb ) {
                                      _src = huemanSlideModuleParams.defaultThumb;
                                }
                                if ( ! _.isNumber( _slideBg ) ) {
                                      dfd.resolve( _src );
                                } else {
                                      wp.media.attachment( _slideBg ).fetch()
                                            .always( function() {
                                                  var attachment = this;
                                                  if ( _.isObject( attachment ) && _.has( attachment, 'attributes' ) && _.has( attachment.attributes, 'sizes' ) ) {
                                                        _src = this.get('sizes').thumbnail.url;
                                                        dfd.resolve( _src );
                                                  }
                                            });
                                }
                          }).promise();
                    };


                    var $slideTitleEl = $( '.' + module.control.css_attr.item_title , item.container ).find('.slide-title'),
                        $slideThumbEl = $( '.' + module.control.css_attr.item_title , item.container ).find( '.slide-thumb');

                    //TITLE
                    //always write the title
                    if ( ! $slideTitleEl.length ) {
                          //remove the default item title
                          $( '.' + module.control.css_attr.item_title , item.container ).html( '' );
                          //write the new one
                          $( '.' + module.control.css_attr.item_title , item.container ).append( $( '<div/>',
                                {
                                    class : 'slide-title',
                                    html : _title
                                }
                          ) );
                    } else {
                          $slideTitleEl.html( _title );
                    }

                    //THUMB
                    //When shall we append the item thumb ?
                    //=>IF the slide-thumb element is not set
                    //=>OR in the case where data have been provided and the input_changed is 'slide-background'
                    //=>OR if no data is provided ( we are in the initialize phase )
                    var _isBgChange = _areDataSet && data.input_changed && 'slide-background' === data.input_changed;

                    if ( 0 === $slideThumbEl.length ) {
                          _getThumbSrc().done( function( src ) {
                                if ( 'not_set' != src ) {
                                      $( '.' + module.control.css_attr.item_title, item.container ).prepend( $('<div/>',
                                            {
                                                  class : 'slide-thumb',
                                                  html : '<img src="' + src + '" width="32" height="32" alt="' + _title + '" />'
                                            }
                                      ));
                                }
                          });
                    } else if ( _isBgChange || ! _areDataSet ) {
                          _getThumbSrc().done( function( src ) {
                                if ( 'not_set' != src ) {
                                      $slideThumbEl.html( '<img src="' + src + '" width="32" height="32" alt="' + _title + '" />' );
                                }
                          });
                    }
              }
      }//CZRSliderItemCtor
});//extend
})( wp.customize , jQuery, _ );