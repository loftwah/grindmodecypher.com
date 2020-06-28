<?php
/*  Print the post list articles. Runs the WP loop on the $wp_query object.
/* ------------------------------------ */
while ( have_posts() ) :
        the_post();
    if ( apply_filters( 'pc_hapmas_print_grid_start_wrapper', false ) ) :
?>
        <div id="grid-wrapper" class="<?php echo implode( ' ', apply_filters('hu_masonry_wrapper_classes', array( 'post-list group masonry') ) ) ; ?>">
    <?php
    endif;
            ha_locate_template( 'addons/pro/grids/masonry/front/tmpl/masonry-article.php', $load = true, $require_once = false );

    if ( apply_filters( 'pc_hapmas_print_grid_end_wrapper', false ) ): ?>
        </div><!--/.post-list-->
<?php
    endif;
endwhile;
?>


<?php hu_get_template_part( 'parts/pagination' ); ?>