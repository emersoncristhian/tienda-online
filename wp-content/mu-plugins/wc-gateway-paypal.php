<?php
/**
 * Plugin Name: PayPal Gateway para Perú (WooCommerce)
 * Description: Pasarela de pago con PayPal con conversión automática PEN a USD y soporte para Checkout Blocks.
 * Version: 1.0.0
 * Author: MOTO PARTS CRISTHIAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'mp_init_paypal_peru_gateway', 11 );

function mp_init_paypal_peru_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class WC_Gateway_Paypal_Peru extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'paypal_peru';
			$this->has_fields         = false;
			$this->method_title       = 'PayPal (Perú / Internacional)';
			$this->method_description = 'Permite a los clientes pagar con saldo PayPal o tarjetas de débito/crédito internacionales con conversión automática de Soles a Dólares.';

			$this->init_form_fields();
			$this->init_settings();

			$this->title         = $this->get_option( 'title', 'PayPal (Tarjetas de Crédito / Débito y Saldo)' );
			$this->description   = $this->get_option( 'description', 'Paga de forma rápida y segura con tu cuenta PayPal o con tarjetas de débito/crédito internacionales (Visa, Mastercard, American Express).' );
			$this->paypal_email  = $this->get_option( 'paypal_email', 'crsthnemerson@gmail.com' );
			$this->exchange_rate = (float) $this->get_option( 'exchange_rate', '3.75' );
			$this->testmode      = $this->get_option( 'testmode', 'no' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => 'Habilitar/Deshabilitar',
					'type'    => 'checkbox',
					'label'   => 'Habilitar pago por PayPal',
					'default' => 'yes',
				),
				'title' => array(
					'title'       => 'Título en el checkout',
					'type'        => 'text',
					'description' => 'Nombre que ve el cliente al seleccionar el método de pago.',
					'default'     => 'PayPal (Tarjetas de Crédito / Débito y Saldo)',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Descripción',
					'type'        => 'textarea',
					'description' => 'Descripción que el cliente ve durante el proceso de pago.',
					'default'     => 'Paga de forma rápida y segura con tu cuenta PayPal o con tarjetas de débito/crédito internacionales (Visa, Mastercard, American Express).',
				),
				'paypal_email' => array(
					'title'       => 'Correo de tu cuenta PayPal',
					'type'        => 'email',
					'description' => 'El correo asociado a tu cuenta de PayPal donde recibirás los fondos.',
					'default'     => 'crsthnemerson@gmail.com',
				),
				'exchange_rate' => array(
					'title'       => 'Tipo de cambio (Soles por 1 USD)',
					'type'        => 'text',
					'description' => 'Valor de 1 Dólar en Soles para la conversión automática en PayPal. Ejemplo: 3.75',
					'default'     => '3.75',
				),
				'testmode' => array(
					'title'       => 'Modo de pruebas (Sandbox)',
					'type'        => 'checkbox',
					'label'       => 'Habilitar modo de pruebas Sandbox de PayPal',
					'default'     => 'no',
				),
			);
		}

		public function payment_fields() {
			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}
			$rate = $this->exchange_rate > 0 ? $this->exchange_rate : 3.75;
			?>
			<div class="mp-paypal-box" style="background:#f0f7fc;border:2px solid #0079C1;border-radius:12px;padding:16px 18px;margin-top:10px;color:#222;font-size:14px;line-height:1.5;">
				<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
					<span style="display:inline-block;background:#003087;color:#fff;font-weight:800;font-size:12px;padding:3px 10px;border-radius:12px;letter-spacing:0.5px;">PAYPAL</span>
					<strong style="color:#003087;font-size:14px;">Pago Seguro Internacional</strong>
				</div>
				<p style="margin:0 0 8px 0;color:#333;">
					💳 Puedes pagar con saldo de tu cuenta <strong>PayPal</strong> o directamente con tu tarjeta de <strong>crédito o débito</strong> (Visa, Mastercard, AMEX).
				</p>
				<div style="background:#fff;border-radius:8px;padding:10px 14px;border:1px solid #cce4f5;font-size:13px;color:#555;">
					ℹ️ <strong>Conversión de moneda:</strong> Dado que PayPal no procesa Soles (PEN), tu pedido se convertirá automáticamente a <strong>Dólares (USD)</strong> al tipo de cambio de <strong>S/. <?php echo esc_html( number_format( $rate, 2 ) ); ?> por 1 USD</strong>.
				</div>
			</div>
			<?php
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$total_pen = (float) $order->get_total();
			$rate      = $this->exchange_rate > 0 ? $this->exchange_rate : 3.75;
			$usd_total = number_format( $total_pen / $rate, 2, '.', '' );

			$order->update_meta_data( '_paypal_usd_amount', $usd_total );
			$order->update_meta_data( '_paypal_exchange_rate', $rate );
			$order->save();

			$order->update_status(
				'on-hold',
				sprintf(
					__( 'Esperando pago por PayPal. Total Soles: S/. %s. Monto en USD: $%s USD (Tipo de cambio: %s).', 'woocommerce' ),
					$total_pen,
					$usd_total,
					$rate
				)
			);

			wc_reduce_stock_levels( $order_id );
			WC()->cart->empty_cart();

			$endpoint = ( 'yes' === $this->testmode )
				? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
				: 'https://www.paypal.com/cgi-bin/webscr';

			$paypal_args = array(
				'cmd'           => '_xclick',
				'business'      => $this->paypal_email,
				'item_name'     => 'Pedido #' . $order->get_order_number() . ' - MOTO PARTS CRISTHIAN',
				'amount'        => $usd_total,
				'currency_code' => 'USD',
				'no_shipping'   => '1',
				'no_note'       => '1',
				'custom'        => $order_id,
				'return'        => $this->get_return_url( $order ),
				'cancel_return' => wc_get_checkout_url(),
			);

			$redirect_url = add_query_arg( $paypal_args, $endpoint );

			return array(
				'result'   => 'success',
				'redirect' => $redirect_url,
			);
		}

		public function thankyou_page( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$usd_total = $order->get_meta( '_paypal_usd_amount' );
			$rate      = $order->get_meta( '_paypal_exchange_rate' );
			if ( ! $usd_total ) {
				$usd_total = number_format( (float) $order->get_total() / 3.75, 2, '.', '' );
			}

			$endpoint = ( 'yes' === $this->testmode )
				? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
				: 'https://www.paypal.com/cgi-bin/webscr';

			$paypal_url = add_query_arg(
				array(
					'cmd'           => '_xclick',
					'business'      => $this->paypal_email,
					'item_name'     => 'Pedido #' . $order->get_order_number() . ' - MOTO PARTS CRISTHIAN',
					'amount'        => $usd_total,
					'currency_code' => 'USD',
					'custom'        => $order_id,
					'return'        => $this->get_return_url( $order ),
					'cancel_return' => wc_get_checkout_url(),
				),
				$endpoint
			);
			?>
			<div class="mp-paypal-thankyou" style="background:#f0f7fc;border:2px solid #0079C1;border-radius:14px;padding:24px;margin:24px 0;box-shadow:0 4px 16px rgba(0,121,193,0.12);">
				<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
					<span style="background:#003087;color:#fff;font-weight:800;font-size:16px;padding:6px 16px;border-radius:24px;">PAYPAL</span>
					<h3 style="margin:0;color:#003087;font-size:18px;">Pago con PayPal registrado</h3>
				</div>

				<p style="margin:0 0 16px 0;font-size:15px;color:#333;">
					Tu pedido <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> ha sido recibido y reservado.
				</p>

				<div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #cce4f5;margin-bottom:18px;">
					<p style="margin:0 0 6px 0;font-size:15px;">💰 <strong>Total en Soles:</strong> <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></p>
					<p style="margin:0 0 6px 0;font-size:16px;">💵 <strong>Total convertido en PayPal:</strong> <span style="font-size:19px;font-weight:800;color:#003087;">$<?php echo esc_html( $usd_total ); ?> USD</span></p>
					<p style="margin:0;font-size:13px;color:#666;">(Calculado a tipo de cambio referencial S/. <?php echo esc_html( $rate ?: '3.75' ); ?>)</p>
				</div>

				<div style="text-align:center;margin-top:16px;">
					<a href="<?php echo esc_url( $paypal_url ); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;justify-content:center;gap:10px;background:#0070BA;color:#fff;font-size:16px;font-weight:700;text-decoration:none;padding:14px 28px;border-radius:50px;box-shadow:0 4px 14px rgba(0,112,186,0.35);">
						🅿️ Pagar ahora con PayPal ($<?php echo esc_html( $usd_total ); ?> USD)
					</a>
				</div>
			</div>
			<?php
		}
	}
}

add_filter( 'woocommerce_payment_gateways', 'mp_add_paypal_peru_gateway' );

function mp_add_paypal_peru_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Paypal_Peru';
	return $gateways;
}

/**
 * Soporte para WooCommerce Checkout Blocks de PayPal
 */
