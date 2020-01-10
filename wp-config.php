<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'spike' );

/** MySQL database username */
define( 'DB_USER', 'amaury' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Tc5v93KK' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '63Hl*Ki<N#Pz?GsI<7i:M%!b&TVa4TSg~x5[KX!!!Mtj&.T:U8$0:Qo`&R2](Yf}' );
define( 'SECURE_AUTH_KEY',  'd2DIh#k Vq*Z +%G).#4&29Z9cK:30][6,hVbKoVTI_{cM$jI1!Hi7W2iiMYj3N(' );
define( 'LOGGED_IN_KEY',    'JAktmt%eL>zuABe@`AqBS1}EgGpPIJyR4pNRg<&_#E:YE`{[{BS*OciTr>w;DEln' );
define( 'NONCE_KEY',        'XGZpxW+1WC})w[VK3HrpYxIxM,.v[z(>%86&t7/Pk*rK2#z`Dj3V9j1x!Btp_rfy' );
define( 'AUTH_SALT',        'Ceg6kIx~^0>#*XGH|nrIazK#am5cn.j O_7$f}b=y5P6{@dw2}-@8VecuPf#.-GJ' );
define( 'SECURE_AUTH_SALT', 'N3$Xf;}`<qL)A7uW:*%l^7!nTw}cN,zYgA_+gNQVs!#&xSAP]_$>yGz%sBXny>k-' );
define( 'LOGGED_IN_SALT',   'PVCJuzNH/wo.h^|Y0l4Z]R<,hSfI<^I^p+Hyei}}%f K/pek?O=/Tv=8&E;m>sa6' );
define( 'NONCE_SALT',       'W;$+[v[<Wxn%Q^rzu i_~NKRg*tbFe@H^_#A!NQ%R,^7$| ^pd>>,q0#09#lE9)d' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_spike_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

