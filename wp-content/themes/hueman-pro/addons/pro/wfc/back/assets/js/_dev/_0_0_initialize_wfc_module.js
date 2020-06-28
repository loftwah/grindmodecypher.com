
var CZRWFCModuleMths = CZRWFCModuleMths || {};

( function ( api, $, _, TCFontAdmin ) {
      $.extend( CZRWFCModuleMths, {
            initialize: function( id, constructorOptions ) {
                  var module = this;

                  module.initialConstrucOptions = $.extend( true, {}, constructorOptions );//detach from the original obj

                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, constructorOptions );

                  //extend the module with new template Selectors
                  $.extend( module, {
                        itemPreAddEl : 'czr-module-wfc-pre-item-input-list',
                        itemInputList : 'czr-module-wfc-item-input-list'
                  } );

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUTS
                  module.inputConstructor = api.CZRInput.extend( module.CZRWFCItemInputCtor || {} );

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR ITEMS AND MODOPTS
                  module.itemConstructor = api.CZRItem.extend( module.CZRWFCItemCtor || {} );

                  //declares a default Item model
                  // 'id'              => '',
                  // 'selector'        => '',
                  // 'subset'          => null,
                  // 'font-family'     => '',
                  // 'font-weight'     => null,
                  // 'font-style'      => null,
                  // 'color'           => "#000000",
                  // 'color-hover'     => "#000000",
                  // 'font-size'       => 16,//"14px",
                  // 'line-height'     => 24,//"20px",
                  // 'text-align'      => null,
                  // 'text-decoration' => null,
                  // 'text-transform'  => null,
                  // 'letter-spacing'  => 0,
                  // 'static-effect'   => 'none',
                  // 'important'       => false,
                  // 'title'           => false

                  this.defaultItemModel = TCFontAdmin.defaultModel;
                  this.themeSelectorList = TCFontAdmin.themeSelectorList;

                  // FIRE MODULE READY
                  //1) on section expansion
                  //2) or in the case of a module embedded in a regular control, if the module section is alreay opened => typically when skope is enabled
                  if ( _.has( api, 'czr_activeSectionId' ) && module.control.section() == api.czr_activeSectionId() && 'resolved' != module.isReady.state() ) {
                     module.ready();
                  }
                  api.section( module.control.section() ).expanded.bind( function() {
                        if ( 'resolved' == module.isReady.state() )
                          return;
                        module.ready();
                  });

                  // When the collection of input in the pre-item are ready, implement the dependencies
                  // Example : id => changes title
                  module.bind( 'pre-item-input-collection-ready', function() {
                        // setup model values and dependencies on init.
                        module.setupPreItemInputs();
                  });

                  // Populate the list of available selectors => the ones not yet added
                  var updateAvailableSelectorCollection = function() {
                        var _availSelectors = {};
                        _.each( TCFontAdmin.DefaultSettings, function( _data, _selectorId ){
                              if ( _.isEmpty( _.findWhere( module().items, { id : _selectorId } ) ) ) {
                                    _availSelectors[ _selectorId ] = _data;
                              }
                        });

                        // Always add the custom model
                        var customModel = $.extend( true, {}, TCFontAdmin.defaultModel );
                        customModel = _.omit( 'id' , customModel );
                        _availSelectors[ 'custom' ] = customModel;
                        _availSelectors[ 'custom' ].title = TCFontAdmin.Translations['Custom'];

                        module.availableSelectors = module.availableSelectors || new api.Value();
                        module.availableSelectors( _availSelectors );
                  };

                  // AVAILABLE SELECTOR COLLECTION
                  module.isReady.then( function() {
                        // on init
                        updateAvailableSelectorCollection();

                        // fired when the pre item is rendered and before the  input collection is setup
                        module.bind( 'before-pre-item-input-collection-setup', updateAvailableSelectorCollection );

                        // When items are added / removed
                        module.czr_Item.bind( 'add', updateAvailableSelectorCollection );
                        module.czr_Item.bind( 'remove', updateAvailableSelectorCollection );
                        module.czr_Item.bind( 'remove', function() {
                              api.previewer.refresh();
                        });
                  });

                  // Listen to the preview sending informations about the existence of the selector
                  var sectionId = module.control.section();

                  if ( api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                        var selectorExistsInPreview = new api.Value( false );

                        api.previewer.bind( 'wfc-missing-selector', function() {
                              selectorExistsInPreview( false );
                        });

                        api.previewer.bind( 'wfc-missing-selector', _.debounce( function( data ) {
                              if ( selectorExistsInPreview() ) {
                                    api.section( sectionId ).notifications.remove( 'missing_selector_in_preview' );
                              } else {
                                    selectorExistsInPreview( false );
                                    var selectorTitle = module.czr_Item.has( data.id ) ? module.czr_Item( data.id )().title : '';
                                    api.section( sectionId ).notifications.add( new api.Notification( 'missing_selector_in_preview', {
                                          type: 'warning',
                                          message: selectorTitle + '=>' + TCFontAdmin.Translations['This selector does not exist in this context.'],
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    _.delay( function() {
                                          api.section( sectionId ).notifications.remove( 'missing_selector_in_preview' );
                                    }, 5000 );
                              }
                        }, 1000 ) );

                        api.previewer.bind( 'wfc-selector-exists', function( ) {
                              selectorExistsInPreview( true );
                              api.section( sectionId ).notifications.remove( 'missing_selector_in_preview' );
                        });
                  }// if api.section( sectionId ).notifications

                  // maybe update section title
                  // @params args : {
                  //    colors : ['#4e8df4','#ec4d40','#fcc72d','#4eb369'],
                  //    firstFamily : '[gfont]Bungee+Inline:regular',
                  //    secondFamily : '[gfont]Pacifico:regular'
                  // }
                  try {
                        module.styleSectionTitle({
                              colors : ['#4e8df4','#ec4d40','#fcc72d','#4eb369'],
                              firstFamily : '[gfont]Bungee+Inline:regular',
                              secondFamily : '[gfont]Pacifico:regular'
                        });
                  } catch ( er ) {
                        api.errorLog( 'Error when styling the section title when initializing module : ' + module.id );
                        //console.log( er );
                  }
            },//initialize



            //////////////////////////////////////////////////////////
            /// NOTIFY USERS
            /// 1) when setting up a custom selector and selector is missing
            /// 2) when adding an already existing selector
            /////////////////////////////////////////
            // This method is fired by the core fmk right before an item instantiation, when the item candidate has been prepared
            validateItemBeforeAddition : function( api_ready_item ) {
                  var module = this,
                      sectionId = module.control.section();

                  // if user adds a custom selector, we need to have a css selector set
                  if ( 'custom' == api_ready_item.id && ( _.isEmpty( api_ready_item.selector ) || ! _.isString( api_ready_item.selector ) ) ) {
                        if ( api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                              api.section( sectionId ).notifications.add( new api.Notification( 'missing_selector', {
                                    type: 'warning',
                                    message: TCFontAdmin.Translations['Please specify a CSS selector'],
                                    dismissible: true
                              } ) );

                              // Removed if not dismissed after 5 seconds
                              _.delay( function() {
                                    api.section( sectionId ).notifications.remove( 'missing_selector' );
                              }, 5000 );
                        }
                        return;
                  }

                  if ( 'custom' != api_ready_item.id ) {
                      if ( api_ready_item.id && module.czr_Item && module.czr_Item.has( api_ready_item.id ) ) {
                            if ( module.control.section && ! _.isUndefined( module.control.section.notifications ) ) {
                                  api.section( sectionId ).notifications.add( new api.Notification( 'item_already_exists', {
                                        type: 'info',
                                        message: TCFontAdmin.Translations['This selector has already been added.'],
                                        dismissible: true
                                  } ) );

                                  // Removed if not dismissed after 5 seconds
                                  _.delay( function() {
                                        api.section( sectionId ).notifications.remove( 'item_already_exists' );
                                  }, 5000 );
                            }
                            return;
                      }
                  }


                  // Is this a valid selector ?
                  try {
                        $( api_ready_item.selector );
                  } catch ( er ) {
                        if ( api.section.has( sectionId ) && api.section( sectionId ).notifications ) {
                              api.section( sectionId ).notifications.add( new api.Notification( 'invalid_selector', {
                                    type: 'warning',
                                    message: TCFontAdmin.Translations['This css selector is not valid.'],
                                    dismissible: true
                              } ) );

                              // Removed if not dismissed after 5 seconds
                              _.delay( function() {
                                    api.section( sectionId ).notifications.remove( 'invalid_selector' );
                              }, 4000 );
                        }
                        return;
                  }

                  return api_ready_item;
            },

            //////////////////////////////////////////////////////////
            /// SETUP PRE ITEM MODEL INITIAL VALUES AND INPUT DEPENDENCIES
            //////////////////////////////////////////
            // fired on 'pre-item-input-collection-ready'
            // - on init the module.preItem() is not set yet. => id = "" and title = false.
            //    => we want to set starter values so that even if the user clicks on "Add it" without picking any predefined selector, it creates a valid module item with an id + a title
            // - when the pre-defined id changes, the title should be updated.
            setupPreItemInputs : function() {
                  var module = this;
                  if ( _.isUndefined( module.preItem.czr_Input ) || ! _.isObject( module.preItem.czr_Input ) ) {
                        api.errorLog('Missing input collection in the pre-item for module : ' + module.id );
                  }

                  var _getSelectorTitle = function( _selectorId_ ) {
                        _selectorId_ = _selectorId_ || module.preItem.czr_Input( 'id' )();
                        var _selectorTitle_ = '';
                        
                        // Turn the id into a title if we need to fallback on it
                        // => Capitalize + remove _ character
                        if ( _.isEmpty( _selectorId_ ) ) {
                              _selectorTitle_ = module.preItem.czr_Input( 'title' )();
                        } else {
                              _selectorTitle_ = module.firstToUpperCase( _selectorId_ ).replace(/_/g,' ');
                        }

                        // Use the DefaultSettings title if exists @see php utils::tc_get_selector_title_map()
                        if ( TCFontAdmin.DefaultSettings[ _selectorId_ ] ) {
                              if ( TCFontAdmin.DefaultSettings[ _selectorId_ ].title && ! _.isEmpty( TCFontAdmin.DefaultSettings[ _selectorId_ ].title ) ) {
                                    _selectorTitle_ = TCFontAdmin.DefaultSettings[ _selectorId_ ].title;
                              }
                        } else if ( 'custom' == _selectorId_ ) {
                              _selectorTitle_ = module.getCustomSelectorTitle( module.preItem.czr_Input('selector')() );
                        }
                        return _selectorTitle_;
                  };


                  // module.preItem.czr_Input( 'title' )( _newTitle );
                  module.preItem.czr_Input.each( function( _input_ ) {
                        switch( _input_.id ) {
                              case 'id' :
                                    _input_.bind( function( selectorId ) {
                                          module.preItem.czr_Input( 'title' )( _getSelectorTitle( selectorId ) );
                                          // Display the selector field is id is custom
                                          module.preItem.czr_Input( 'selector' ).visible( 'custom' == _input_() );
                                    });
                              break;
                              case 'selector' :
                                    _input_.bind( function() {
                                          module.preItem.czr_Input( 'title' )( _getSelectorTitle() );
                                    });
                              break;
                        }
                  });

                  // get the first selector of the collection
                  var selectorIdOnInit = Object.keys( module.availableSelectors() )[0];
                  // set it
                  // it will also set the title with the listener above
                  module.preItem.czr_Input( 'id' )( selectorIdOnInit );
            },











            //////////////////////////////////////////
            /// STYLE SECTION TITLE
            //Fired when initializing the module
            styleSectionTitle : function( args ) {
                  args = _.extend( {
                        colors : ['#4e8df4','#ec4d40','#fcc72d','#4eb369'],
                        firstFamily : '[gfont]Bungee+Inline:regular',
                        secondFamily : '[gfont]Pacifico:regular'
                  }, args );

                  var module = this,
                      _section_ = api.section( module.control.section() ),
                      $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                      $panelTitleEl = _section_.container.find('.customize-section-title h3');

                  //stop here if we don't target the right elements
                  if ( 1 > $sectionTitleEl.length && 1 > $panelTitleEl.length )
                    return;

                  var _explodedTitle = _section_.params.title.split(' '),//[ 'Font', 'Customizer' ] // test with  'sdflkjsdflkjsldf long section title'.split(' ')
                      firstWordHtml = [],
                      styledTitle = [];

                  //stop here if our title is empty
                  if ( ! _.isArray( _explodedTitle ) || 1 > _explodedTitle[0].length )
                    return;

                  //color each letter of the first word, which is Font in English
                  var _explodedWord = module.firstToUpperCase( _explodedTitle[0] ).split(''),
                      colorIndex = 0;
                  for ( var i = 0; i < _explodedWord.length; i++ ) {
                        colorIndex = colorIndex < args.colors.length ? colorIndex : 0;
                        firstWordHtml.push( '<span style="color:' + args.colors[ colorIndex ] + '">' + _explodedWord[i] +'</span>' );
                        colorIndex++;
                  }

                  // now loop on the exploded title and re-concatenate it
                  _.each( _explodedTitle, function( word, key ) {
                        //First Word style
                        if ( 1 > key ) {
                              styledTitle.push( [
                                    '<span class="wfc-first">',
                                    firstWordHtml.join(''),
                                    '</span>'
                              ].join('') );
                        }//other word(s) style
                        else {
                              styledTitle.push( [
                                    '<span class="wfc-second" style="font-size:1.2em;line-height:1.4em">',
                                    word,
                                    '</span>'
                              ].join('') );
                        }
                  });

                  // load the fonts
                  // @to improve performances by loading only the relevant text
                  module.maybeLoadFont( { family : args.firstFamily } );
                  module.maybeLoadFont( { family : args.secondFamily } );

                  // The default title looks like this : Font Customizer <span class="screen-reader-text">Press return or enter to open this section</span>
                  // we want to style "Font Customizer" only.
                  if ( 0 < $sectionTitleEl.length ) {
                        var $sectionTitleSpan = $sectionTitleEl.find('span');
                        $sectionTitleEl.html( styledTitle.join(' ') ).append( $sectionTitleSpan );
                        $sectionTitleEl.find('.wfc-first').css( module.getInlineFontStyle( args.firstFamily ) );
                        $sectionTitleEl.find('.wfc-second').css( module.getInlineFontStyle( args.secondFamily ) );
                  }

                  // The default title looks like this : <span class="customize-action">Customizing</span> Font Customizer
                  // we want to style "Font Customizer" only.
                  if ( 0 < $panelTitleEl.length ) {
                        var $panelTitleSpan = $panelTitleEl.find('span');
                        $panelTitleEl.html( styledTitle.join(' ') ).prepend( $panelTitleSpan );
                        $panelTitleEl.find('.wfc-first').css( module.getInlineFontStyle( args.firstFamily ) );
                        $panelTitleEl.find('.wfc-second').css( module.getInlineFontStyle( args.secondFamily ) );
                  }
            },











            //////////////////////////////////////////
            /// MODULE HELPERS
            //@return string
            firstToUpperCase : function( str ) {
                  return str.substr(0, 1).toUpperCase() + str.substr(1);
            },
            // @return {} used to set $.css()
            // @param font {string}.
            // Example : Aclonica:regular
            // Example : Helvetica Neue, Helvetica, Arial, sans-serif
            getInlineFontStyle : function( _fontFamily_ ){
                  // the font is set to 'none' when "Select a font family" option is picked
                  if ( ! _.isString( _fontFamily_ ) || _.isEmpty( _fontFamily_ ) )
                    return {};

                  //always make sure we remove the prefix.
                  _fontFamily_ = _fontFamily_.replace('[gfont]', '').replace('[cfont]', '');

                  var module = this,
                      split = _fontFamily_.split(':'), font_family, font_weight, font_style;

                  font_family       = module.getFontFamilyName( _fontFamily_ );

                  font_weight       = split[1] ? split[1].replace( /[^0-9.]+/g , '') : 400; //removes all characters
                  font_weight       = _.isNumber( font_weight ) ? font_weight : 400;
                  font_style        = ( split[1] && -1 != split[1].indexOf('italic') ) ? 'italic' : '';


                  return {
                        'font-family' : 'none' == font_family ? 'inherit' : font_family.replace(/[+|:]/g, ' '),//removes special characters
                        'font-weight' : font_weight || 400,
                        'font-style'  : font_style || 'normal'
                  };
            },


            // @return the font family name only from a pre Google formated
            // Example : input is Inknut+Antiqua:regular
            // Should return Inknut Antiqua
            getFontFamilyName : function( rawFontFamily ) {
                  if ( ! _.isString( rawFontFamily ) || _.isEmpty( rawFontFamily ) )
                      return rawFontFamily;

                  rawFontFamily = rawFontFamily.replace('[gfont]', '').replace('[cfont]', '');
                  var split         = rawFontFamily.split(':');
                  return _.isString( split[0] ) ? split[0].replace(/[+|:]/g, ' ') : '';//replaces special characters ( + ) by space
            },

            // check if the provided _id_ matched a key of the TCFontAdmin.DefaultSettings collection
            // @return bool
            isCustomSelectorId : function( _id_ ) {
                  if ( ! _.isString( _id_ ) )
                    return;
                  return ! _.has( TCFontAdmin.DefaultSettings, _id_ );
            },

            // @return string
            getCustomSelectorTitle : function( _selector_ ) {
                  if ( _.isString( _selector_ ) && ! _.isEmpty( _selector_ ) ) {
                        return [ TCFontAdmin.Translations['Custom'], ':', _selector_ ].join(' ');
                  } else {
                        return TCFontAdmin.Translations['Custom'];
                  }
            },


            //@return promise object
            //@param args {
            //  family : '', ex: [gfont]Warnes:regular, or Sniglet:800
            //  subset : '',
            //  fontClass : '',
            //  text : null,
            //  waitFontLoaded : false
            //}
            maybeLoadFont : function ( args ) {
                  var _dfd_ = $.Deferred(),
                        module = this;

                  //first check if this is a gfont? if cfont, returns.
                  if ( ! _.isString( args.family ) || -1 == args.family.indexOf('[gfont]') )
                      return _dfd_.resolve().promise();

                  args = _.extend( {
                        family : '',//ex: [gfont]Warnes:regular,
                        subset : null,
                        fontClass : args.family.replace(/[^A-Za-z0-9\s!?]/g,'').replace(/\s/g, "-"),
                        text : null,
                        waitFontLoaded : false
                  }, args || {} );

                  // clean prefix
                  args.family = args.family.replace('[gfont]', '').replace('[cfont]', '');

                  // //https://developers.google.com/fonts/docs/getting_started#Optimizing_Requests
                  // var text        = [ item().title.replace(/[\+|:]/g, ' ') , args.family.replace(/[\+|:]/g, ' ') ].join(' ');
                  // //remove spaces
                  // text = text.replace(/ /g, '');
                  // //add the text query parameter

                  // text = _.uniq( text.split('') );
                  // //text has to be uri encoded

                  // text = encodeURIComponent( text );
                  // text = text.join('');

                  //adds the subset parameter if specified
                  // if ( ! _.isUndefined( subset ) && ! _.isNull( subset ) && 'all-subsets' != subset ) {
                  //       args.family = [ args.family, ':', subset ].join('');
                  // }

                  var googleApiUrl = '//fonts.googleapis.com/css?family=',
                      apiUrl      = [];

                  apiUrl.push( googleApiUrl );
                  apiUrl.push( args.family );

                  // apiUrl.push( '&text=' );
                  // apiUrl.push( text );

                  //adds the subset parameter if specified
                  if ( ! _.isUndefined( args.subset ) && ! _.isNull( args.subset ) && 'all-subsets' != args.subset ) {
                     apiUrl.push('&subset=' + args.subset );
                  }

                  // document.fonts.ready.then(function () {
                  //     console.log( item.getFontFamilyName( args.family ) +' loaded? ' + document.fonts.check('1em ' + item.getFontFamilyName( args.family ) ));  // true
                  // });

                  // When supported, let's use the CSS Font Loading API
                  // else, let's wait for an arbitrary 200 ms
                  // https://caniuse.com/#feat=font-loading
                  // https://stackoverflow.com/questions/5680013/how-to-be-notified-once-a-web-font-has-loaded
                  if ( args.waitFontLoaded ) {
                        if ( document.fonts && ! _.isUndefined( document.fonts.onloadingdone ) ) {
                              document.fonts.onloadingdone = function () {
                                    // console.log('onloadingdone we have ' + fontFaceSetEvent.fontfaces.length + ' font faces loaded');
                                    // console.log('fontFaceSetEvent.fontfaces', fontFaceSetEvent.fontfaces);
                                    // console.log( item.module.getFontFamilyName( args.family ) +' loaded? ' + document.fonts.check('1em ' + item.module.getFontFamilyName( args.family ) ));

                                    if ( document.fonts.check('1em ' + module.getFontFamilyName( args.family ) ) ) {
                                        _dfd_.resolve();
                                    } else {
                                        _.delay( function() {
                                              _dfd_.resolve();
                                        }, 200 );
                                    }
                              };
                        } else {
                              _.delay( function() {
                                    _dfd_.resolve();
                              }, 200 );
                        }
                  } else {
                        _.delay( function() {
                            _dfd_.resolve();
                        }, 50 );
                  }

                  // writes font-link
                  // one font link by item ( selector )
                  if ( $('link.' + args.fontClass ).length === 0 ) {
                      $('link:last').after('<link class="' + args.fontClass + '" href="' + apiUrl.join('') + '" rel="stylesheet" type="text/css">');
                  } else {
                    $('link.' + args.fontClass ).attr( 'href', apiUrl.join('') );
                  }

                  return _dfd_.promise();
            }
      });//extend
})( wp.customize , jQuery, _, TCFontAdmin );