<?php
/*------------------------------------
----------------------- PRE PROCESSING
-------------------------------------*/
function ha_pro_get_related_posts( $args = array() ) {
    $defaults = array(
        'order_by'        => 'date', //can take rand, comment_count, date
        'posts_per_page'  => 10,
        'related_by'      => 'categories'//can take : categories, tags, post_format, all, no_conds
    );

    $args = wp_parse_args( $args, $defaults );

    //Grabbing the post id
    //The current template is rendered via an ajax request if :
    //hu_get_option( HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name )['ajax_enabled']
    //&& ! hu_is_customizing()
    //&& ! ha_is_partial_ajax_request()
    //@see overriden related-posts.php tmpl
    $post_id = null;
    if ( hu_is_ajax() && ! hu_is_customizing() && ! ha_is_partial_ajax_request() ) {
        $post_id = ( isset( $_POST['related_post_id'] ) && ! empty( $_POST['related_post_id'] ) ) ? esc_attr( $_POST['related_post_id'] ) : $post_id;
    } else {
        wp_reset_postdata();
        global $post;
        $post_id = $post->ID;
    }

    // Define related query post arguments
    $query_args = array(
        'no_found_rows'           => true,
        'post_type' => 'post',
        'update_post_meta_cache'  => false,
        'update_post_term_cache'  => false,
        'ignore_sticky_posts'     => 1,
        'orderby'                 => in_array( $args['order_by'], array( 'rand', 'comment_count', 'date' ) ) ? $args['order_by'] : 'rand',// 'comment_count',// 'relevance',//'rand',
        'post__not_in'            => array( $post_id ),
        'posts_per_page'          => ( is_int( intval( $args['posts_per_page'] ) ) && intval( $args['posts_per_page'] ) > 0 ) ? intval( $args['posts_per_page'] ) : 10
    );

    // Related by categories
    switch( $args['related_by'] ) {
        case 'categories' :
            $cats = get_post_meta( $post_id, 'related-cat', true );
            if ( ! $cats ) {
                $cats = wp_get_post_categories( $post_id, array( 'fields'=>'ids' ) );
                $query_args['category__in'] = $cats;
            } else {
                $query_args['cat'] = $cats;
            }
        break;
        case 'tags' :
            $tags = get_post_meta($post_id, 'related-tag', true);

            if ( ! $tags ) {
                $tags = wp_get_post_tags( $post_id, array( 'fields'=>'ids') );
                $query_args['tag__in'] = $tags;
            } else {
                $query_args['tag_slug__in'] = explode( ',', $tags );
            }
            if ( ! $tags ) { $break = true; }
        break;
        case 'post_format' :
            if ( false != get_post_format( $post_id ) ) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'post_format',
                        'field'    => 'slug',
                        'terms'    => array( 'post-format-' . get_post_format( $post_id ) )
                    )
                );
            }
        break;
        case 'all' :
            $query_args['tax_query'] = array(
                'relation' => 'OR',
                array(
                  'taxonomy' => 'category',
                  'field'    => 'term_id',
                  'terms'    => wp_get_post_categories( $post_id, array( 'fields'=>'ids' ) ),
                ),
                array(
                    'taxonomy' => 'post_tag',
                    'field'    => 'term_id',
                    'terms'    => wp_get_post_tags( $post_id, array( 'fields'=>'ids') )
                )
            );
            if ( false != get_post_format( $post_id ) ) {
                $query_args['tax_query'][] = array(
                    'taxonomy' => 'post_format',
                    'field'    => 'slug',
                    'terms'    => array( 'post-format-' . get_post_format( $post_id ) )
                );
            }
        break;
    }
    return ! isset( $break ) ? new WP_Query( $query_args ) : new WP_Query;
}


//hook : wp_get_attachment_image_attributes
function _set_lazy_load_attributes( $attr ) {
    $attr['data-flickity-lazyload'] = $attr['src'];
    unset($attr['src']);
    unset($attr['srcset']);
    return $attr;
}



