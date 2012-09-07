<?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */

get_header();
?>

	<div id="content">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
      <div class="post-wrap">
        <div class="post-header">
          <div class="post-date">
            <div class="post-day">
              <?php the_time('d') ?>
            </div>
            <div class="post-month">
              <?php the_time('Y.m') ?>
            </div>
          </div>
          <div class="post-m">
              <h2><?php the_title(); ?></h2>
              <div class="page-edit">
                <?php the_tags('Tags: ', ', ', '<br />'); ?>
              </div>
          </div>
          <div class="post-r">
              <div class="editer-pic" >
                <?php echo get_avatar( get_the_author_email(), '60' ); ?>
              </div>
              <div class="editor-id"><strong><?php the_author() ?></strong> </div>
          </div>
        </div>
        <div class="entry">
          <div class="migration"><p>This site has been moved to <a href="http://dreamerslab.com/" title="Go to dreamerslab.com">dreamerslab.com</a></p><p>本站已經移至 <a href="http://dreamerslab.com/" title="前往 dreamerslab.com">dreamerslab.com</a></p></div>
          <?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>

          <?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

          <div class="postmetadata">
          <?php the_time('Y-m-d h:i:s') ?> <br />
          Posted in <?php the_category(', ') ?> | <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
          </div>
        </div>
        <div class="related">
          <?php wp_related_posts(); ?>
        </div>
        <br />
        <div class="navigation clearfix">
          <div class="alignleft"><?php previous_post_link('&laquo; %link') ?></div>
          <div class="alignright"><?php next_post_link('%link &raquo;') ?></div>
        </div>
      </div>
		</div>

	<?php comments_template(); ?>

	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