add_action( 'woocommerce_blocks_loaded', 'mp_register_paypal_peru_blocks_support' );

function mp_register_paypal_peru_blocks_support() {
	if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}

	final class WC_Paypal_Peru_Blocks_Support extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
		protected $name = 'paypal_peru';

		public function initialize() {
			$this->settings = get_option( 'woocommerce_paypal_peru_settings', array() );

			$script_path = '/assets/wc-paypal-blocks.js';
			$script_url  = content_url( 'mu-plugins' . $script_path );
			$script_file = __DIR__ . $script_path;

			wp_register_script(
				'wc-paypal-blocks-integration',
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
			return array( 'wc-paypal-blocks-integration' );
		}

		public function get_payment_method_data() {
			$rate = ! empty( $this->settings['exchange_rate'] ) ? (float) $this->settings['exchange_rate'] : 3.75;
			if ( $rate <= 0 ) {
				$rate = 3.75;
			}

			$html = '<div class="mp-paypal-box" style="background:#f0f7fc;border:2px solid #0079C1;border-radius:12px;padding:16px 18px;margin-top:10px;color:#222;font-size:14px;line-height:1.5;">';
			$html .= '<p style="margin:0 0 8px 0;color:#333;">💳 Puedes pagar con saldo de tu cuenta <strong>PayPal</strong> o directamente con tu tarjeta de <strong>crédito o débito</strong> (Visa, Mastercard, AMEX).</p>';
			$html .= '<div style="background:#fff;border-radius:8px;padding:10px 14px;border:1px solid #cce4f5;font-size:13px;color:#555;">';
			$html .= 'ℹ️ <strong>Conversión automática:</strong> Tu pedido se convertirá de forma segura a <strong>Dólares (USD)</strong> al tipo de cambio de <strong>S/. ' . esc_html( number_format( $rate, 2 ) ) . ' por 1 USD</strong> al redirigirte a PayPal.';
			$html .= '</div>';
			$html .= '</div>';

			return array(
				'title'           => isset( $this->settings['title'] ) ? $this->settings['title'] : 'PayPal (Tarjetas de Crédito / Débito y Saldo)',
				'description'     => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
				'descriptionHtml' => $html,
				'supports'        => array( 'products' ),
			);
		}
	}

	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new WC_Paypal_Peru_Blocks_Support() );
		}
	);
}
