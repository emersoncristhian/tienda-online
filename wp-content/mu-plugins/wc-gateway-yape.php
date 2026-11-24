<?php
/**
 * Plugin Name: Yape Gateway para WooCommerce
 * Description: Pasarela de pago directa con Yape y confirmación automática por WhatsApp para Perú.
 * Version: 1.0.0
 * Author: MOTO PARTS CRISTHIAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'mp_init_yape_gateway', 11 );

function mp_init_yape_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class WC_Gateway_Yape extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'yape';
			$this->has_fields         = false;
			$this->method_title       = 'Yape (Pago Móvil Perú)';
			$this->method_description = 'Permite a los clientes pagar a través de Yape escaneando el QR o transfiriendo al número celular y confirmando por WhatsApp.';

			$this->init_form_fields();
			$this->init_settings();

			$this->title         = $this->get_option( 'title', 'Yape (Pago Móvil BCP)' );
			$this->description   = $this->get_option( 'description', 'Paga al instante desde tu app Yape transfiriendo a nuestro número móvil y envía tu comprobante por WhatsApp.' );
			$this->instructions  = $this->get_option( 'instructions', 'Transfiere el monto exacto por Yape y envíanos tu comprobante por WhatsApp para procesar y despachar tu pedido de inmediato.' );
			$this->yape_number   = $this->get_option( 'yape_number', '986947730' );
			$this->yape_name     = $this->get_option( 'yape_name', 'Emerson Cristhian M. Coaquira' );
			$this->whatsapp_num  = $this->get_option( 'whatsapp_num', '986947730' );
			$this->qr_image_url  = $this->get_option( 'qr_image_url', '' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => 'Habilitar/Deshabilitar',
					'type'    => 'checkbox',
					'label'   => 'Habilitar pago por Yape',
					'default' => 'yes',
				),
				'title' => array(
					'title'       => 'Título en el checkout',
					'type'        => 'text',
					'description' => 'Nombre que ve el cliente al seleccionar el método de pago.',
					'default'     => 'Yape (Pago Móvil BCP)',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Descripción',
					'type'        => 'textarea',
					'description' => 'Descripción que el cliente ve durante el proceso de pago.',
					'default'     => 'Paga al instante desde tu app Yape transfiriendo a nuestro número móvil y envía tu comprobante por WhatsApp.',
				),
				'yape_number' => array(
					'title'       => 'Número de celular Yape',
					'type'        => 'text',
					'description' => 'Número de teléfono al cual los clientes realizarán el pago.',
					'default'     => '986947730',
				),
				'yape_name' => array(
					'title'       => 'Titular de la cuenta Yape',
					'type'        => 'text',
					'description' => 'Nombre del titular que figura en la app de Yape.',
					'default'     => 'Emerson Cristhian M. Coaquira',
				),
				'whatsapp_num' => array(
					'title'       => 'WhatsApp para recibir comprobantes',
					'type'        => 'text',
					'description' => 'Número de WhatsApp de la tienda al que se enviará la constancia (sin código de país o con 51).',
					'default'     => '986947730',
				),
				'qr_image_url' => array(
					'title'       => 'URL de la imagen del Código QR (Opcional)',
					'type'        => 'text',
					'description' => 'Enlace a la imagen del código QR de tu Yape (puedes subirlo a Medios y pegar el link aquí).',
					'default'     => '',
				),
				'instructions' => array(
					'title'       => 'Instrucciones para la página de confirmación',
					'type'        => 'textarea',
					'description' => 'Instrucciones adicionales para la pantalla de agradecimiento y correos.',
					'default'     => 'Transfiere el monto exacto por Yape y envíanos tu comprobante por WhatsApp para despachar tu pedido.',
				),
			);
		}

		public function get_qr_url() {
			if ( ! empty( $this->qr_image_url ) ) {
				return $this->qr_image_url;
			}
			if ( file_exists( WP_CONTENT_DIR . '/uploads/yape-qr.jpeg' ) ) {
				return content_url( 'uploads/yape-qr.jpeg' );
			}
			if ( file_exists( __DIR__ . '/assets/yape-qr.jpeg' ) ) {
				return content_url( 'mu-plugins/assets/yape-qr.jpeg' );
			}
			return '';
		}

		public function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}

			$formatted_number = trim( chunk_split( preg_replace( '/\D/', '', $this->yape_number ), 3, ' ' ) );
			?>
			<div class="mp-yape-box" style="background:#f7f0fa;border:2px solid #742284;border-radius:12px;padding:18px 20px;margin-top:12px;color:#222;font-size:14px;line-height:1.6;">
				<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
					<span style="display:inline-block;background:#742284;color:#fff;font-weight:800;font-size:15px;padding:4px 14px;border-radius:20px;letter-spacing:0.5px;">YAPE</span>
					<strong style="color:#742284;font-size:15px;">Pago Rápido y Seguro sin comisiones</strong>
				</div>

				<div style="background:#fff;border-radius:8px;padding:12px 16px;border:1px solid #e2d2e8;margin-bottom:12px;">
					<p style="margin:0 0 6px 0;">📱 <strong>Número Yape:</strong> <span style="font-size:18px;font-weight:700;color:#742284;letter-spacing:1px;"><?php echo esc_html( $formatted_number ); ?></span></p>
					<p style="margin:0;">👤 <strong>Titular:</strong> <span style="font-weight:600;color:#333;"><?php echo esc_html( $this->yape_name ); ?></span></p>
				</div>

				<?php 
				$qr_url = $this->get_qr_url();
				if ( ! empty( $qr_url ) ) : ?>
					<div style="text-align:center;margin:14px 0;background:#fff;padding:14px;border-radius:10px;border:1px solid #e2d2e8;">
						<p style="margin:0 0 8px 0;font-size:13px;font-weight:700;color:#742284;">Escanea este código QR desde tu app Yape:</p>
						<img src="<?php echo esc_url( $qr_url ); ?>" alt="QR Yape Emerson Cristhian" style="max-width:210px;width:100%;height:auto;border-radius:10px;border:2px solid #742284;box-shadow:0 3px 10px rgba(116,34,132,0.12);display:inline-block;">
					</div>
				<?php endif; ?>

				<ol style="margin:0;padding-left:20px;color:#444;">
					<li>Abre tu aplicación <strong>Yape</strong> en tu teléfono.</li>
					<li>Transfiere el importe total al número <strong><?php echo esc_html( $formatted_number ); ?></strong> o escanea el QR.</li>
					<li>Confirma que el titular sea <strong><?php echo esc_html( $this->yape_name ); ?></strong>.</li>
					<li>Guarda la captura de tu comprobante.</li>
					<li>Al presionar <strong>"Realizar el pedido"</strong>, verás el botón para enviarnos el comprobante por WhatsApp.</li>
				</ol>
			</div>
			<?php
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			// Poner en espera hasta verificar el voucher
			$order->update_status( 'on-hold', __( 'Esperando comprobante de pago por Yape vía WhatsApp.', 'woocommerce' ) );

			// Reducir stock
			wc_reduce_stock_levels( $order_id );

			// Vaciar carrito
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		public function thankyou_page( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$clean_wsp = preg_replace( '/\D/', '', $this->whatsapp_num );
			if ( strlen( $clean_wsp ) === 9 ) {
				$clean_wsp = '51' . $clean_wsp;
			}

			$total_formatted = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ) );
			$msg = sprintf(
				'Hola MOTO PARTS CRISTHIAN, adjunto mi comprobante de pago por Yape para el Pedido #%s (Total: %s).',
				$order->get_order_number(),
				$total_formatted
			);
			$whatsapp_link = 'https://wa.me/' . $clean_wsp . '?text=' . rawurlencode( $msg );
			$formatted_number = trim( chunk_split( preg_replace( '/\D/', '', $this->yape_number ), 3, ' ' ) );
			?>
			<div class="mp-yape-thankyou" style="background:#f7f0fa;border:2px solid #742284;border-radius:14px;padding:24px;margin:24px 0;box-shadow:0 4px 16px rgba(116,34,132,0.12);">
				<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
					<span style="background:#742284;color:#fff;font-weight:800;font-size:16px;padding:6px 16px;border-radius:24px;">YAPE</span>
					<h3 style="margin:0;color:#742284;font-size:18px;">¡Casi listo! Completa tu pago por Yape</h3>
				</div>

				<p style="margin:0 0 16px 0;font-size:15px;color:#333;">
					Tu pedido <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> ha sido recibido y está reservado. Para iniciar el empaque y despacho, realiza el pago y envíanos la captura:
				</p>

				<div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #d9bfdf;margin-bottom:18px;">
					<p style="margin:0 0 8px 0;font-size:15px;">💰 <strong>Monto total a yapear:</strong> <span style="font-size:20px;font-weight:800;color:#C41E3A;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></p>
					<p style="margin:0 0 8px 0;font-size:15px;">📱 <strong>Número Yape:</strong> <span style="font-size:19px;font-weight:800;color:#742284;letter-spacing:1px;"><?php echo esc_html( $formatted_number ); ?></span></p>
					<p style="margin:0;font-size:15px;">👤 <strong>Titular:</strong> <span style="font-weight:700;color:#222;"><?php echo esc_html( $this->yape_name ); ?></span></p>
				</div>

				<?php 
				$qr_url = $this->get_qr_url();
				if ( ! empty( $qr_url ) ) : ?>
					<div style="text-align:center;margin:18px 0;background:#fff;padding:16px;border-radius:10px;border:1px solid #d9bfdf;">
						<p style="margin:0 0 8px 0;font-size:14px;font-weight:700;color:#742284;">Escanea este código QR desde tu app Yape:</p>
						<img src="<?php echo esc_url( $qr_url ); ?>" alt="QR Yape Emerson Cristhian" style="max-width:220px;width:100%;height:auto;border-radius:10px;border:2px solid #742284;box-shadow:0 4px 14px rgba(116,34,132,0.15);display:inline-block;">
					</div>
				<?php endif; ?>

				<div style="text-align:center;margin-top:20px;">
					<a href="<?php echo esc_url( $whatsapp_link ); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;gap:10px;background:#25D366;color:#fff;font-size:16px;font-weight:700;text-decoration:none;padding:14px 28px;border-radius:50px;box-shadow:0 4px 14px rgba(37,211,102,0.4);transition:transform 0.2s,box-shadow 0.2s;">
						<span style="font-size:20px;">📲</span> Enviar comprobante por WhatsApp
					</a>
					<p style="margin:10px 0 0 0;font-size:13px;color:#666;">Al hacer clic se abrirá WhatsApp con el número del pedido y monto listos para enviar.</p>
				</div>
			</div>
			<?php
		}

		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			if ( $sent_to_admin || 'on-hold' !== $order->get_status() || 'yape' !== $order->get_payment_method() ) {
				return;
			}

			$formatted_number = trim( chunk_split( preg_replace( '/\D/', '', $this->yape_number ), 3, ' ' ) );
			$clean_wsp = preg_replace( '/\D/', '', $this->whatsapp_num );
			if ( strlen( $clean_wsp ) === 9 ) {
				$clean_wsp = '51' . $clean_wsp;
			}

			$msg = sprintf(
				'Hola MOTO PARTS CRISTHIAN, adjunto mi comprobante de pago por Yape para el Pedido #%s.',
				$order->get_order_number()
			);
			$whatsapp_link = 'https://wa.me/' . $clean_wsp . '?text=' . rawurlencode( $msg );

			if ( $plain_text ) {
				echo "\n=================================================================\n";
				echo "INSTRUCCIONES DE PAGO POR YAPE\n";
				echo "=================================================================\n";
				echo "Número Yape: " . $formatted_number . "\n";
				echo "Titular: " . $this->yape_name . "\n";
				echo "Monto total: " . html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ) ) . "\n";
				echo "Envía tu comprobante a nuestro WhatsApp: https://wa.me/" . $clean_wsp . "\n\n";
			} else {
				?>
				<div style="background:#f7f0fa;border:2px solid #742284;border-radius:10px;padding:16px;margin-bottom:24px;font-family:sans-serif;">
					<h3 style="color:#742284;margin-top:0;">📱 Pago pendiente por Yape</h3>
					<p style="margin:4px 0;"><strong>Número Yape:</strong> <?php echo esc_html( $formatted_number ); ?></p>
					<p style="margin:4px 0;"><strong>Titular:</strong> <?php echo esc_html( $this->yape_name ); ?></p>
					<p style="margin:12px 0 4px 0;">Por favor realiza la transferencia y envíanos tu comprobante a nuestro WhatsApp:</p>
					<p style="margin:8px 0;"><a href="<?php echo esc_url( $whatsapp_link ); ?>" style="color:#25D366;font-weight:bold;text-decoration:underline;">👉 Haz clic aquí para enviar comprobante por WhatsApp (+<?php echo esc_html( $clean_wsp ); ?>)</a></p>
				</div>
				<?php
			}
		}
	}
}

add_filter( 'woocommerce_payment_gateways', 'mp_add_yape_gateway' );

function mp_add_yape_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Yape';
	return $gateways;
}

/**
 * Soporte para WooCommerce Checkout Blocks
 */
