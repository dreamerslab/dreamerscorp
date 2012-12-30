<?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */

get_header();
?>

<div id="content">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

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
            <div class="editor-id"><strong><?php the_author() ?></strong></div>
        </div>
      </div>
      <div class="entry">
        <div class="migration"><p>This site has been moved to <a href="http://dreamerslab.com/" title="Go to dreamerslab.com">dreamerslab.com</a></p><p>本站已經移至 <a href="http://dreamerslab.com/" title="前往 dreamerslab.com">dreamerslab.com</a></p></div>
        <?php the_content('more &raquo;'); ?>
      </div>
      <p class="postmetadata">
        <?php the_time('Y-m-d h:i:s') ?> <br />
        Posted in <?php the_category(', ') ?> | <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?>
      </p>
    </div>
  </div>

<?php endwhile; ?>

  <div class="navigation navigation-alt clearfix">
    <div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
    <div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
  </div>

<?php else : ?>

  <h2 class="center">Not Found</h2>
  <p class="center">Sorry, but you are looking for something that isn't here.</p>
<?php get_search_form(); ?>

<?php endif; ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>