//extends api.CZRDynModule

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