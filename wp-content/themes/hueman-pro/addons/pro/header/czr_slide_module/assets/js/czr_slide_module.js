//extends api.CZRDynModule

var CZRSlideModuleMths = CZRSlideModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRSlideModuleMths, {
      initialize: function( id, constructorOptions ) {
            var module = this;

            module.initialConstrucOptions = $.extend( true, {}, constructorOptions );//detach from the original obj

            this.sliderSkins = huemanSlideModuleParams.sliderSkins;//light, dark

            //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUTS
            module.inputConstructor = api.CZRInput.extend( module.CZRSliderItemInputCtor || {} );
            module.inputModOptConstructor = api.CZRInput.extend( module.CZRSliderModOptInputCtor || {} );

            //SET THE CONTENT PICKER OPTIONS
            $.extend( module.inputOptions, {
                  'content_picker' : {
                        post : '',//['page'],<= all post types
                        taxonomy : ''//'_none_'//<= all taxonomy types
                  }
            });

            //EXTEND THE DEFAULT CONSTRUCTORS FOR ITEMS AND MODOPTS
            module.itemConstructor = api.CZRItem.extend( module.CZRSliderItemCtor || {} );
            module.modOptConstructor = api.CZRModOpt.extend( module.CZRSliderModOptCtor || {} );

             //run the parent initialize
            api.CZRDynModule.prototype.initialize.call( module, id, constructorOptions );

            //extend the module with new template Selectors
            $.extend( module, {
                  itemPreAddEl : 'czr-module-slide-pre-item-input-list',
                  itemInputList : 'czr-module-slide-item-input-list',
                  modOptInputList : 'czr-module-slide-mod-opt-input-list'
            } );


            //declares a default ModOpt model
            //this.defaultModOptModel = {
            //     is_mod_opt : true,
            //     module_id : module.id,
            //     'slider-speed' : 6,
            //     'lazyload' : 1,
            //     'slider-height' : 100
            // };
            var _clonedDefaultModOpt = $.extend( true, {}, huemanSlideModuleParams.defaultModOpt );
            this.defaultModOptModel = _.extend(
                  _clonedDefaultModOpt,
                  {
                        module_id : module.id
                  }
            );

            //DEFINE THE MODULE SKOPE CONSTANT.
            //can be 'local' or 'group'
            module.SKOPE_LEVEL = constructorOptions.control.params.skopeLevel;
            module.SKOPE_ID = constructorOptions.control.params.skopeId;

            //declares a default Item model
            // this.defaultItemModel = {
            //     id : '',
            //     title : '',
            //     'slide-background' : '',
            //     'slide-title'      : '',
            //     'slide-subtitle'   : '',
            //     'slide-cta'         : '',
            //     'slide-link'       : '',
            //     'slide-custom-link'  : ''
            // };
            //The server model includes the slide-src property that is created when rendering the slide in the front tmpl
            this.defaultItemModel = _.omit( huemanSlideModuleParams.defaultSlideMod, 'slide-src');

            //overrides the default success message
            this.itemAddedMessage = huemanSlideModuleParams.i18n['New Slide created ! Scroll down to edit it.'];

            // module.czr_wpQueryInfos = api.czr_wpQueryInfos();
            // if ( 'resolved' == api.czr_wpQueryDataReady.state() ) {
            //     module.czr_wpQueryInfos( api.czr_wpQueryInfos() );
            // } else {
            //     api.czr_wpQueryDataReady.done( function() {
            //           module.czr_wpQueryInfos( api.czr_wpQueryInfos() );
            //     });
            // }
            module.isReady.then( function() {
                  // var _refreshModuleModel = function( query_data ) {
                  //       var _setId = api.CZR_Helpers.getControlSettingId( module.control.id );
                  //       //module.refreshItemCollection();

                  //       console.log('in refresh module model', query_data, module.control.id,  api.control.has( module.control.id ) );

                  //       //initialize
                  //       //Wait for the control to be registered when switching skope
                  //       api.control( module.control.id, function() {
                  //             // module.initializeModuleModel( module.initialConstrucOptions, query_data )
                  //             //       .done( function( newModuleValue ) {
                  //             //             module.set( newModuleValue, { silent : true } );
                  //             //             module.refreshItemCollection();
                  //             //       })
                  //             //       .always( function( newModuleValue ) {

                  //             //       });
                  //       });

                  //       // unbind to avoid firing this for control already unregistered when navigating through several context in the preview
                  //       api.czr_wpQueryInfos.unbind( _refreshModuleModel );
                  // };

                  //Fired on module ready and skope ready ( even when skope is deactivated )
                  var _toggleModuleItemVisibility = function() {
                        var $preItemBtn = $('.' + module.control.css_attr.open_pre_add_btn, module.container ),
                            $preItemWrapper = $('.' + module.control.css_attr.pre_add_wrapper, module.container),
                            _isLocal = 'local' == module.SKOPE_LEVEL;


                        //HIDE THE ITEM CREATION WHEN NOT LOCAL
                        $preItemBtn.toggle( _isLocal );
                        $preItemWrapper.toggle( _isLocal );
                        module.itemsWrapper.toggle( _isLocal );

                        //DISPLAY A NOTICE WHEN NOT LOCAL
                        if ( ! _isLocal ) {
                              var _localSkopeId = api.czr_activeSkopes()[ 'local' ];
                              if ( ! module.control.container.find( '.slide-mod-skope-notice').length ) {
                                    module.control.container.append( $( '<div/>', {
                                              class: 'slide-mod-skope-notice',
                                              html : [
                                                    huemanSlideModuleParams.i18n['You can set the global options of the slider here by clicking on the gear icon : height, font size, effects...'],
                                                    huemanSlideModuleParams.i18n['Those settings will be inherited by the more specific options levels.']
                                              ].join( ' ' )
                                        })
                                    );
                              } else {
                                  module.control.container.find( '.slide-mod-skope-notice').show();
                              }
                        } else {
                              if ( 1 == module.control.container.find( '.slide-mod-skope-notice').length )
                                module.control.container.find( '.slide-mod-skope-notice').remove();
                        }

                  };

                  //Refresh the module default item based on the query infos if the associated setting has no value yet
                  //api.czr_wpQueryInfos.bind( _refreshModuleModel );

                  // api.czr_wpQueryDataReady.then( function( data ) {
                  //       console.log('api.czr_wpQueryDataReady.then()', data );
                  //       //data = api.czr_wpQueryInfos() || data;//always get the latest query infos
                  //       api.czr_wpQueryInfos.bind( _refreshModuleModel );
                  // });


                  //ACTIONS ON SKOPE READY
                  //1) Hide items and pre-items if skope is not local
                  //2) set the item and modopt refresh button state, and set their state according to the module changes
                  //ITEMS AND PRE ITEMS
                  _.delay( function() {
                        _toggleModuleItemVisibility();
                  }, 200 );

                  //UPDATE REFRESH BUTTONS STATE ON MODULE CHANGES
                  module.callbacks.add( function( to, from ) {
                        module.czr_Item.each( function( _itm_ ){
                              if ( 'expanded' != _itm_.viewState() )
                                return;
                              if ( 1 == _itm_.container.find('.refresh-button').length ) {
                                    _itm_.container.find('.refresh-button').prop( 'disabled', false );
                              }
                        });
                        if ( module.czr_ModOpt && module.czr_ModOpt.isReady ) {
                              module.czr_ModOpt.isReady.then( function() {
                                    if ( api.czr_ModOptVisible() ) {
                                          if ( 1 == module.czr_ModOpt.container.find('.refresh-button').length ) {
                                                module.czr_ModOpt.container.find('.refresh-button').prop( 'disabled', false );
                                          }
                                    }
                              });
                        }
                  });
            });//module.isReady

            //REFRESH ITEM TITLES
            var _refreshItemsTitles = function() {
                  module.czr_Item.each( function( _itm_ ){
                        _itm_.writeItemViewTitle();
                  });
            };
            //Always write the title on :
            //- module model initialized => typically when the query data has been set and is used to set a default item
            //- item collection sorted
            //- on item removed
            //module.bind( 'module-model-initialized', _refreshItemsTitles );
            module.bind( 'item-collection-sorted', _refreshItemsTitles );
            module.bind( 'item-removed', _refreshItemsTitles );


            //fired ready :
            //1) on section expansion
            //2) or in the case of a module embedded in a regular control, if the module section is alreay opened => typically when skope is enabled
            if ( _.has( api, 'czr_activeSectionId' ) && module.control.section() == api.czr_activeSectionId() && 'resolved' != module.isReady.state() ) {
                  module.ready();
            }

            api.section( module.control.section(), function( _section_ ) {
                  _section_.expanded.bind(function(to) {
                        if ( 'resolved' == module.isReady.state() )
                          return;
                        module.ready();
                  });
            });
      },//initialize

















      //Overrides the default method.
      // Fired on module.isReady.done()
      // Fired on api.czr_wpQueryInfos changes
      // => this method is always fired by the parent constructor

      //The job of this pre-processing method is to create a contextual item based on what the server send with 'czr-query-data-ready'
      //This method is fired in the initialize module method
      //and then on each query_data update, if the associated setting has not been set yet, it is fired to get the default contextual item
      //1) image : if post / page, the featured image
      //2) title : several cases @see : hu_set_hph_title()
      //3) subtitle : no subtitle except for home page : the site tagline
      initializeModuleModel : function( constructorOptions, new_data ) {
            var module = this,
                dfd = $.Deferred();

            var _setId = api.CZR_Helpers.getControlSettingId( module.control.id );

            //bail if the setting id is not registered
            if ( ! api.has( _setId ) )
              return dfd.resolve( constructorOptions ).promise();

            // console.log('api.control.has( module.control.id ); ', api.control.has( module.control.id ) );
            // console.log('module.initialConstrucOptions', module.initialConstrucOptions );
            // console.log('api( _setId )()', _setId, api( _setId )());
            //Bail if the skope is not local
            //Make sure to reset the items to [] if the current item is_default
            // if ( api.czr_skope.has( api.czr_activeSkopeId() ) ) {
            //     console.log( 'SKOPE ?', api.czr_activeSkopeId(), api.czr_skope( api.czr_activeSkopeId() )().skope );
            //     console.log( api.czr_isSkopOn() );
            // }


            // If we are local and the setting has been customized ( db values set on registration )
            // then let's pass the untouched constructor options, which includes all the slides and mod opt
            //
            // If we are not local, let's make sure we have not items.
            //
            // If we are local and not yet customized, let's set a default item based on the contextual post thumbnail

            //IF LOCAL
            //If inheriting from a parent, then let's set the default item
            //if setting is dirty in local skope, let's return the ctor options.
            var _isLocal = 'local' == module.SKOPE_LEVEL,
                _isDirty = ( true === api( _setId ).dirty ) || ( ! _.isEmpty( api.dirtyValues()[ _setId ] ) );

            if ( _isLocal && _isDirty ) {
                  return dfd.resolve( constructorOptions ).promise();
            } else if ( ! _isLocal ) {
                  var _newCtorOptions = $.extend( true, {}, constructorOptions );
                  _newCtorOptions.items = [];
                  return dfd.resolve( _newCtorOptions ).promise();
            } else {
                  //If the setting is not set, then we can set the default item based on the query data
                  // if ( ! _.isEmpty( constructorOptions.items ) )
                  //   return dfd.resolve( constructorOptions ).promise();
                  //Always get the query data from the freshest source
                  api.czr_wpQueryDataReady.then( function( data ) {
                        data = api.czr_wpQueryInfos() || data;//always get the latest query infos
                        var _query_data, _default;
                        if ( _.isUndefined( new_data ) ) {
                              _query_data = _.isObject( data ) ? data.query_data : {};
                        } else {
                              _query_data = _.isObject( new_data ) ? new_data.query_data : {};
                        }

                        _default = $.extend( true, {}, module.defaultItemModel );
                        constructorOptions.items = [
                              $.extend( _default, {
                                    'id' : 'default_item_' + module.id,
                                    'is_default' : true,
                                    'slide-background' : ( ! _.isEmpty( _query_data.post_thumbnail_id ) ) ? _query_data.post_thumbnail_id : '',
                                    'slide-title' : ! _.isEmpty( _query_data.post_title )? _query_data.post_title : '',
                                    'slide-subtitle' : ! _.isEmpty( _query_data.subtitle ) ? _query_data.subtitle : ''
                              })
                        ];
                        dfd.resolve( constructorOptions );
                  });
            }

            // //Make sure this is resolved, even when the control is not registered for some reasons
            // _.delay( function() {
            //       if ( ! api.control.has( module.control.id ) ) {
            //             api.errare( 'Slide Module : initializeModuleModel, the control has not been registered after too long.', module.control.id );
            //             dfd.resolve( constructorOptions );
            //       }
            // }, 5000 );
            return dfd.promise();
      },
















      ///////////////////////////////////////////////////////////////////
      /// MODULE SPECIFIC INPUTS METHOD USED FOR BOTH ITEMS AND MOD OPTS
      //////////////////////////////////////////
      //this is an item or a modOpt
      slideModSetupSelect : function() {
            if ( 'skin' != this.id && 'slide-skin' != this.id )
              return;

            var input      = this,
                input_parent  = input.input_parent,
                module     = input.module,
                _sliderSkins  = module.sliderSkins,//{}
                _model = input_parent();

            //generates the options
            _.each( _sliderSkins , function( _layout_name , _k ) {
                  var _attributes = {
                            value : _k,
                            html: _layout_name
                      };
                  if ( _k == _model[ input.id ] ) {
                        $.extend( _attributes, { selected : "selected" } );
                  }
                  $( 'select[data-czrtype="' + input.id + '"]', input.container ).append( $('<option>', _attributes) );
            });
            $( 'select[data-czrtype="' + input.id + '"]', input.container ).selecter();
      },


      //Save color as rgb
      //this can be an item or a mod opt
      slideModSetupColorPicker : function() {
          // var input  = this,
          //     input_parent = input.input_parent,
          //     _model = input_parent();

          // input.container.find('input').iris( {
          //       palettes: true,
          //       hide:false,
          //       change : function( e, o ) {
          //             //if the input val is not updated here, it's not detected right away.
          //             //weird
          //             //is there a "change complete" kind of event for iris ?
          //             //$(this).val($(this).wpColorPicker('color'));
          //             //input.container.find('[data-czrtype]').trigger('colorpickerchange');

          //             var _rgb = api.CZR_Helpers.hexToRgb( o.color.toString() ),
          //                 _isCorrectRgb = _.isString( _rgb ) && -1 !== _rgb.indexOf('rgb(');

          //             if ( ! _isCorrectRgb )
          //               _rgb = "rgb(34,34,34)";//force to dark skin if incorrect

          //             //synchronizes with the original input
          //             $(this).val( _rgb ).trigger('colorpickerchange').trigger('change');
          //       }
          // });

          var input  = this;
          input.container.find('input').wpColorPicker({
              palettes: true,
              //hide:false,
              width: window.innerWidth >= 1440 ? 271 : 251,
              change : function( e, o ) {
                    // var _rgb = api.CZR_Helpers.hexToRgb( o.color.toString() ),
                    //     _isCorrectRgb = _.isString( _rgb ) && -1 !== _rgb.indexOf('rgb(');

                    // if ( ! _isCorrectRgb ) {
                    //       _rgb = "rgb(34,34,34)";//force to dark skin if incorrect
                    // }
                    $(this).val( o.color.toString() ).trigger('colorpickerchange').trigger('change');
              },
              clear : function( e, o ) {
                    //$(this).val('').trigger('colorpickerchange').trigger('change');
                    input('');
              }
          });
      },
















      //////////////////////////////////////////
      /// MODULE HELPERS
      //the slide-link value is an object which has always an id (post id) + other properties like title
      _isCustomLink : function( input_val ) {
            return _.isObject( input_val ) && '_custom_' === input_val.id;
      },

      _isChecked : function( v ) {
            return 0 !== v && '0' !== v && false !== v && 'off' !== v;
      }
});//extend
})( wp.customize , jQuery, _ );//extends api.CZRDynModule

