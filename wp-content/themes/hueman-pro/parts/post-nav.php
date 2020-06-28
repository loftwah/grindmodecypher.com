<?php if ( is_single() ): ?>
	<ul class="post-nav group">
		<li class="next"><?php next_post_link('%link', '<i class="fas fa-chevron-right"></i><strong>'.__('Next story', 'hueman-pro').'</strong> <span>%title</span>'); ?></li>
		<li class="previous"><?php previous_post_link('%link', '<i class="fas fa-chevron-left"></i><strong>'.__('Previous story', 'hueman-pro').'</strong> <span>%title</span>'); ?></li>
	</ul>
<?php endif; ?>