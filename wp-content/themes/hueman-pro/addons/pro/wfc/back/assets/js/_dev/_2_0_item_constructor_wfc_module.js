
var CZRWFCModuleMths = CZRWFCModuleMths || {};

( function ( api, $, _ ) {
      $.extend( CZRWFCModuleMths, {
            // updateItemOrPreItemModel : function( item_instance, is_preItem ) {
            //       var item = item_instance;
            //       is_preItem = is_preItem || false;

            //       //check if we are in the pre Item case => if so, the social-icon might be empty
            //       if ( ! _.has( item(), 'social-icon') || _.isEmpty( item()['social-icon'] ) )
            //         return;

            //       var _new_model, _new_title, _new_color;

            //       _new_model  = $.extend( true, {}, item() );//always safer to deep clone ( alternative to _.clone() ) => we don't know how nested this object might be in the future
            //       _new_title  = this.getTitleFromIcon( _new_model['social-icon'] );
            //       _new_color  = serverControlParams.social_el_params.defaultSocialColor;
            //       if ( ! is_preItem && item.czr_Input.has( 'social-color' ) )
            //         _new_color = item.czr_Input('social-color')();

            //       //add text follow us... to the title
            //       _new_title = [ serverControlParams.i18n.followUs, _new_title].join(' ');

            //       if ( is_preItem ) {
            //             _new_model = $.extend( _new_model, { title : _new_title, 'social-color' : _new_color } );
            //             item.set( _new_model );
            //       } else {
            //             item.czr_Input('title').set( _new_title );
            //             //item.czr_Input('social-link').set( '' );
            //             if ( item.czr_Input('social-color') ) { //optional
            //               item.czr_Input('social-color').set( _new_color );
            //             }
            //       }
            // },

            //////////////////////////////////////////////////////////
            /// ITEM CONSTRUCTOR
            //////////////////////////////////////////
            CZRWFCItemCtor : {
                  //Fired if the item has been instantiated
                  //The item.callbacks are declared.
                  ready : function() {
                        var item = this;

                        api.CZRItem.prototype.ready.call( item );

                        // Do some checks
                        // Send the item() to the preview
                        item.callbacks.add( function( to ) {
                              var sectionId = item.module.control.section();

                              try {
                                    // Before anything else, make sure that the provided selector can be parsed by $
                                    $( item().selector );

                                    api.previewer.send( 'wfc_joy', {
                                        id : item.id,
                                        data : to
                                    });
                              } catch ( er ) {
                                    if ( api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                                          api.section( sectionId ).notifications.add( new api.Notification( to.id + '_invalid_selector', {
                                                type: 'info',
                                                message: [ to.title, '=>', TCFontAdmin.Translations['Make sure to use valid css selectors.'] ].join(' '),
                                                dismissible: true
                                          } ) );

                                          // Removed if not dismissed after 5 seconds
                                          // _.delay( function() {
                                          //       api.section( sectionId ).notifications.remove( 'invalid_selector' );
                                          // }, 4000 );
                                    }
                                    return;
                              }

                              // Display a notification if the selector is empty
                              if ( _.isEmpty( to.selector ) && api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                                    api.section( sectionId ).notifications.add( new api.Notification( to.id + '_invalid_selector', {
                                          type: 'info',
                                          message: [ to.title, '=>', TCFontAdmin.Translations['Make sure to use valid css selectors.'] ].join(' '),
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    // _.delay( function() {
                                    //       api.section( sectionId ).notifications.remove( 'invalid_selector' );
                                    // }, 4000 );
                                    return;
                              }

                              // removes a potential remaining notification
                              if ( api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                                    api.section( sectionId ).notifications.remove( to.id + '_invalid_selector' );
                              }
                        });


                        // update the item title on font-family change
                        // on item ready, the input are not yet instantiated.
                        // That's why we need to schedule it with the add()
                        // The add() also alows us to re-bound the input after it's been removed
                        item.czr_Input.bind( 'add', function( _input_ ) {
                              switch( _input_.id ) {
                                    case 'font-family' :
                                          _input_.bind( function( to ) {
                                                item.writeItemViewTitle();

                                                //style the czrSelect2 selected option with the new font
                                                $( '.czrSelect2-selection__rendered', _input_.container ).css( _input_.module.getInlineFontStyle( to ) );
                                          });
                                    break;

                                    case 'subset' :
                                          // Update the list of font-family on subset change
                                          // on item ready, the input are not yet instantiated. That's why we need to schedule it with the when()
                                          _input_.bind( function( newSubset ) {
                                                // if ( -1 != item.czr_Input( 'font-family' )().indexOf('[gfont]') )
                                                //   item.subsetChangeReact( newSubset );
                                                item.subsetChangeReact( newSubset );
                                          });
                                    break;

                                    case 'static-effect' :
                                          var staticEffectsCollection = TCFontAdmin.selectOptionLists['static-effect'];
                                          _input_.bind( function( to ) {
                                                item.writeItemViewTitle();
                                                var _newColor;
                                                //set the color to the recommend one
                                                if ( _.has( staticEffectsCollection, to ) && staticEffectsCollection[ to ][2] && _.isString( staticEffectsCollection[ to ][2] ) ) {
                                                      // is it an hex color ?
                                                      _newColor = -1 == staticEffectsCollection[ to ][2].indexOf('#') ? "#000000" : staticEffectsCollection[ to ][2];
                                                } else {
                                                      _newColor = "#000000";
                                                }
                                                item.czr_Input('color')( _newColor );
                                                item.czr_Input('color').container.find('input').trigger('change');
                                          });
                                    break;

                                    case 'selector' :
                                          _input_.bind( function() {
                                                item.writeItemViewTitle();
                                          });
                                    break;

                                    // When the 'customized' input has been added, listen to any item changes and populates the "customized" property list
                                    case 'customized' :
                                          item.callbacks.add( function( to, from, data ) {
                                                if ( 'customized' == data.input_changed )
                                                      return;

                                                //update the customized collection
                                                var _customized = $.extend( true, [], item.czr_Input('customized')() ) ;

                                                //make sure it's an array and push the customized property
                                                _customized = _.isArray( _customized ) ? _customized : [];
                                                if ( data.input_changed && ! _.contains( _customized, data.input_changed  ) ) {
                                                      _customized.push( data.input_changed );
                                                }

                                                item.czr_Input( 'customized' )( _customized );
                                          });
                                    break;
                              }//switch()
                        });//item.czr_Input.bind()


                        //replace the default remove dialob title
                        item.bind( 'remove-dialog-rendered', function() {
                              $( '.' + item.module.control.css_attr.remove_alert_wrapper, item.container )
                                    .find('p')
                                    .html( TCFontAdmin.Translations[ 'Please confirm the removal of the customizations for' ] + ' : ' + '<i>' + item().title + '</i>' );
                        });

                        // on item expansion, ask the preview if the selector exists
                        item.viewState.callbacks.add( function( state ) {
                              if ( 'closed' == state )
                                return;

                              // The preview replies with 'wfc-missing-selector' if relevant
                              // => this message is listened too by the module @see module::initialize() method
                              api.previewer.send( 'wfc_check_if_selector_exists', {
                                    id : item.id,
                                    data : item()
                              });
                        });
                  },//ready()


                  // Override the core method
                  // Validate the not yet customized properties of the item model candidate on initialization
                  // Use the style defined in the json.
                  //
                  // Uses the theme json for "selector"
                  // uses the default model ( defined in wordpress-font-customizer.php ) for all other properties
                  validateItemModelOnInitialize : function( item_model_candidate ) {
                        if ( ! _.isObject( item_model_candidate ) || _.isEmpty( item_model_candidate ) ){
                              throw new Error('ItemCtor::validateItemModelOnInitialize : model candidate is not properly formed.');
                        }

                        // do we have default settings ?
                        // yes If this is not a custom selector.
                        var defaultItemSettings = [];
                        if ( item_model_candidate.id && _.isObject( TCFontAdmin.DefaultSettings ) ) {
                              //Return the default Item settings as a single entry array if match found
                              defaultItemSettings = _.filter( TCFontAdmin.DefaultSettings, function( data, key ) { return item_model_candidate.id == key; } );
                              if ( _.isArray( defaultItemSettings ) && ! _.isEmpty( defaultItemSettings ) ) {
                                    defaultItemSettings = defaultItemSettings[0];
                              }
                        }

                        var _is_customized = function( key ) {
                              item_model_candidate.customized = _.isArray( item_model_candidate.customized ) ? item_model_candidate.customized : [];
                              return _.contains( item_model_candidate.customized, key );
                        };
                        var ready_for_template = {};
                        _.each( item_model_candidate, function( _val, _key ){
                              //assign the provided value by default
                              ready_for_template[ _key ] = _val;
                              if ( ! _is_customized( _key ) ) {
                                    // If the property is predefined in the defaultItemSettings, let's use it.
                                    // But make sure the data is ready to be used by the module. For Example, remove 'px' in the font-size or line-height
                                    switch( _key ) {
                                          case 'selector' :
                                                if (  _.has( defaultItemSettings, _key ) && ! _.isNull( defaultItemSettings[ _key ] ) ) {
                                                      ready_for_template[ _key ] = defaultItemSettings[ _key ];
                                                }
                                          break;
                                          // Color and color-hover might be set to "main" in the json
                                          // make sure it's set to #000000 if it's the case => @todo should be assigned to the skin / primary color
                                          case 'color' :
                                          case 'color-hover' :
                                                ready_for_template[ _key ] = TCFontAdmin.defaultModel[ _key ];//"#000000";
                                          break;

                                          // Should be stored as a number
                                          // Falls back on 16 / 24 px
                                          case 'font-size' :
                                          case 'line-height' :
                                                ready_for_template[ _key ] = _val;
                                                //Keep only the number
                                                if ( ! _.isNumber( ready_for_template[ _key ] ) && _.isString( ready_for_template[ _key ] ) ) {
                                                      ready_for_template[ _key ] = +ready_for_template[ _key ].replace( /[^0-9.]+/g , '' );
                                                }
                                                if ( ! _.isNumber( ready_for_template[ _key ] ) ) {
                                                    ready_for_template[ _key ] = TCFontAdmin.defaultModel[ _key ]; // 16 for font-size, 24 for line-height
                                                }

                                          break;

                                          default :
                                                ready_for_template[ _key ] = _val;
                                          break;
                                    }
                              }
                        });
                        return ready_for_template;
                  },



                  // REACT ON SUBSET CHANGE
                  // fired on :
                  // item.czr_Input.when( 'subset', function( _input_ ) {
                  //    _input_.bind( function( newSubset ) { ... } );
                  // });
                  //
                  subsetChangeReact : function( newSubset ) {
                        var item = this,
                            fontFamilyInput = item.czr_Input( 'font-family' );

                        // RESET SELECT2 + EMPTY ALL FONT FAMILIES IN THE SELECT
                        fontFamilyInput.container.find('select').czrSelect2('destroy').empty();

                        //let's add a small delay because the select has > 1400 options. Not sure if needed.
                        _.delay( function() {
                              // RE-POPULATE THE FONT FAMILIES
                              fontFamilyInput._setupSelectForFontFamily();

                              // MAYBE CHANGE THE FONT-FAMILY TO BE CONSISTENT WITH THE SELECTED SUBSET
                              // Stop here if no font-family is set yet
                              if ( ! _.isString( fontFamilyInput() ) ||_.isEmpty( fontFamilyInput() ) )
                                    return;
                              // => if the the current font family does not belong to the new subset,
                              // then set the first font family found for this subset or fallback on the defaut model font ( helvetica )
                              var googleFormattedCurrentFontFamily = fontFamilyInput().replace('[gfont]', '').replace('[cfont]', ''),
                                  fontData,
                                  newFontFamily = item.czr_Input( 'font-family' )();//initialized with the current value

                              // in the gfont raw collection, the font looks like : "Annie+Use+Your+Telescope:regular"
                              // we need to check if we have a match with "Annie+Use+Your+Telescope"
                              if ( ! _.isUndefined( newSubset ) && ! _.isNull( newSubset ) && 'all-subsets' != newSubset ) {
                                    // retrieves the subset info of the current font family
                                    fontData = _.find( TCFontAdmin.fontCollection.gfonts, function( data ) {
                                          return data.name.substr( 0, googleFormattedCurrentFontFamily.length ) == googleFormattedCurrentFontFamily;
                                    });

                                    // The fontData should be an object
                                    //{
                                    //  name : "Advent+Pro:700"
                                    //  subsets : ["latin", "greek", "latin-ext"]
                                    //}
                                    if ( ! _.isObject( fontData ) || _.isUndefined( fontData.subsets ) )
                                      return;

                                    // if the new subset is found for the current font family we don't move
                                    // otherwise, let's set the first font family of the collection matching the new subset
                                    // falls back on TCFontAdmin.defaultModel['font-family'] => "[cfont]Helvetica Neue, Helvetica, Arial, sans-serif"
                                    if ( _.contains( fontData.subsets, newSubset ) ) {
                                          return;
                                    } else {
                                          var firstFontFoundForNewSubset = _.first( _.filter( TCFontAdmin.fontCollection.gfonts, function( data ) {
                                                return data.subsets && _.contains( data.subsets, newSubset );
                                          }) );
                                          if ( ! _.isObject( firstFontFoundForNewSubset ) || _.isUndefined( firstFontFoundForNewSubset.name ) ) {
                                                newFontFamily = TCFontAdmin.defaultModel['font-family'];
                                          } else {
                                                firstFontFoundForNewSubset = firstFontFoundForNewSubset.name;
                                                if ( ! _.isString( firstFontFoundForNewSubset ) || _.isEmpty( firstFontFoundForNewSubset ) ) {
                                                      newFontFamily = TCFontAdmin.defaultModel['font-family'];
                                                } else {
                                                      newFontFamily = '[gfont]' + firstFontFoundForNewSubset;
                                                }
                                          }
                                    }

                                    // Set the newFontFamily
                                    item.czr_Input( 'font-family' )( newFontFamily );

                                    // czrSelect2 might not be synchronized => trigger a change on the $ element to reflect the api modification
                                    // https://czrSelect2.org/programmatic-control/methods#examples
                                    fontFamilyInput.container.find('select').trigger('change');
                              }//if
                        }, 200 );//delay
                  },//subsetChangeReact()




                  //overrides the default parent method by a custom one
                  //at this stage, the model passed in the obj is up to date
                  // => debounced for better performances. If not, scrolling the font is not smooth.
                  writeItemViewTitle : function( item_model ) {
                        var item = this,
                            module = item.module,
                            _model = item_model || item(),
                            _title = module.firstToUpperCase( _model.id ).replace(/_/g,' '),//<=always fall back on the model id if no title set
                            _fontFamily = _model['font-family'];

                        if ( ! _.isEmpty( _model.title ) && _.isString( _model.title ) ) {
                            _title = api.CZR_Helpers.capitalize( _model.title );
                        }

                        var buffer = _.debounce( function() {
                              // For a custom selector, the title is concatenation of the model.title and the selector
                              if ( module.isCustomSelectorId( item().id ) ) {
                                   _title = module.getCustomSelectorTitle( _model[ 'selector' ] );
                              }

                              // Truncate
                              _title = api.CZR_Helpers.truncate( _title, 30 );


                              // Print
                              item.module.maybeLoadFont( {
                                    family : _fontFamily,
                                    subset : item().subset,
                                    fontClass : 'wfc_google_font_' + item.id
                              } ).done( function() {
                                    var $title = $( '.' + module.control.css_attr.item_title , item.container ).find('h4')
                                          .html('')
                                          .append(
                                                $('<span>', { html : _title } ).css( module.getInlineFontStyle( _fontFamily ) )
                                          );
                                    $title.find('span').addClass('font-effect-' + _model['static-effect'] );
                                    if ( _.contains( _model['customized'], 'color') ) {
                                          $title.find('span').css('color', _model['color'] );
                                    }
                              });

                              //Add a hook here
                              api.CZR_Helpers.doActions('after_writeViewTitle', item.container , _model, item );
                        }, 300 );
                        buffer();
                  }

            }//CZRWFCItemCtor

      });//extend
})( wp.customize , jQuery, _ );