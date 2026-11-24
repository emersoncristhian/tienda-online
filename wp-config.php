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
define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ?: 'tienda_online_wp' );

/** Database username */
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ?: 'root' );

/** Database password */
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ?: '' );

/** Database hostname */
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: '127.0.0.1' );

/** Soporte SSL para bases de datos MySQL en la nube (ej. Aiven, PlanetScale) */
if ( getenv( 'WORDPRESS_DB_SSL' ) === 'true' ) {
	define( 'MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL );
}

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
define( 'AUTH_KEY',          '$lL]5Q(AH6DJmIyS*|)BI[MV[wCfI5g{{#/*5SRo2(/eOn.Iu}zx%ZMcq8V}^>.<' );
define( 'SECURE_AUTH_KEY',   '3+EE4CNPk|  vz&#s+T2=:8k=/}G#8H(C2?,11MJ[<dGbz9VQipT=pBvm>9qS`a<' );
define( 'LOGGED_IN_KEY',     'K2J73%U11lS~+{|E7q2oD8`VkbvyQdx915eWu`9-$%RoJ&1na#mb:_6T~1nfS-,=' );
define( 'NONCE_KEY',         '[ny^?/E}dQ^|}t:/I5o*#+FwG@EA`F}xOXh-hW:-1*YS2.i4ieYL%3;njOP,^v0Q' );
define( 'AUTH_SALT',         'EodE=I,<Ii#g`39<SY?x3_U XZ7xY:gPOz{51![#~;:oOp}yxiNYr<g4drve `rZ' );
define( 'SECURE_AUTH_SALT',  '+EE}:fEe?N,CqGJgEC%uA-85qv:yM%mD_2}OTdy07VyU.#)JB9JL&:2Y T/HtT:K' );
define( 'LOGGED_IN_SALT',    'a[v7y^# Ob(<Rb)Cz2p#)r0Ru3+eo{8ZDO1B)ZDGKiwf~>R3N,.rphs_f]o3oeb=' );
define( 'NONCE_SALT',        '+{(6tz$-:q5a&$5 tw~Dj9eYT%WDKL-P=ar[VA~xnBB5a(GLkG>zyIyKPyDXcyk_' );
define( 'WP_CACHE_KEY_SALT', '2X)Op,T5`l|^mntnQ6.Ke0+8DQLI#/h,L/L(xLgAqGuN880j-*{[2!o&U#QM/Znm' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

/* SMTP Gmail - envio de correos WooCommerce */
if ( getenv( 'SMTP_GMAIL_USER' ) ) {
	define( 'SMTP_GMAIL_USER', getenv( 'SMTP_GMAIL_USER' ) );
}
if ( getenv( 'SMTP_GMAIL_PASS' ) ) {
	define( 'SMTP_GMAIL_PASS', getenv( 'SMTP_GMAIL_PASS' ) );
}

/* Render termina TLS en el proxy; WordPress debe reconocer la petición HTTPS. */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

if ( getenv( 'WP_HOME' ) ) {
	define( 'WP_HOME', rtrim( getenv( 'WP_HOME' ), '/' ) );
}
if ( getenv( 'WP_SITEURL' ) ) {
	define( 'WP_SITEURL', rtrim( getenv( 'WP_SITEURL' ), '/' ) );
}

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

/* Seguridad */
define( 'DISALLOW_FILE_EDIT', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