//return the max column number
//=> fn of user columns, page layout and related post count
function _set_max_columns( $user_columns, $layout_class, $post_count ) {
    $user_columns = intval($user_columns);
    $post_count = intval($post_count);
    if ( $user_columns < 0 ) {
        $user_columns = 1;
    } else if ( $user_columns > 4 ) {
        $user_columns = 4;
    }
    $_user_columns = $user_columns;

    //$_user_columns = $user_columns  = min( array( $user_columns, $post_count ) );
    //restrict the related posts columns depending on the user choosen layout
    $columns      = array( '4', '3', '2', '1' );
                           // 4, 3, 2, 1
    $matrix       = array(
          'col-1c'  => array( 1, 1, 1, 1 ),
          'col-2cl' => array( 0, 1, 1, 1 ),
          'col-2cr' => array( 0, 1, 1, 1 ),
          'col-3cm' => array( 0, 0, 1, 1 ),
          'col-3cl' => array( 0, 0, 1, 1 ),
          'col-3cr' => array( 0, 0, 1, 1 )
    );

    if ( array_key_exists( $layout_class, $matrix ) && in_array( $user_columns, $columns ) ) {
          $match            = false;
          $keep_searching   = false;
          foreach ( $columns as $_index => $col ) {
                if ( $match ) {
                      break;
                }
                if( $col == $user_columns ) {
                      if ( true == (bool)$matrix[$layout_class][$_index] ) {
                            $match = true;
                      } else {
                            $keep_searching = true;
                      }
                }
                if ( $keep_searching ) {
                      if ( true == (bool)$matrix[$layout_class][$_index] ) {
                            $match = true;
                      }
                }
                $_user_columns = $col;
          }
    }
    return $_user_columns;
}


?>


<?php
//OPTIONS
// $defaults_related_opts = array(
//     'freescroll'    => true,
//     'col_number'    => 3,
//     'order_by'      => 'rand',//can take rand, comment_count, date
//     'related_by'    => 'categories',//can take : categories, tags, post_format, all
//     'cell_height'   => 'normal',
//     'post_number'   => 10
// );
// $defaults_related_opts = array(
// [id] => pro_related_posts_czr_module
// [title] =>
// [enable] => 1
// [col_number] => 4
// [cell_height] => normal //can take : normal, tall
// [display_heading] => 1
// [heading_text] => You may also like...
// [freescroll] => 1
// [post_number] => 10
// [order_by] => rand //can take rand, comment_count, date,
// [related_by] => categories //can take : categories, tags, post_format, all, no_conds
// )
$defaults_related_opts = HU_AD() -> pro_related_posts -> related_post_model;

//GRAB THE OPTIONS
// => Always get the option from the $_POSTED data in ajax
// if ajax disabled, rely on the regular options
//The current template is rendered via an ajax request if :
//hu_get_option( HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name )['ajax_enabled']
//&& ! hu_is_customizing()
//&& ! ha_is_partial_ajax_request()
//@see overriden related-posts.php tmpl
$db_opts = array();
$layout_class = 'col-3cm';
$free_related_posts_opt = 'categories';
if ( hu_is_ajax() && ! hu_is_customizing() && ! ha_is_partial_ajax_request() ) {
    if ( isset( $_POST['pro_related_posts_opt'] ) && ! empty( $_POST['pro_related_posts_opt'] ) ) {
        if ( is_array( $_POST['pro_related_posts_opt'] ) )
          $db_opts = $_POST['pro_related_posts_opt'];
        else if ( is_string( $_POST['pro_related_posts_opt'] ) )
          $db_opts = json_decode( wp_unslash( $_POST['pro_related_posts_opt'] ) );
    }
    if ( isset( $_POST['free_related_posts_opt'] ) && ! empty( $_POST['free_related_posts_opt'] ) ) {
        $free_related_posts_opt = $_POST['free_related_posts_opt'];
    }
    if ( isset( $_POST['layout_class'] ) && ! empty( $_POST['layout_class'] ) ) {
        $layout_class = $_POST['layout_class'];
    }
} else {
    $db_opts = hu_get_option( HU_AD() -> pro_related_posts -> pro_related_posts_short_opt_name );
    $layout_class = hu_get_layout_class();
    $free_related_posts_opt = hu_get_option( 'related-posts' );//can take '1' ( disable), categories, tags
}