var CZRSlideModuleMths = CZRSlideModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRSlideModuleMths, {

      ///////////////////////////////////////////////////////////
      /// INPUT CONSTRUCTORS
      //////////////////////////////////////////
      CZRSliderItemInputCtor : {
            ready : function() {
                  var input = this;
                  //update the item title on slide-title change
                  if ( 'slide-title' === input.id ) {
                        input.bind( function( to ) {
                              input.updateItemTitle( to );
                        });
                  }

                  //add the custom link option to the content picker
                  if ( 'slide-link' == input.id ) {
                        input.defaultContentPickerOption = [{
                              id          : '_custom_',
                              title       : [ '<span style="font-weight:bold">' , huemanSlideModuleParams.i18n['Set a custom url'], '</span>' ].join(''),
                              type_label  : '',
                              object_type : '',
                              url         : ''
                        }];
                  }

                  api.CZRInput.prototype.ready.call( input);
            },

            //overrides the default method
            setupSelect : function() {
                  return this.module.slideModSetupSelect.call( this );
            },

            //Save color as rgb
            setupColorPicker : function() {
                  return this.module.slideModSetupColorPicker.call( this );
            },

            //ACTIONS ON czr_input('slide-title') change
            //Don't fire in pre item case
            //@return void
            updateItemTitle : function( _new_title ) {
                  var input = this,
                      item = input.input_parent,
                      is_preItemInput = _.has( input, 'is_preItemInput' ) && input.is_preItemInput,
                      _new_model  = $.extend( true, {}, item() );
                  // if ( is_preItemInput )
                  //   return;
                  $.extend( _new_model, { title : _new_title } );

                  //This is listened to by module.czr_Item( item.id ).itemReact
                  //the object passed is needed to avoid a refresh
                  item.set(
                        _new_model,
                        {
                              input_changed     : 'title',
                              input_transport   : 'postMessage',
                              not_preview_sent  : true//<= this parameter set to true will prevent the setting to be sent to the preview ( @see api.Setting.prototype.preview override ). This is useful to decide if a specific input should refresh or not the preview.} );
                        }
                  );
            }
      },//CZRSlidersInputMths



      CZRSliderModOptInputCtor : {
            ready : function() {
                  var input = this;
                  //add the custom link option to the content picker
                  if ( 'fixed-link' == input.id ) {
                        input.defaultContentPickerOption = [{
                              id          : '_custom_',
                              title       : [ '<span style="font-weight:bold">' , huemanSlideModuleParams.i18n['Set a custom url'], '</span>' ].join(''),
                              type_label  : '',
                              object_type : '',
                              url         : ''
                        }];
                  }

                  api.CZRInput.prototype.ready.call( input);
            },

            //overrides the default method
            setupSelect : function() {
                  return this.module.slideModSetupSelect.call( this );
            },

            //Save color as rgb
            setupColorPicker : function() {
                  return this.module.slideModSetupColorPicker.call( this );
            },
      }//CZRSliderItemInputCtor
});//extend
})( wp.customize , jQuery, _ );//extends api.CZRDynModule

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
})( wp.customize , jQuery, _ );//extends api.CZRDynModule

