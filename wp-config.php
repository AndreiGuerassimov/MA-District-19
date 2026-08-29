<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'matoronto' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '#wgQ#c%f}D.B46%KhQ].;<U@joZ&#yHRcp~W6ERKr Aj+Dg<jy1l9P1CDrLd^*nX' );
define( 'SECURE_AUTH_KEY',  'EE=>,.H]UJ0R$pk;AH_*EU9,dFc<iST!01Xo*i99i.}N-%#m.$c+h2+D>mhR_*L@' );
define( 'LOGGED_IN_KEY',    '9_X*+:wR^U,-,?A%=tX@aCG{14%jv2f7ejZELcdaU<|CF6 P=7mMjRK4jeN$Q]bO' );
define( 'NONCE_KEY',        'NM={F[5|cHPj,WC;]DXxdklHjqHDRK#3:rf}?Kt!fEt^7xxxeHm2IF|51sEdVj9e' );
define( 'AUTH_SALT',        '~~?(.8><x^0ofrW[d+]f6BZr!^`qIfs$QTc!laTtdc@}376+IPtlE6Jd2nl!v@kg' );
define( 'SECURE_AUTH_SALT', 'j4EsVuHd</#3qJRcfc8uGj+?;80K+FPh3zUu7lAQy~g8-VlFCbd%<i843msErZ)H' );
define( 'LOGGED_IN_SALT',   'IK$sG@f!~a4$?FzB~e?nD!|WM;}ocQhe|.!64r{1v~^`L4IB yzu!o},Cw~YN4k%' );
define( 'NONCE_SALT',       'p!R*o]b4ze!vPSXMoATBS>/$`#@xo( D0gcAY!gKabKDsH9qxt!8s-{]6c8glD+n' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/*
 * Theme development mode. Disables WordPress's cache of theme block patterns
 * and template files, so new patterns/*.php and parts/*.html are picked up
 * without flushing transients.
 *
 * LOCAL DEVELOPMENT ONLY -- remove or set to '' in production, where the cache
 * is wanted.
 */
define( 'WP_DEVELOPMENT_MODE', 'theme' );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

/** Prevent offical WordPress theme downloads. */
define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);