//HANDLE HUEMAN FREE REL POST OPTION
//amend with hueman free option but make sure $free_related_posts_opt has an acceptable value
if ( is_string( $free_related_posts_opt ) && in_array( $free_related_posts_opt, array( '1', 'categories', 'tags' ) ) ) {
    $defaults_related_opts['enable'] = '1' != $free_related_posts_opt;
    if ( '1' != $free_related_posts_opt ) {
      $defaults_related_opts['related_by'] = $free_related_posts_opt;
    }
}


//Are we well formed ?
$db_opts = ( ! is_array( $db_opts ) || ! array_key_exists( 'id', $db_opts ) ) ? array() : $db_opts;
//Normalizes with defaults
$db_opts = wp_parse_args( $db_opts , $defaults_related_opts );


//RELATED POSTS ENABLED ?
$rel_posts_enabled = esc_attr( hu_booleanize_checkbox_val( $db_opts['enable'] ) );

if ( $rel_posts_enabled ) {

    //CELL HEIGHT
    //$max_height = esc_attr( $db_opts['cell_height'] );// 'tall' or 'normal';

    //FREE SCROLL
    $is_free_scroll = esc_attr( hu_booleanize_checkbox_val( $db_opts['freescroll'] ) );

    //POST NUMBER
    $posts_per_page = esc_attr( $db_opts['post_number'] );

    //RELATED BY
    $related_by = esc_attr( $db_opts['related_by'] );

    //ORDER BY
    $order_by = esc_attr( $db_opts['order_by'] );

    //DISPLAY HEADING ?
    $display_heading = esc_attr( hu_booleanize_checkbox_val( $db_opts['display_heading'] ) );

    //HEADING's TEXT
    $heading_text = html_entity_decode( esc_attr( $db_opts['heading_text'] ) );

    $related = ha_pro_get_related_posts(
        array(
            'order_by'        => $order_by,//can take rand, comment_count, date
            'posts_per_page'  => $posts_per_page,
            'related_by'      => $related_by//can take : categories, tags, post_format, all
        )
    );


    //COLUMN NUMBER
    //The max column is a function of user col number, page layout and the post count
    $max_columns = esc_attr( $db_opts['col_number'] );
    $max_columns = _set_max_columns( $max_columns, $layout_class, $related -> post_count );

    //When is the carousel draggable and when do we display the prev/next arrows ?
    // => when the max col number is > post_count
    $can_be_flickitised = $related -> post_count > $max_columns;

    $wrapper_classes = array(
        'pro-rel-posts-wrap',
        'group',
        'col-' . $max_columns
        //'item-height-' . $max_height
    );
}// if $rel_posts_enabled
?>






