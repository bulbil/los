<?php
/**
 * Default Page Header
 *
 * @package WP-Bootstrap
 * @subpackage WP-Bootstrap
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri();?>/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144"
          href="<?php echo get_template_directory_uri();?>/assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
          href="<?php echo get_template_directory_uri();?>/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
          href="<?php echo get_template_directory_uri();?>/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed"
          href="<?php echo get_template_directory_uri();?>/assets/ico/apple-touch-icon-57-precomposed.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> >

            <div class="header">
                <div class="navbar-header">

                    <a class="navbar-brand" href="#">THE LAND <img src="wp-content/themes/bootstrapwp/los_logo.png" />OF SUNSHINE</a>

                </div>  
                <div class="navbar navbar-collapse">
                    <ul class="nav nav-pills nav-justified">
                    <li class="active"><a href="http://localhost:8888/los/php/home.php">home</a></li>
                    <li><a href="http://localhost:8888/los/php/form.php">add new</a></li>
                    <li><a href="http://localhost:8888/los/php/show-data.php">show data</a></li>
                    </ul>
                </div>
            </div>