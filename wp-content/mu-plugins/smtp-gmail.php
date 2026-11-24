<?php
/**
 * Plugin Name: SMTP Gmail Tienda
 * Description: Envía todos los correos de WordPress/WooCommerce por SMTP de Gmail.
 * Author: MOTO PARTS CRISTHIAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Credenciales definidas en wp-config.php (SMTP_GMAIL_USER / SMTP_GMAIL_PASS).
if ( defined( 'SMTP_GMAIL_USER' ) ) {
	// wp_mail usa wordpress@$sitename por defecto (dominio sin TLD = invalido).
	// Sin este filtro, setFrom() lanza excepcion antes de llegar a phpmailer_init.
	add_filter( 'wp_mail_from', function () {
		return SMTP_GMAIL_USER;
	} );

	add_filter( 'wp_mail_from_name', function () {
		return get_option( 'blogname', 'MOTO PARTS CRISTHIAN' );
	} );
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( ! defined( 'SMTP_GMAIL_USER' ) || ! defined( 'SMTP_GMAIL_PASS' ) ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = 'smtp.gmail.com';
	$phpmailer->Port       = 587;
	$phpmailer->SMTPAuth   = true;
	$phpmailer->SMTPSecure = 'tls';
	$phpmailer->Username   = SMTP_GMAIL_USER;
	$phpmailer->Password   = SMTP_GMAIL_PASS;
	$phpmailer->CharSet    = 'UTF-8';

	// Gmail exige que el remitente coincida con la cuenta autenticada.
	$from = SMTP_GMAIL_USER;
	$name = get_option( 'blogname', 'MOTO PARTS CRISTHIAN' );
	$phpmailer->setFrom( $from, $name, false );

	$reply_to = get_option( 'woocommerce_email_reply_to_address' );
	if ( $reply_to && is_email( $reply_to ) ) {
		$phpmailer->addReplyTo( $reply_to, $name );
	}
}, 10 );
