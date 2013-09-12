<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'los_blog');

/** MySQL database username */
define('DB_USER', 'los');

/** MySQL database password */
define('DB_PASSWORD', 'lummis');

/** MySQL hostname */
define('DB_HOST', 'dbhost.landofsunshine.org');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'd_dwo-_}&0GqwHG4g3|vm5.w1cfkKc/)gCc,D$g:9M.KLA%V&92|}<rBax=uC3TN');
define('SECURE_AUTH_KEY',  'uxiA4sc$&J=/lsR&d-1cF2&G{a,wPqy}Y$6-VW$PTXq+6;;#6Z;)U45#aN+^Ew}N');
define('LOGGED_IN_KEY',    'CB_p>{8lS^&&~n*{~O`>j}vuk+akKj!=%LNpPf7&yH+YH3G~g@le.ToJ*>Wnvd$6');
define('NONCE_KEY',        'Ka,e2W4[uF-+ik>&PJEmEV)gYoaBb_P$v-2-]^lgkZlDEncP_;A:/:QR`{4R8x=l');
define('AUTH_SALT',        '~Y(._#S]W`hXIzNv3xgOMxdih+|gY@|D]Qh1=Byvd#0<-|dfkC|7D|c*^75lf$<A');
define('SECURE_AUTH_SALT', 'J|$WJ#;Z2*sR0axe+g~+ju*e;_{Nu$OVm[OH~#1HR=bXzKAfT|A`u1}-KY0m+x4+');
define('LOGGED_IN_SALT',   '-/}AvPtzD...*6JrvjHOzMmkk9i4|VDf;YVPYPxsA}$]&e=Q {QKn#2#f2`:;+zC');
define('NONCE_SALT',       '#~7jEH?w?eA|Z@A3s-c2:xE~/ZN2!Zc-sO+;KocpC3NN`*(]wDA}_uUcn+MHOB}|');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
