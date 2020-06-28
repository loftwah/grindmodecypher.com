
var CZRWFCModuleMths = CZRWFCModuleMths || {};

( function ( api, $, _, TCFontAdmin ) {
      $.extend( CZRWFCModuleMths, {
            //////////////////////////////////////////////////////////
            /// INPUT CONSTRUCTORS
            //////////////////////////////////////////
            CZRWFCItemInputCtor : {
                  // ready : function() {
                  //       api.CZRInput.prototype.ready.call( input);
                  // },
                  //overrides the default method
                  setupSelect : function() {
                        // if ( 'selector' != this.id )
                        //   return;

                        var input      = this;

                        switch( input.id ) {
                              // the id case occurs only when setting the pre-item
                              // we want to make sure we always have the first option selected
                              case 'id' :
                                    input._setupSelectForPreItemSelectorId();
                              break;
                              case 'subset' :
                                    input._setupSelectForSubset();
                              break;
                              case 'font-family' :
                                    input._setupSelectForFontFamily();
                              break;
                              case 'static-effect' :
                              case 'font-weight' :
                              case 'font-style' :
                              case 'text-align' :
                              case 'text-decoration' :
                              case 'text-transform' :
                                    var input_parent  = input.input_parent,
                                        _selectOptions  = {},
                                        _model = input_parent();

                                    if ( TCFontAdmin.selectOptionLists && TCFontAdmin.selectOptionLists[ input.id ] ) {
                                          _selectOptions = TCFontAdmin.selectOptionLists[ input.id ];
                                    } else {
                                          api.consoleLog( 'CZRWFCItemInputCtor::setupSelect => missing option list for ' + input.id );
                                    }


                                    var writeOption = function( _title , _id ) {
                                          // the title of static-effect is an array
                                          _title = _.isArray( _title ) ? _title[0] : _title;
                                          var _html_ = ! _.isEmpty( _title ) ? _title : _id,
                                                _attributes = {
                                                    value : _id,
                                                    html: _html_
                                                };
                                          if ( _id == _model[ input.id ] ) {
                                                $.extend( _attributes, { selected : "selected" } );
                                          }

                                          $( 'select[data-czrtype="' + input.id + '"]', input.container ).append( $('<option>', _attributes) );
                                    };

                                    // First write the default option
                                    // but not for the static-effect which already has a no-effect option value
                                    if ( 'static-effect' != input.id ) {
                                          writeOption( TCFontAdmin.Translations['Select'], '_not_set_' );
                                    }

                                    // Then writes the other options
                                    _.each( _selectOptions , writeOption );

                                    $( 'select[data-czrtype="' + input.id + '"]', input.container ).selecter();
                              break;
                        }
                  },


                  ////////////////////////////////////////////////////////
                  /// PRE-ITEM
                  _setupSelectForPreItemSelectorId : function() {
                        var input      = this,
                            module     = input.module,
                            _selectOptions  = {};
                        _selectOptions = $.extend( true, {},  module.availableSelectors() );

                        //generates the options
                        var _firstOptionSelected = false;
                        var _generateSelectorOptions = function( _selectOptions ) {
                              var _html_ = '';
                              _.each( _selectOptions , function( _selectorData ,_id ) {
                                    var optionTitle = '';

                                    // Turn the id into a title if we need to fallback on it
                                    // => Capitalize + remove _ character
                                    if ( _.isString( _selectorData.title ) && ! _.isEmpty( _selectorData.title ) ) {
                                          optionTitle = _selectorData.title;
                                    } else {
                                          optionTitle = module.firstToUpperCase( _id ).replace(/_/g,' ');
                                    }

                                    if ( ! _firstOptionSelected ) { //if (_id == _model[ input.id ] ) {
                                          _html_ += '<option selected="selected" value="' +_id + '">' + optionTitle + '</option>';
                                          _firstOptionSelected = true;
                                    } else {
                                          _html_ += '<option value="' +_id + '">' + optionTitle + '</option>';
                                    }

                                    // if ( ! _firstOptionSelected ) {
                                    //       $.extend( _attributes, { selected : "selected" } );
                                    //       _firstOptionSelected = true;
                                    // }
                                    //$( 'select[data-czrtype="' + input.id + '"]', input.container ).append( $('<option>', _attributes) );
                              });
                              return _html_;
                        };

                        // generate the cfont and gfont html
                        _.each( [
                              {
                                    title : TCFontAdmin.Translations['Pre-defined selectors'],
                                    list : _.omit(  _selectOptions , 'custom' )
                              },
                              {
                                    title : TCFontAdmin.Translations['Define a custom selector'],
                                    list : { custom : { title : _selectOptions.custom.title } }
                              }
                        ], function( _data_ ) {
                              var $optGroup = $('<optgroup>', { label : _data_.title , html : _generateSelectorOptions( _data_.list ) });
                              $( 'select[data-czrtype="' + input.id + '"]', input.container ).append( $optGroup );
                        });



                        $( 'select[data-czrtype="' + input.id + '"]', input.container ).selecter();
                  },
                  ////////////////////////////////////////////////////////
                  /// /PRE-ITEM


                  _setupSelectForFontFamily : function() {
                        var input = this;
                        input._preprocessSelect2ForFontFamily().done( function( customResultsAdapter ) {
                              input._setupSelectForFontFamilySelector( customResultsAdapter );
                        });
                  },


                  // @return void();
                  // Instantiates a czrSelect2 select input
                  // http://ivaynberg.github.io/czrSelect2/#documentation
                  _setupSelectForFontFamilySelector : function( customResultsAdapter ) {
                        var input      = this,
                            item  = input.input_parent,
                            _model = item(),
                            _googleFontsFilteredBySubset = function() {
                                  var subset = item.czr_Input('subset')(),
                                      filtered = _.filter( TCFontAdmin.fontCollection.gfonts, function( data ) {
                                            return data.subsets && _.contains( data.subsets, subset );
                                      });

                                  if ( ! _.isUndefined( subset ) && ! _.isNull( subset ) && 'all-subsets' != subset ) {
                                        return filtered;
                                  } else {
                                        return TCFontAdmin.fontCollection.gfonts;
                                  }

                            },
                            $fontSelectElement = $( 'select[data-czrtype="' + input.id + '"]', input.container );

                        // generates the options
                        // @param type = cfont or gfont
                        var _generateFontOptions = function( fontList, type ) {
                              var _html_ = '';
                              _.each( fontList , function( font_data ) {
                                    var _value = font_data.name,
                                        optionTitle = _.isString( _value ) ? _value.replace(/[+|:]/g, ' ' ) : _value,
                                        _setFontTypePrefix = function( val, type ) {
                                              return _.isString( val ) ? [ '[', type, ']', val ].join('') : '';//<= Example : [gfont]Aclonica:regular
                                        };

                                    _value = _setFontTypePrefix( _value, type );

                                    if ( _value == _model['font-family'] ) {
                                          _html_ += '<option selected="selected" value="' + _value + '">' + optionTitle + '</option>';
                                    } else {
                                          _html_ += '<option value="' + _value + '">' + optionTitle + '</option>';
                                    }
                              });
                              return _html_;
                        };

                        //add the first option
                        if ( _.isNull( _model['font-family'] ) || _.isEmpty( _model['font-family'] ) ) {
                              $fontSelectElement.append( '<option value="none" selected="selected">' + TCFontAdmin.Translations['Select a font family'] + '</option>' );
                        } else {
                              $fontSelectElement.append( '<option value="none">' + TCFontAdmin.Translations['Select a font family'] + '</option>' );
                        }


                        // generate the cfont and gfont html
                        _.each( [
                              {
                                    title : TCFontAdmin.Translations['Web Safe Fonts'],
                                    type : 'cfont',
                                    list : TCFontAdmin.fontCollection.cfonts
                              },
                              {
                                    title : TCFontAdmin.Translations['Google Fonts'],
                                    type : 'gfont',
                                    list : _googleFontsFilteredBySubset()
                              }
                        ], function( fontData ) {
                              var $optGroup = $('<optgroup>', { label : fontData.title , html : _generateFontOptions( fontData.list, fontData.type ) });
                              $fontSelectElement.append( $optGroup );
                        });

                        var _fonts_czrSelect2_params = {
                                //minimumResultsForSearch: -1, //no search box needed
                            //templateResult: paintFontOptionElement,
                            //templateSelection: paintFontOptionElement,
                            escapeMarkup: function(m) { return m; },
                        };
                        /*
                        * Maybe use custom adapter
                        */
                        if ( customResultsAdapter ) {
                              $.extend( _fonts_czrSelect2_params, {
                                    resultsAdapter: customResultsAdapter,
                                    closeOnSelect: false,
                              } );
                        }

                        //http://ivaynberg.github.io/czrSelect2/#documentation
                        //FONTS
                        $fontSelectElement.czrSelect2( _fonts_czrSelect2_params );
                        $( '.czrSelect2-selection__rendered', input.container ).css( input.module.getInlineFontStyle( input() ) );

                  },


                  //@return a promise()
                  _preprocessSelect2ForFontFamily : function() {
                        /*
                        * Override czrSelect2 Results Adapter in order to select on highlight
                        * deferred needed cause the selects needs to be instantiated when this override is complete
                        * selec2.amd.require is asynchronous
                        */
                        var selectFocusResults = $.Deferred();
                        if ( 'undefined' !== typeof $.fn.czrSelect2 && 'undefined' !== typeof $.fn.czrSelect2.amd && 'function' === typeof $.fn.czrSelect2.amd.require ) {
                              $.fn.czrSelect2.amd.require(['czrSelect2/results', 'czrSelect2/utils'], function (Result, Utils) {
                                    var ResultsAdapter = function($element, options, dataAdapter) {
                                      ResultsAdapter.__super__.constructor.call(this, $element, options, dataAdapter);
                                    };
                                    Utils.Extend(ResultsAdapter, Result);
                                    ResultsAdapter.prototype.bind = function (container, $container) {
                                      var _self = this;
                                      container.on('results:focus', function (params) {
                                        if ( params.element.attr('aria-selected') != 'true') {
                                          _self.trigger('select', {
                                              data: params.data
                                          });
                                        }
                                      });
                                      ResultsAdapter.__super__.bind.call(this, container, $container);
                                    };
                                    selectFocusResults.resolve( ResultsAdapter );
                              });
                        }
                        else {
                              selectFocusResults.resolve( false );
                        }

                        return selectFocusResults.promise();

                  },//_preprocessSelect2ForFontFamily

                  //@return void()
                  _setupSelectForSubset : function() {
                        var input      = this,
                            input_parent  = input.input_parent,
                            _selectOptions  = {},
                            _model = input_parent();
                        _selectOptions = TCFontAdmin.fontCollection.subsets;

                        //generates the options
                        _.each( _selectOptions , function( _title ,_id ) {
                              var _html_ = ! _.isEmpty( _title ) ? _title :_id,
                                  _attributes = {
                                        value :_id,
                                        html: input.module.firstToUpperCase( _html_ )
                                  };
                              if ( _id == _model[ input.id ] ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              }

                              $( 'select[data-czrtype="' + input.id + '"]', input.container ).append( $('<option>', _attributes) );
                        });
                        $( 'select[data-czrtype="' + input.id + '"]', input.container ).selecter();
                  }

            }//CZRWFCItemInputCtor

      });//extend
})( wp.customize , jQuery, _, TCFontAdmin );