add_action( 'woocommerce_blocks_loaded', 'mp_register_yape_blocks_support' );

function mp_register_yape_blocks_support() {
	if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}

	final class WC_Yape_Blocks_Support extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
		protected $name = 'yape';

		public function initialize() {
			$this->settings = get_option( 'woocommerce_yape_settings', array() );

			$script_path = '/assets/wc-yape-blocks.js';
			$script_url  = content_url( 'mu-plugins' . $script_path );
			$script_file = __DIR__ . $script_path;

			wp_register_script(
				'wc-yape-blocks-integration',
				$script_url,
				array(
					'wc-blocks-registry',
					'wc-settings',
					'wp-element',
					'wp-html-entities',
				),
				file_exists( $script_file ) ? filemtime( $script_file ) : '1.0.0',
				true
			);
		}

		public function is_active() {
			return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
		}

		public function get_payment_method_script_handles() {
			return array( 'wc-yape-blocks-integration' );
		}

		public function get_payment_method_data() {
			$yape_num  = isset( $this->settings['yape_number'] ) ? $this->settings['yape_number'] : '986947730';
			$formatted = trim( chunk_split( preg_replace( '/\D/', '', $yape_num ), 3, ' ' ) );
			$name      = isset( $this->settings['yape_name'] ) ? $this->settings['yape_name'] : 'Emerson Cristhian M. Coaquira';
			$qr_url    = '';
			if ( ! empty( $this->settings['qr_image_url'] ) ) {
				$qr_url = $this->settings['qr_image_url'];
			} elseif ( file_exists( WP_CONTENT_DIR . '/uploads/yape-qr.jpeg' ) ) {
				$qr_url = content_url( 'uploads/yape-qr.jpeg' );
			} elseif ( file_exists( __DIR__ . '/assets/yape-qr.jpeg' ) ) {
				$qr_url = content_url( 'mu-plugins/assets/yape-qr.jpeg' );
			}

			$html = '<div class="mp-yape-box" style="background:#f7f0fa;border:2px solid #742284;border-radius:12px;padding:16px 18px;margin-top:10px;color:#222;font-size:14px;line-height:1.5;">';
			$html .= '<div style="background:#fff;border-radius:8px;padding:12px 14px;border:1px solid #e2d2e8;margin-bottom:12px;">';
			$html .= '<p style="margin:0 0 6px 0;">📱 <strong>Número Yape:</strong> <span style="font-size:18px;font-weight:700;color:#742284;letter-spacing:1px;">' . esc_html( $formatted ) . '</span></p>';
			$html .= '<p style="margin:0;">👤 <strong>Titular:</strong> <span style="font-weight:600;color:#333;">' . esc_html( $name ) . '</span></p>';
			$html .= '</div>';
			if ( ! empty( $qr_url ) ) {
				$html .= '<div style="text-align:center;margin:12px 0;background:#fff;padding:12px;border-radius:10px;border:1px solid #e2d2e8;">';
				$html .= '<p style="margin:0 0 8px 0;font-size:13px;font-weight:700;color:#742284;">Escanea este código QR desde tu app Yape:</p>';
				$html .= '<img src="' . esc_url( $qr_url ) . '" alt="QR Yape" style="max-width:200px;width:100%;height:auto;border-radius:10px;border:2px solid #742284;box-shadow:0 3px 10px rgba(116,34,132,0.12);display:inline-block;">';
				$html .= '</div>';
			}
			$html .= '<ol style="margin:0;padding-left:18px;color:#444;">';
			$html .= '<li>Abre tu app <strong>Yape</strong> y transfiere al <strong>' . esc_html( $formatted ) . '</strong>.</li>';
			$html .= '<li>Confirma que el titular sea <strong>' . esc_html( $name ) . '</strong>.</li>';
			$html .= '<li>Al finalizar, tendrás el botón para enviarnos tu comprobante por <strong>WhatsApp</strong>.</li>';
			$html .= '</ol>';
			$html .= '</div>';

			return array(
				'title'           => isset( $this->settings['title'] ) ? $this->settings['title'] : 'Yape (Pago Móvil BCP)',
				'description'     => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
				'descriptionHtml' => $html,
				'supports'        => array( 'products' ),
			);
		}
	}

	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new WC_Yape_Blocks_Support() );
		}
	);
}
