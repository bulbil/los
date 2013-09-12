<?php
/**
 * Template Name: Page - Home Hero
 * Description: Displays page title and content in Hero section above 3 widgets.
 *
 * @package WordPress
 * @subpackage BootstrapWP
 */
get_header(); ?>
    <div class="row">
        <div class="span8 offset2">
            <?php if (function_exists('bootstrapwp_breadcrumbs')) {
                bootstrapwp_breadcrumbs();
            } ?>
        </div>
    </div><!--/.row -->

    <div class="row">

        <div class="span8 offset2">

            <?php while (have_posts()) : the_post(); ?>

                <?php the_content(); ?>
                <hr/>

            <?php // reset the loop
            endwhile;
            wp_reset_query(); ?>

            <?php // Blog post query
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            query_posts(array('post_type' => 'post', 'paged' => $paged, 'showposts' => 0));
            if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div <?php post_class(); ?>>
                    <a href="<?php the_permalink(); ?>" title="<?php the_title();?>">
                        <h3><?php the_title();?></h3>
                    </a>
                    <p class="meta">
                        <?php echo bootstrapwp_posted_on();?>
                    </p>

                    <div class="row">

                        <?php // Post thumbnail conditional display.
                        if ( bootstrapwp_autoset_featured_img() !== false ) : ?>
                            <div class="span2">
                                <a href="<?php the_permalink(); ?>" title="<?php  the_title_attribute( 'echo=0' ); ?>">
                                    <?php echo bootstrapwp_autoset_featured_img(); ?>
                                </a>
                            </div>
                            <div class="span6">
                        <?php else : ?>
                            <div class="span6">
                        <?php endif; ?>
                                <?php the_excerpt(); ?>
                            </div>
                    </div><!-- /.row -->

                </div><!-- /.post_class -->

            <?php // end of blog post loop.
            endwhile; endif; ?>


	<div class="span6">
            <?php bootstrapwp_content_nav('nav-below');?>
        </div>


    </div><!--/.row -->
	        <div id="sidebar" class="span2">
	            <?php
        	    if (function_exists('dynamic_sidebar')) {
                	dynamic_sidebar("home-right");
	            } ?>
        	</div>

</div><!--/.container -->

<?php get_footer(); ?>