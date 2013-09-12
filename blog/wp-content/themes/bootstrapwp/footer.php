<?php
/**
 * Default Footer
 *
 * @package WordPress
 * @subpackage BootstrapWP
 */
?>
        <footer>
            <div class="container">
		<div class="row">
		<div class="span8 offset2">
                <p><img src="http://mirrors.creativecommons.org/presskit/buttons/80x15/svg/by-sa.svg" alt="creative commons by share alike" />&nbsp; <?php bloginfo('name'); ?>, <?php the_time('Y') ?></p>
                <?php
                if (function_exists('dynamic_sidebar')) {
                    dynamic_sidebar("footer-content");
                } ?>
		</div>
		</div>
            </div><!-- /container -->
        </footer>
        <?php wp_footer(); ?>
    </body>
</html>