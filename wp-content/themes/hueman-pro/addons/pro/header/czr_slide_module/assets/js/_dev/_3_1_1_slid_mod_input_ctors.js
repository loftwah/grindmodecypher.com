//extends api.CZRDynModule

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
})( wp.customize , jQuery, _ );