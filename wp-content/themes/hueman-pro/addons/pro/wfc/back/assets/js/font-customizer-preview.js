/*
 * WordPress Font Customizer Preview
 * copyright (c) 2014-2015 Nicolas GUILLAUME (nikeo), Press Customizr.
 * GPL2+ Licensed
*/
(function (api, $, _ ) {
      var DBSettings      = TCFontPreview.DBSettings,
          Selectors     = [],
          _tempStaticEffectStorage   = {};


      // @param {args}
      // {
      //   id : string,
      //   selector : string,
      //   properties : { prop1, prop2, ..., prop n }
      // }
      // was styleSelector( itemId , selector , _data_ , Excluded )
      function styleSelector( args ) {
            var _defaults = {
                        id : '',
                        selector : '',
                        properties : {},
                  };
            args = _.extend ( _defaults, args );

            //may be initialize the _tempStaticEffectStorage
            _tempStaticEffectStorage[ args.id ] = _tempStaticEffectStorage[ args.id ] || {};
            _tempStaticEffectStorage[ args.id ][ 'has-tried-inset' ] = _tempStaticEffectStorage[ args.id ][ 'has-tried-inset' ] || false;

            //Since v1.19 => MENU ITEMS adds a class name to the body tag to remove the first letter default customizr style
            //Check if the theme is Customizr with the .tc-header length check
            if ( 'menu_items' == args.id && 0 < $('.tc-header').length ) {
                  $('body').addClass('wfc-reset-menu-item-first-letter');
            }


            var _pre_css_ = [];

            _.each( args.properties, function( _val_, property ) {
                  // Some properties like font-style can get the _not_set_ value when the user chooses "Select"
                  // we don't want to print this specific value
                  if ( '_not_set_' == _val_ )
                        return;

                  switch( property ) {
                        case 'color-hover' :
                              // var _style_id_ = 'live-wfc-color-hover',
                              //     $style_element = ( 0 === $( '#' + _style_id_).length ) ? $('<style>' , { id : _style_id_ , type : "text/css" }) : $( '#' + _style_id_ );

                              // if (  1 > $('head').find( '#' + _style_id_ ).length ) {
                              //     $('head').append( $style_element );
                              // }
                              //_pre_css_.push( [ args.selector, ':hover','{', 'color:', _val_ + '!important','}' ].join('') );

                              _pre_css_.push( {
                                    pseudo : 'hover',
                                    property : 'color',
                                    val : _val_,
                                    important : true
                              });

                              //make sure to set the color style ( if any ) not !important, otherwise the color-hover is not taken into account
                              if ( _.has( args.properties, 'color' ) ) {
                                  //_pre_css_.push( [ args.selector, '{', 'color:', args.properties.color,'}' ].join('') );
                                  _pre_css_.push( {
                                        property : 'color',
                                        val : args.properties.color,
                                  });
                              }
                        break;
                        case 'font-size' :
                        case 'line-height' :
                              if ( ! _.isString( _val_ ) )
                                return;

                              var _numericSize = _val_.replace( 'px', '').replace('rem','');

                              _numericSize = parseInt( _numericSize , 10);

                              if ( ! _.isNumber( _numericSize ) )
                                return;

                              var _em = parseFloat( _numericSize ) / 16;

                              _pre_css_.push( {
                                    property : property,
                                    val : _em + 'rem'
                              });
                        break;
                        case 'letter-spacing' :
                              if ( ! _.isString( _val_ ) )
                                return;

                              _val_ = _val_.replace( 'px', '').replace('rem','');
                              _val_ = parseInt( _val_ , 10);
                              if ( ! _.isNumber( _val_ ) )
                                return;

                              _pre_css_.push( {
                                    property : property,
                                    val : _val_ + 'px'
                              });

                        break;
                        case 'font-family' :
                              var font_family = _val_,
                                    subset     = args.properties['subset'] ? args.properties['subset'] : 0,
                                    //clean font
                                    clean_font_family = RemoveFontType(font_family);

                              //adds the gfont link to parent frames only if font contains gfont
                              if ( -1 != font_family.indexOf('gfont') ) {
                                    tcAddFontLink ( _.isString( args.selector ) ? args.selector : '' , clean_font_family, subset);
                              }

                              var propCandidate = toStyle( clean_font_family );
                              // {
                              //   font-family:"Lora"
                              //   font-style:"italic"
                              //   font-weight:"700"
                              // }
                              _.each( propCandidate, function( val, prop ){
                                    _pre_css_.push( {
                                          property : prop,
                                          val : val,
                                          important : true
                                    });
                              });

                        break;

                        case 'static-effect' :
                              //Get an array of css classes
                              var ClassesList = $( args.selector ).attr('class');

                              //checks if we have a list of class to parse first
                              if ( ClassesList ) {
                                    ClassesList = ClassesList.split(' ');
                                    //Loop over array and check if font-effect exists anywhere in the class name
                                    for ( var i = 0; i < ClassesList.length; i++ ) {
                                        //Checks if font-effect exists in the class name
                                        if (ClassesList[i].indexOf('font-effect') != -1) {
                                              //font-effect Exists, remove the class
                                              $(args.selector).removeClass(ClassesList[i]);
                                        }
                                    }
                              }//end if classeslist?

                              //Add class
                              $( args.selector ).addClass( 'font-effect-' + _val_ );

                              //adds the effect to temp setting object
                              _tempStaticEffectStorage[args.id]['static-effect'] = _val_;
                              _tempStaticEffectStorage[args.id]['has-tried-inset'] = ( 'inset' == _tempStaticEffectStorage[args.id]['static-effect'] ) ? true : _tempStaticEffectStorage[args.id]['has-tried-inset'];
                        break;

                        //the color case handle the specific case where inset effect is set
                        case 'color' :
                              //do we have a current effect set ?(either from DB or set in the current customization session)
                              var CurrentEffect = _tempStaticEffectStorage[ args.id ]['static-effect'] ? _tempStaticEffectStorage[ args.id ]['static-effect'] : '';

                              if ( CurrentEffect != 'inset' && DBSettings[ args.id ] && 'inset' == DBSettings[ args.id ]['static-effect'] ) {
                                    $( args.selector ).css('background-color' , 'transparent');
                                    _pre_css_.push( {
                                          property : 'background-color',
                                          val : 'transparent',
                                    });
                              }
                              _pre_css_.push( {
                                    property : property,
                                    val : _val_,
                              });
                        break;

                        // case 'important' :
                        //       //if users has check the 'override other style' checkbox, then change the style attribute of the selector
                        //       // => flag all properties (but Font family because handle separately) with !important
                        //       if ( true === _val_ || 1 == _val_ ) {
                        //             SetPropImportant( 'all' , $( args.selector ) );
                        //       }
                        // break;

                        default :
                              //if users has checked the 'override other style' checkbox, then change the style attribute of the selector
                              // => flag all properties (but Font family because handle separately) with !important
                              _pre_css_.push( {
                                    property : property,
                                    val : _val_
                              });
                        break;
                  }//end switch
            });//end _.each

            var rulesCand = {},
                stylableProp = [
                    'font-family',
                    'font-style',
                    'font-weight',
                    'font-size',
                    'line-height',
                    'color',
                    'color-hover',
                    'letter-spacing',
                    'text-align',
                    'text-decoration',
                    'text-transform'
                ];
            _.each( _pre_css_, function( data ) {
                  // normalize
                  data = _.extend( {
                        property : '',
                        val : '',
                        pseudo : '',
                        important : 1 == args.properties.important || true === args.properties.important
                  }, data );

                  // skip if not a stylable property
                  if ( ! _.contains( stylableProp, data.property ) )
                    return;

                  var selector = args.selector;
                  if ( ! _.isEmpty( data.pseudo ) ) {
                        //if several slectors are used, separated by commas, we need to apply the pseudo class to all
                        var _selectors_ = [];
                        _.each( selector.split(','), function( _sel_ ) {
                              _sel_ = [ _sel_, ':', data.pseudo ].join('');
                              _selectors_.push( _sel_ );
                        });
                        selector = _selectors_.join(',');
                  }
                  var val = data.val;
                  if ( false !== data.important && ! _.isEmpty( val ) ) {
                        val = val + '!important';
                  }
                  rulesCand[ selector ] = rulesCand[ selector ] || {};
                  if ( ! _.isEmpty( val ) ) {
                        rulesCand[ selector ][ data.property ] = val;
                  }
            });

            // treat for CSS stylsheet
            var cssCandidate = '';
            _.each( rulesCand, function( rules, selector ) {
                  var _rules_ = [];
                  _.each( rules, function( val, property ) {
                        _rules_.push( [ property, ':', val ].join('') );
                  });
                  cssCandidate += [ selector, '{', _rules_.join(';'), '}' ].join('');
            });

            //Append
            var _stylesheet_id_ = 'wfc-style-' + args.id;

            if ( 1 > $( '#' + _stylesheet_id_ ).length ) {
                  $('head').append( $('<style>' , { id : _stylesheet_id_ , type : "text/css" } ) );
            }

            $( '#' + _stylesheet_id_ ).html(  cssCandidate ).attr('data-origin', 'customizer' );

      }//styleSelector

      function RemoveFontType(font){
            return font ? font.replace('[cfont]' , '').replace('[gfont]' , '') : false;
      }

      function toReadable(font){
            return font ? font.replace(/[+|:]/g, ' ') : false;
      }

      function removeChar(expression) {
            return expression ? expression.replace( /[^0-9.]+/g , '') : false;
      }

      function CleanSelector(selector) {
            selector = selector.replace(/[.|#]/g, '');
            return selector.replace(/\s+/g, '-');
      }


      function toStyle( font ){
            if ( ! _.isString( font ) || _.isEmpty( font ) ) {
                  throw new Error( "preview::stoStyle => invalid font");
            }
            var split         = font.split(':'),
                font_family, font_weight, font_style = '';

            font_family       = split[0];
            //removes all characters
            font_weight       = split[1] ? removeChar(split[1]) : '';
            font_style        = ( split[1] && -1 != split[1].indexOf('italic') ) ? 'italic' : '';

            return {'font-family': toReadable(font_family), 'font-weight': ( font_weight || 400 ) , 'font-style': ( font_style || '' ) };
      }



      function tcAddFontLink(selector , font , subset) {
            Selectors[selector] = font;
            var apiUrl        = [ '//fonts.googleapis.com/css?family=' ];
            apiUrl.push(font);

            //adds the subset parameter if specified
            if ( subset && 'all-subsets' != subset ) {
               apiUrl.push('&subset=' + subset );
            }

            //add font links
            if ($('link#' + CleanSelector(selector) ).length === 0) {
                $('link:last').after('<link class="gfont" id="' + CleanSelector(selector) + '" href="' + apiUrl.join('') + '" rel="stylesheet" type="text/css">');
            }
            else {
              $('link#' + CleanSelector(selector)).attr('href', apiUrl.join('') );
            }
      }







      api.bind( 'preview-ready', function() {

            api.preview.bind( 'wfc_joy', function( args ) {
                  var _defaults = {
                        id : '',
                        data : {}
                  };
                  args = _.extend ( _defaults, args );

                  // build the customized data
                  var _customized_ = {};
                  _.each( args.data.customized, function( customizedProperty ) {
                        _customized_[ customizedProperty ] = args.data[ customizedProperty ];
                  });

                  // Before anything else, make sure that the provided selector can be parsed by $
                  try {
                        $( args.data.selector );
                  } catch ( er ) {
                        console.log( 'Invalid selector for jQuery : ' + args.data.selector );
                        return;
                  }

                  // make sure we have a selector, and the element exists before applying any style
                  // pass only the customized properties
                  // always pass the id, the selector
                  if ( args.data.selector && 0 < $( args.data.selector ).length ) {
                        api.preview.send( 'wfc-selector-exists', {} );

                        try {
                              styleSelector( {
                                    id : args.data.id,
                                    selector : args.data.selector,
                                    properties : _customized_
                              });
                        } catch( er ) {
                              console.log( 'preview : styleSelector() => ', er );
                        }
                  } else {
                        // we don't have a selector
                        // => Make sure we remove any previously customized stylesheet for this selector
                        var _stylesheet_id_ = 'wfc-style-' + args.data.id;
                        if ( 0 < $( '#' + _stylesheet_id_ ).length ) {
                              $( '#' + _stylesheet_id_ ).remove();
                        }
                        // if ( _.isEmpty( args.data.selector ) ) {
                        //       console.log('Missing selector on WFC post messaging', args );
                        // }
                        if ( 1 > $( args.data.selector ).length ) {
                            //api.preview.bind( 'sync', function( events ) {
                                  api.preview.send( 'wfc-missing-selector', {
                                        id : args.data.id,
                                        selector : args.data.selector
                                  } );
                            //});
                        }
                  }
            });//api.preview.bind( 'wfc_joy')


            api.preview.bind( 'wfc_check_if_selector_exists', function( args ) {
                  var _defaults = {
                        id : '',
                        data : {}
                  };
                  args = _.extend ( _defaults, args );

                  // Before anything else, make sure that the provided selector can be parsed by $
                  try {
                        $( args.data.selector );
                  } catch ( er ) {
                        //console.log( 'Invalid selector for jQuery : ' + args.data.selector );
                        return;
                  }

                  if ( 1 > $( args.data.selector ).length ) {
                      api.preview.send( 'wfc-missing-selector', {
                            id : args.data.id,
                            selector : args.data.selector
                      } );
                  }
            });//api.preview.bind( 'wfc_joy')
      });
}) ( wp.customize, jQuery, _ );