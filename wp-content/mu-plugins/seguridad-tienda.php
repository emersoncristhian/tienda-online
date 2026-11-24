<?php
/**
 * Plugin Name: Seguridad Tienda Online
 * Description: Endurecimiento basico de seguridad para WordPress/WooCommerce.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIENDA_SEC_MAX_INTENTOS', 10 );
define( 'TIENDA_SEC_VENTANA', 900 );
define( 'TIENDA_SEC_BLOQUEO', 900 );

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_filter( 'xmlrpc_methods', 'tienda_sec_sin_xmlrpc' );
add_filter( 'wp_headers', 'tienda_sec_quitar_pingback' );

function tienda_sec_sin_xmlrpc( $methods ) {
	return array();
}

function tienda_sec_quitar_pingback( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}

remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

add_filter( 'rest_endpoints', 'tienda_sec_ocultar_usuarios_rest' );

function tienda_sec_ocultar_usuarios_rest( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	return $endpoints;
}

add_action( 'send_headers', 'tienda_sec_cabeceras' );

function tienda_sec_cabeceras() {
	if ( headers_sent() ) {
		return;
	}
	header_remove( 'X-Powered-By' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	header( 'X-Permitted-Cross-Domain-Policies: none' );
}

add_filter( 'login_errors', 'tienda_sec_error_generico' );

function tienda_sec_error_generico( $errors ) {
	if ( false !== strpos( $errors, 'Demasiados intentos' ) ) {
		return $errors;
	}
	return 'Usuario o contraseña incorrectos.';
}

add_filter( 'authenticate', 'tienda_sec_comprobar_bloqueo', 30, 3 );

function tienda_sec_comprobar_bloqueo( $user, $username, $password ) {
	$ip = tienda_sec_ip();
	if ( ! $ip ) {
		return $user;
	}
	if ( get_transient( 'tienda_sec_lock_' . $ip ) ) {
		return new WP_Error( 'tienda_sec_locked', 'Demasiados intentos fallidos. Espera 15 minutos antes de volver a intentarlo.' );
	}
	return $user;
}

add_action( 'wp_login_failed', 'tienda_sec_registrar_fallo', 10, 2 );

function tienda_sec_registrar_fallo( $username, $error ) {
	$ip = tienda_sec_ip();
	if ( ! $ip ) {
		return;
	}
	$fails_key = 'tienda_sec_fails_' . $ip;
	$fails     = (int) get_transient( $fails_key );
	$fails++;
	set_transient( $fails_key, $fails, TIENDA_SEC_VENTANA );
	if ( $fails >= TIENDA_SEC_MAX_INTENTOS ) {
		set_transient( 'tienda_sec_lock_' . $ip, 1, TIENDA_SEC_BLOQUEO );
		delete_transient( $fails_key );
	}
}

add_action( 'wp_login', 'tienda_sec_limpiar_fallos' );

function tienda_sec_limpiar_fallos() {
	$ip = tienda_sec_ip();
	if ( ! $ip ) {
		return;
	}
	delete_transient( 'tienda_sec_fails_' . $ip );
	delete_transient( 'tienda_sec_lock_' . $ip );
}

function tienda_sec_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( $_SERVER['REMOTE_ADDR'] ) : '';
	if ( '' === $ip ) {
		return '';
	}
	return md5( $ip );
}
