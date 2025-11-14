<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'qL[fY(?8N<LGE=VU{nU$2xzjHbu~!/E)c^fQ~Uop9n~Lk/#<MPc]NnsywtjVnYZR' );
define( 'SECURE_AUTH_KEY',   ')_~]r6L1@E;2PHmrGMmVc^ulfxy?cV4N<6w+;j?N4q47.$S`1VGdZ569$}z{[tf>' );
define( 'LOGGED_IN_KEY',     'Y)!?G3?forX/wp&~yEc(6rW&$f2MWW|4j+gGV4u<P&4z++ol!Mb* 35(E/^K PxD' );
define( 'NONCE_KEY',         '+Y!Hs;}TJJ)JKq~F&!1Z>TWv-MxdwNbo_5o/7G2D t%!O~7-g=KfN-kk#!>HfoZA' );
define( 'AUTH_SALT',         'H}g9M[9-9Ok#@tH/|2d)Tla?GKuU}T<Q8o|8d|+[aP`&~g#!Z}=Bx%|wV3MN3{qw' );
define( 'SECURE_AUTH_SALT',  '-@b($1Kd?I_@?tQl]-,xCX =:le*E?^?cB?E~XTl.lJ r_5WM2cRS)m*&XjIPY 6' );
define( 'LOGGED_IN_SALT',    'obP?>@,3{Al5kxn*IE<nZL=ncLLvg46aDCDW <S|:f(CW#kP#eyKozC]^Eesl0[s' );
define( 'NONCE_SALT',        'zAZzG{wSIL|H6.!tjTmPIqO(92ax&uGq-F%q!vi=W*vBJc;j@4YHr j7?- ^W|L:' );
define( 'WP_CACHE_KEY_SALT', 'J.rX(8(b;HSr|d|0{>E[iA@usV#BI4Q|&Sl6$rW~)wc%1f0wb&^3f)YzaQv&h7/|' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

/* Memory Increase for Etch */
define( 'WP_MEMORY_LIMIT', '256M' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
