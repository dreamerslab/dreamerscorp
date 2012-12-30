<?php
/*
 * @package WordPress 
 * @subpackage Dreamerscorp
 */

get_header(); ?>

	<div id="content">
	
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		  <h2><?php the_title(); ?></h2>
			<div class="entry">
			  <div class="migration"><p>This site has been moved to <a href="http://dreamerslab.com/" title="Go to dreamerslab.com">dreamerslab.com</a></p><p>本站已經移至 <a href="http://dreamerslab.com/" title="前往 dreamerslab.com">dreamerslab.com</a></p></div>
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

			</div>
		</div>
		
		<?php endwhile; endif; ?>
		
	 <?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
	 
	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>