<?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */

get_header(); ?>

	<div id="content" >

	<?php if (have_posts()) : ?>

		<h2 class="pagetitle">Search Results</h2>

		<div class="navigation navigation-alt clearfix">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>


		<?php while (have_posts()) : the_post(); ?>

	<div <?php post_class() ?>>
    <div class="post-wrap post-wrap-alt">
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
          <h2>
            <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
              <?php the_title(); ?>
            </a>
          </h2>
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
      <p class="postmetadata">Posted in <?php the_category(', ') ?> | <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
   </div>
   </div>
		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">No posts found. Try a different search?</h2>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>