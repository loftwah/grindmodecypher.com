//extends api.CZRDynModule

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
})( wp.customize , jQuery, _ );