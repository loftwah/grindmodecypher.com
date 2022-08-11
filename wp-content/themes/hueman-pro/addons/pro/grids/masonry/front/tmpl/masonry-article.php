<?php
/*  Print the masonry article. Runs the WP loop on the $wp_query object.
/* ------------------------------------ */
?>
<?php
// masonry used add_image_size( 'thumb-medium-no-crop', 520, 9999, false );
$thumb_size = apply_filters( 'hu_masonry_grid_thumb_size', 'thumb-medium-no-crop' );

// $map = array(
//   'col-1c'  => 'thumb-xxlarge',
//   'col-2cl' => 'thumb-xlarge',
//   'col-2cr' => 'thumb-xlarge',
//   'col-3cm' => 'thumb-large',
//   'col-3cl' => 'thumb-large',
//   'col-3cr' => 'thumb-large'
// );

if( !function_exists('hu_limit_srcset_img_width_for_thumb_masonry') ) {
    // do not allow the browser to pick a size larger than 'thumb-large'
    function hu_limit_srcset_img_width_for_thumb_masonry() {
        $max_src_size = '520';
        $masonry_thumb_size = PC_HAPGRIDS::$instance->grid_thumb_size_for_columns;
        $masonry_thumb_size = hu_is_checked( 'blog-use-original-image-size' ) ? 'full' : $masonry_thumb_size;
        // Map of the size candidates for masonty
        // @see filter 'hu_masonry_grid_thumb_size'
        // @see sizes registered after setup theme in Hueman
        $map = array(
            'thumb-xxlarge' => '1320',
            'thumb-xlarge' => '980',
            'thumb-large' => '720',
            'thumb-large-no-crop' => '720',
            'thumb-medium-no-crop' => '520',
            'thumb-medium' => '520',
            'full' => '1600'
        );

        if ( array_key_exists( $masonry_thumb_size, $map ) ) {
            $max_src_size = $map[$masonry_thumb_size];
        }
        return $max_src_size;
    }
}
// april 2020 : added for https://github.com/presscustomizr/hueman/issues/866
// filter has to be set in article tmpl (instead of before and after the wp query) to be taken into account when using infinite scroll
if ( !has_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_thumb_masonry' ) ) {
  add_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_thumb_masonry' );
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( array('group', 'grid-item') ); ?>>
  <div class="post-inner post-hover">

    <div class="post-thumbnail">
      <a href="<?php the_permalink(); ?>">
        <?php hu_the_post_thumbnail( $thumb_size );?>
        <?php if ( has_post_format('video') && !is_sticky() ) echo'<span class="thumb-icon"><i class="fas fa-play"></i></span>'; ?>
        <?php if ( has_post_format('audio') && !is_sticky() ) echo'<span class="thumb-icon"><i class="fas fa-volume-up"></i></span>'; ?>
        <?php if ( is_sticky() ) echo'<span class="thumb-icon"><i class="fas fa-star"></i></span>'; ?>
      </a>
      <?php if ( hu_is_comment_icon_displayed_on_grid_item_thumbnails() ): ?>
        <a class="post-comments" href="<?php comments_link(); ?>"><i class="far fa-comments"></i><?php comments_number( '0', '1', '%' ); ?></a>
      <?php endif; ?>
    </div><!--/.post-thumbnail-->

    <div class="post-content">
      <?php if ( hu_is_checked( 'post-list-meta-category' ) || hu_is_checked( 'post-list-meta-date' ) ) : ?>
        <div class="post-meta group">
          <?php if ( hu_is_checked( 'post-list-meta-category' ) ) : ?>
            <p class="post-category"><?php the_category(' / '); ?></p>
          <?php endif; ?>
          <?php if ( hu_is_checked( 'post-list-meta-date' ) ) : ?>
            <?php get_template_part('parts/post-list-author-date'); ?>
          <?php endif; ?>
        </div><!--/.post-meta-->
      <?php endif; ?>

      <h2 class="post-title entry-title">
        <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute( array( 'before' => __( 'Permalink to ', 'hueman' ) ) ); ?>"><?php the_title(); ?></a>
      </h2><!--/.post-title-->

      <?php if (hu_get_option('excerpt-length') != '0'): ?>
      <div class="entry excerpt entry-summary">
        <?php the_excerpt(); ?>
      </div><!--/.entry-->
      <?php endif; ?>
    </div>
  </div><!--/.post-inner-->
</article><!--/.post-->
<?php
if ( has_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_thumb_masonry' ) ) {
  remove_filter( 'max_srcset_image_width', 'hu_limit_srcset_img_width_for_thumb_masonry' );
}
?>