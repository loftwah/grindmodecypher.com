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
})( wp.customize , jQuery, _ );