var CZRSlideModuleMths = CZRSlideModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRSlideModuleMths, {
      CZRSliderModOptCtor : {
            ready: function() {
                  var modOpt = this,
                      module = modOpt.module;
                  //wait for the input collection to be populated, and then set the input visibility dependencies
                  modOpt.inputCollection.bind( function( col ) {
                        if( _.isEmpty( col ) )
                          return;
                        try { modOpt.setModOptInputVisibilityDeps(); } catch( er ) {
                              api.errorLog( 'setModOptInputVisibilityDeps : ' + er );
                        }

                        //MOD OPT REFRESH BTN
                        //1) Set initial state
                        modOpt.container.find('.refresh-button').prop( 'disabled', true );
                        //2) listen to user actions
                        //add DOM listeners
                        api.CZR_Helpers.setupDOMListeners(
                              [     //toggle mod options
                                    {
                                          trigger   : 'click keydown',
                                          selector  : '.refresh-button',
                                          name : 'slide-refresh-preview-from-mod-opt',
                                          actions   : function( ev ) {
                                                // var _setId = api.CZR_Helpers.getControlSettingId( module.control.id );
                                                // if ( api.has( _setId ) ) {
                                                //       api( _setId ).previewer.send( 'setting', [ _setId, api( _setId )() ] );
                                                //       _.delay( function() {
                                                //             modOpt.container.find('.refresh-button').prop( 'disabled', true );
                                                //       }, 250 );
                                                // }
                                                var _doWhenPreviewerReady = function() {
                                                      api.previewer.unbind( 'ready', _doWhenPreviewerReady );
                                                      _.delay( function() {
                                                            modOpt.container.find('.refresh-button').prop( 'disabled', true );
                                                      }, 250 );
                                                };
                                                api.previewer.bind( 'ready', _doWhenPreviewerReady );
                                                api.previewer.refresh();
                                          }
                                    }
                              ],//actions to execute
                              { model : modOpt(), dom_el : modOpt.container },//model + dom scope
                              modOpt //instance where to look for the cb methods
                        );//api.CZR_Helpers.setupDOMListeners()
                  });//modOpt.inputCollection()

                  //fire the parent
                  api.CZRModOpt.prototype.ready.call( modOpt );
            },


            //Fired when the input collection is populated
            //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
            setModOptInputVisibilityDeps : function() {
                  var modOpt = this,
                      module = modOpt.module,
                      _isFixedContentOn = function() {
                            return module._isChecked( modOpt.czr_Input('fixed-content')() );
                      };

                  modOpt.czr_Input.each( function( input ) {
                        switch( input.id ) {
                              //DESIGN
                              // case 'skin' :
                              //       var _isCustom = function( val ) {
                              //             return 'custom' == val;
                              //       };

                              //       //Fire on init
                              //       modOpt.czr_Input('skin-custom-color').visible( _isCustom( input() ) );
                              //       modOpt.czr_Input('text-custom-color').visible( _isCustom( input() ) );

                              //       //React on change
                              //       input.bind( function( to ) {
                              //             modOpt.czr_Input('skin-custom-color').visible( _isCustom( to ) );
                              //             modOpt.czr_Input('text-custom-color').visible( _isCustom( to ) );
                              //       });
                              // break;

                              //CONTENT
                              case 'fixed-content' :
                                    var _modOptsDependants = [ 'fixed-title', 'fixed-subtitle', 'fixed-cta', 'fixed-link', 'fixed-link-target', 'fixed-custom-link' ],
                                        _setVisibility = function( _depId, _inputVal ) {
                                              var _bool_;
                                              switch( _depId ) {
                                                    case 'fixed-title' :
                                                    case 'fixed-subtitle' :
                                                    case 'fixed-cta' :
                                                          _bool_ = module._isChecked( _inputVal );
                                                    break;

                                                    case 'fixed-link' :
                                                    case 'fixed-link-target' :
                                                          _bool_ = module._isChecked( _inputVal ) && ! _.isEmpty( modOpt.czr_Input('fixed-cta')() );
                                                    break;

                                                    case 'fixed-custom-link' :
                                                          _bool_ = module._isChecked( _inputVal ) && ! _.isEmpty( modOpt.czr_Input('fixed-cta')() ) && module._isCustomLink( modOpt.czr_Input('fixed-link')() );
                                                    break;
                                              }

                                              modOpt.czr_Input( _depId ).visible( _bool_ );
                                        };

                                    //MOD OPTS
                                    _.each( _modOptsDependants, function( _inpt_id ) {
                                          //Fire on init
                                          _setVisibility( _inpt_id, input() );
                                    });

                                    //React on change
                                    input.bind( function( to ) {
                                          _.each( _modOptsDependants, function( _inpt_id ) {
                                               _setVisibility( _inpt_id, to );
                                          });
                                    });
                              break;
                              case 'fixed-cta' :
                                      //Fire on init
                                      modOpt.czr_Input('fixed-link').visible(
                                            ! _.isEmpty( input() ) &&
                                            _isFixedContentOn()
                                      );
                                      modOpt.czr_Input('fixed-custom-link').visible(
                                            ! _.isEmpty( input() ) &&
                                            module._isCustomLink( modOpt.czr_Input('fixed-link')() ) &&
                                            _isFixedContentOn()
                                      );
                                      modOpt.czr_Input('fixed-link-target').visible(
                                            ! _.isEmpty( input() ) &&
                                            _isFixedContentOn()
                                      );

                                      //React on change
                                      input.bind( function( to ) {
                                            modOpt.czr_Input('fixed-link').visible(
                                                  ! _.isEmpty( to ) &&
                                                  _isFixedContentOn()
                                            );
                                            modOpt.czr_Input('fixed-custom-link').visible(
                                                  ! _.isEmpty( to ) &&
                                                  module._isCustomLink( modOpt.czr_Input('fixed-link')() ) &&
                                                  _isFixedContentOn()
                                            );
                                            modOpt.czr_Input('fixed-link-target').visible(
                                                  ! _.isEmpty( to ) &&
                                                  _isFixedContentOn()
                                            );
                                      });
                                break;

                                //the slide-link value is an object which has always an id (post id) + other properties like title
                                case 'fixed-link' :
                                      //Fire on init
                                      modOpt.czr_Input('fixed-custom-link').visible( module._isCustomLink( input() ) && _isFixedContentOn() );
                                      //React on change
                                      input.bind( function( to ) {
                                            modOpt.czr_Input('fixed-custom-link').visible( module._isCustomLink( to ) && _isFixedContentOn() );
                                      });
                                break;

                              //EFFECTS AND PERFORMANCES
                              case 'autoplay' :
                                    //Fire on init
                                    modOpt.czr_Input('slider-speed').visible( module._isChecked( input() ) );
                                    modOpt.czr_Input('pause-on-hover').visible( module._isChecked( input() ) );

                                    //React on change
                                    input.bind( function( to ) {
                                          modOpt.czr_Input('slider-speed').visible( module._isChecked( to ) );
                                          modOpt.czr_Input('pause-on-hover').visible( module._isChecked( to ) );
                                    });
                              break;
                              case 'parallax' :
                                    //Fire on init
                                    modOpt.czr_Input('parallax-speed').visible( module._isChecked( input() ) );

                                    //React on change
                                    input.bind( function( to ) {
                                          modOpt.czr_Input('parallax-speed').visible( module._isChecked( to ) );
                                    });
                              break;
                              case 'post-metas' :
                                    var _dts = [ 'display-cats', 'display-comments', 'display-auth-date' ],
                                        _setVis = function( _depId, _inputVal ) {
                                              modOpt.czr_Input( _depId ).visible( module._isChecked( _inputVal ) );
                                        };

                                    //MOD OPTS
                                    _.each( _dts, function( _inpt_id ) {
                                          //Fire on init
                                          _setVis( _inpt_id, input() );
                                    });

                                    //React on change
                                    input.bind( function( to ) {
                                          _.each( _dts, function( _inpt_id ) {
                                                _setVis( _inpt_id, to );
                                          });
                                    });
                              break;

                        }
                  });
            },
      }//CZRSliderModOptCtor
});//extend
})( wp.customize , jQuery, _ );//extends api.CZRDynModule

var CZRSlideModuleMths = CZRSlideModuleMths || {};
( function ( api, $, _ ) {
      //provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_slide_module : {
                  mthds : CZRSlideModuleMths,
                  crud : true,
                  multi_item : true,
                  name : 'Slider',
                  has_mod_opt : true,
                  ready_on_section_expanded : false,//will be fired in the module::initialize()
                  //defaultItemModel : {}
            }
      });
})( wp.customize , jQuery, _ );