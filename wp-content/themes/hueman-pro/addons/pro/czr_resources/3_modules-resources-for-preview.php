<?php
//FILTER THE QUERY DATA SENT TO THE PANEL FOR THE PRO HEADER MODULE
add_filter( 'czr-preview-query-data', 'ha_filter_preview_query_data' );
//@param (array) $_wp_query_infos = array(
//  'conditional_tags' => array(),
//  'query_data' => $query_data
//)
function ha_filter_preview_query_data( $_wp_query_infos ) {
  if ( ! array_key_exists( 'query_data', $_wp_query_infos ) )
    return $_wp_query_infos;

  $new_query_data                 = $_wp_query_infos['query_data'];
  $new_query_data                 = is_array( $new_query_data ) ? $new_query_data : array();
  $new_query_data['post_title']   = wp_strip_all_tags( apply_filters( 'hph_title', get_bloginfo('name') ) );//get filtered with hu_set_hph_title(), can return mixed html string
  $new_query_data['subtitle']     = wp_strip_all_tags( apply_filters( 'hph_subtitle', get_bloginfo('description') ) );//get filtered with hu_set_hph_subtitle(), can return mixed html string
  $_wp_query_infos['query_data']  = $new_query_data;

  return $_wp_query_infos;
}


//Add the control dependencies
add_action( 'customize_controls_print_footer_scripts'   , 'hu_pro_extend_ctrl_dependencies', 100 );
function hu_pro_extend_ctrl_dependencies() {
    ?>
    <script id="pro-control-dependencies" type="text/javascript">
      (function (api, $, _) {
          //@return boolean
          var _is_checked = function( to ) {
                  return 0 !== to && '0' !== to && false !== to && 'off' !== to;
          };
          //when a dominus object define both visibility and action callbacks, the visibility can return 'unchanged' for non relevant servi
          //=> when getting the visibility result, the 'unchanged' value will always be checked and resumed to the servus control current active() state
          api.CZR_ctrlDependencies.prototype.dominiDeps = _.extend(
              api.CZR_ctrlDependencies.prototype.dominiDeps,
              [
                  {
                      dominus : 'pro_post_list_design',
                      servi   : [ 'pro_grid_columns' ],
                      visibility : function( to, servusShortId ) {

                          if ( _.contains( [
                                        'masonry-grid',
                                        'classic-grid' ], to ) ) {
                              return true;
                          }


                          return false;
                      }
                  },
                  /*
                  {
                      dominus : 'pro_skins',
                      servi : [ 'color-1', 'color-2', 'color-topbar', 'color-header', 'color-header-menu', 'color-mobile-menu', 'color-footer' ],
                      //servi : [ 'color-1', 'color-2', 'color-header', 'color-header-menu', 'color-mobile-menu', 'color-footer' ],
                      visibility : function( to, servusShortId ) {
                          return true;
                      },
                      actions : function( to, servusShortId, dominusParams ) {
                          var _servi = dominusParams.servi ? dominusParams.servi : [],
                              wpServusId = api.CZR_Helpers.build_setId( servusShortId ),
                              _id;
                          switch( to ) {
                              case 'light' :
                                  _.each( _servi, function( _shortId ) {
                                      _id = api.CZR_Helpers.build_setId( _shortId );
                                      if ( api.has( _id ) ) {
                                          if ( 'color-1' != _id ) {
                                            api( _id )( '#ffffff' );
                                          } else {
                                            api( _id )( '#000000' );
                                          }
                                      }
                                  });

                              break;
                              case 'dark' :
                                  _.each( _servi, function( _shortId ) {
                                      _id = api.CZR_Helpers.build_setId( _shortId );
                                      if ( api.has( _id ) ) {
                                          api( _id )( '#000000' );
                                      }
                                  });
                              break;
                              case 'none' :
                                  _.each( _servi, function( _shortId ) {
                                      _id = api.CZR_Helpers.build_setId( _shortId );
                                      if ( api.has( _id ) ) {
                                          var _defColor = api.control.has( _id ) ? api.control( _id ).params.defaultValue : '#909090';
                                          api( _id )( _defColor );
                                      }
                                  });
                              break;
                          }
                          // if ( 'show_on_front' == servusShortId ) {
                          //       if ( 'posts' != to && $( '.' + _class , api.control(wpServusId).container ).length ) {
                          //             $('.' + _class, api.control(wpServusId).container ).remove();
                          //       } else if ( 'posts' == to ) {
                          //             _maybe_print_html();
                          //       }
                          // } else if ( 'page_for_posts' == servusShortId ) {
                          //       if ( 'page' != to && $( '.' + _class , api.control(wpServusId).container ).length ) {
                          //             $('.' + _class, api.control(wpServusId).container ).remove();
                          //       } else if ( 'page' == to ) {
                          //             _maybe_print_html();
                          //       }
                          // }
                      }
                  }
                */
              ]//dominiDeps {}
          );//_.extend()
      }) ( wp.customize, jQuery, _);
    </script>
    <?php
}