<?php
/*------------------------------------
--------------------------------- VIEW
-------------------------------------*/
?>
<?php
//Let's determine which image size would be the best for the current user layout
//added april 2020 for https://github.com/presscustomizr/hueman/issues/866
$map = array(
      'col-1c'  => 'thumb-medium',//520w
      'col-2cl' => 'thumb-medium',
      'col-2cr' => 'thumb-medium',
      'col-3cm' => 'thumb-medium',
      'col-3cl' => 'thumb-medium',
      'col-3cr' => 'thumb-medium'
);
$sb_layout = hu_get_layout_class();
$related_img_size = array_key_exists( $sb_layout, $map ) ? $map[ $sb_layout ] : null;
?>
<?php if ( $rel_posts_enabled && $related -> have_posts() ): ?>
    <div id="pro-related-posts" class="<?php echo $can_be_flickitised ? 'flickitised' : 'not-flickitised'; ?>" style="<?php ha_is_partial_ajax_request() ? 'opacity:0' : ''; ?>">
        <?php if ( $display_heading ) : ?>
            <h4 class="heading">
              <i class="far fa-hand-point-right"></i><?php echo $heading_text; ?>
            </h4>
        <?php endif; ?>

        <ul class="<?php echo implode(' ', $wrapper_classes ); ?>">
            <?php
              // do not allow the browser to pick a size larger than 'thumb-medium'
              if( !function_exists('hu_limit_srcset_img_width_for_rel_post_thumb') ) {
                  function hu_limit_srcset_img_width_for_rel_post_thumb() { return '520'; }
              }
              // april 2020 : added for https://github.com/presscustomizr/hueman/issues/866
              add_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_rel_post_thumb' );

              // sets specific attributes if lazy load is enabled
              if ( $can_be_flickitised ) {
                  add_filter( 'wp_get_attachment_image_attributes', '_set_lazy_load_attributes' , 999 );
              }
            ?>
            <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                <li class="post-hover carousel-cell item">
                  <article <?php post_class(); ?>>

                    <div class="post-thumbnail">
                      <a href="<?php the_permalink(); ?>">
                        <?php hu_the_post_thumbnail( $related_img_size ); ?>
                        <?php if ( has_post_format('video') && ! is_sticky() ) echo'<span class="thumb-icon small"><i class="fas fa-play"></i></span>'; ?>
                        <?php if ( has_post_format('audio') && ! is_sticky() ) echo'<span class="thumb-icon small"><i class="fas fa-volume-up"></i></span>'; ?>
                        <?php if ( is_sticky() ) echo'<span class="thumb-icon small"><i class="fas fa-star"></i></span>'; ?>
                      </a>
                      <?php if ( comments_open() && ( hu_is_checked( 'comment-count' ) ) ): ?>
                        <a class="post-comments" href="<?php comments_link(); ?>"><i class="far fa-comments"></i><?php comments_number( '0', '1', '%' ); ?></a>
                      <?php endif; ?>
                    </div>

                    <div class="related-inner">

                      <h4 class="post-title entry-title">
                        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to ', 'hueman' ) ) ); ?>"><?php the_title(); ?></a>
                      </h4>
                      <?php if ( hu_is_checked( 'post-list-meta-date' ) ) : ?>
                        <div class="post-meta group">
                          <?php get_template_part('parts/post-list-author-date'); ?>
                        </div>
                      <?php endif; ?>
                    </div>

                  </article>
                </li>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
            <?php
              remove_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_rel_post_thumb' );

              if ( $can_be_flickitised ) {
                  remove_filter( 'wp_get_attachment_image_attributes', '_set_lazy_load_attributes' , 999 );
              }
            ?>
        </ul><!--/.post-related-->
    </div>


    <?php wp_reset_query(); ?>


    <script type="text/javascript" id="reviews-carousel">
        jQuery( function($) {
            // make sure fitText plugin is loaded
            // fixes https://github.com/presscustomizr/hueman-pro-addons/issues/212
            if( !$.fn.fitText )
              return;
            var $_proRelPostsWrap = $('.pro-rel-posts-wrap', '#pro-related-posts');

            $('#pro-related-posts').css('opacity' , 1 );

            $_proRelPostsWrap.find( '.post-title').fitText( 1.2, { minFontSize: '14px', maxFontSize: '17px' });
            $_proRelPostsWrap.find( '.post-meta').fitText( 1.2, { minFontSize: '13px', maxFontSize: '14px' });

            <?php if ( $can_be_flickitised ) : ?>
                $_proRelPostsWrap.flickity({
                    // cellAlign: 'left',
                    // contain: true
                    cellSelector: '.carousel-cell',
                    prevNextButtons: true,
                    pageDots: false,
                    wrapAround: true,
                    imagesLoaded: true,
                    //setGallerySize: false,
                    dragThreshold: 10,
                    autoPlay: false,//_autoPlay, // {Number in milliseconds }
                    //pauseAutoPlayOnHover: self.pauseAutoPlayOnHover,
                    //accessibility: false,
                    lazyLoad: 2,//self.lazyLoadOpt,//<= load images up to 3 adjacent cells when freescroll enabled
                    draggable: true,// ! self.isSingleSlide,
                    freeScroll: <?php echo $is_free_scroll ? 'true' : 'false'; ?>,
                    freeScrollFriction: 0.07,// default : 0.075
                });
            <?php endif; ?>

            _.delay( function() {
                //reset the hardcoded rules set in the stylesheet
                $_proRelPostsWrap.css({ 'max-height' : 'none', overflow : 'visible' } );
            }, 200 );
        });
    </script>

<?php endif; ?>