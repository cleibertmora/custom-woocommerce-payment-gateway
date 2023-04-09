<?php

class WC_Pago_Movil_Forma_De_Pago extends WC_Payment_Gateway {

    public function __construct() {
        // Define la información básica del método de pago
        $this->id = 'pago-movil';
        $this->has_fields = true;
        $this->title = 'Pago Móvil (Venezuela)';
        $this->method_title = 'Pago Móvil';
        $this->method_description = 'Permite recibir pagos mediante Pago Móvil.';

        // Define los ajustes del método de pago
        $this->init_form_fields();
        $this->init_settings();

        // Get settings.
		$this->tasa_dia        = $this->get_option( 'tasa_dia' );
		$this->nombre_banco    = $this->get_option( 'nombre_banco' );
		$this->nombre_titular  = $this->get_option( 'nombre_titular' );
		$this->cedula_titular  = $this->get_option( 'cedula_titular' );
		$this->nro_telefono    = $this->get_option( 'nro_telefono' );

        // Define los hooks de WooCommerce necesarios para el método de pago
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_after_checkout_form', array( $this, 'agregar_script_checkout') );
        add_action( 'woocommerce_thankyou', array( $this, 'text_thankyou_page') );

        // add_action('wp_ajax_upload_comprobante_pago_movil', array( $this, 'upload_comprobante_pago_movil') );
        // add_action('wp_ajax_nopriv_upload_comprobante_pago_movil', array( $this, 'upload_comprobante_pago_movil') );
        // add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        // add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

    }

    public function get_title() {
        return $this->title;
    }

    /**
     * Define los ajustes del método de pago
     */

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Habilitar/Inhabilitar',
                'type'    => 'checkbox',
                'label'   => 'Habilitar Pago Móvil',
                'default' => 'yes'
            ),
            'tasa_dia' => array(
                'title' => __('Tasa del día', 'woocommerce'),
                'type' => 'number',
                'description' => __('Ingrese el valor del dolár el día de hoy', 'woocommerce'),
                'custom_attributes' => array(
                    'step' => '0.01',
                    'min' => '0'
                ),
                'default' => '',
            ),
            'nombre_banco' => array(
                'title' => __('Nombre Banco', 'woocommerce'),
                'type' => 'text',
                'description' => __('Ingrese el nombre del banco donde se encuentra la cuenta', 'woocommerce'),
            ),
            'nombre_titular' => array(
                'title' => __('Nombre Titular', 'woocommerce'),
                'type' => 'text',
                'description' => __('Ingrese el nombre del titular de la cuenta', 'woocommerce'),
                'default' => '',
            ),
            'cedula_titular' => array(
                'title' => __('Cédula Titular', 'woocommerce'),
                'type' => 'text',
                'description' => __('Ingrese la cédula del titular de la cuenta', 'woocommerce'),
                'default' => '',
            ),
            'nro_telefono' => array(
                'title' => __('Nro. Teléfono', 'woocommerce'),
                'type' => 'text',
                'description' => __('Ingrese el número de teléfono asociado de la cuenta', 'woocommerce'),
                'default' => '',
            )
        );
    }

    public function agregar_script_checkout() {
        wp_enqueue_script( 'mi-script-checkout', plugins_url( 'checkout.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
    }

    public function payment_fields() {

        $totalCheckout = WC()->cart->get_cart_contents_total();

        $toPay = $this->tasa_dia * $totalCheckout;
        $toPay = number_format( floatval( $toPay ), 2, ',', '.' ) . ' Bs.';

        echo '<div>';

        echo '<div style="background-color:#e6e6e6; border-radius: 8px; padding: 2px 14px 34px;">
            <h5 style="text-align:center"><b>Datos para el pago móvil:</b></h5>
            <ul style="list-style-type: none;">
                <li><b>Banco:</b> '. $this->nombre_banco .'</li>
                <li><b>Titular:</b> '. $this->nombre_titular .'</li>
                <li><b>Documento:</b> '. $this->cedula_titular .'</li>
                <li><b>Nro. Teléfono:</b> '. $this->nro_telefono .'</li>
                <li style="text-align:center; font-size: 1.5rem"><b>A pagar: '. $toPay .'</b></li>
            </ul>
        </div>';

        echo '<div id="custom-payment-form">';
        echo '<h2>' . __('Información del pagador', 'textdomain') . '</h2>';

        woocommerce_form_field('nombre_pago_movil', array(
            'type' => 'text',
            'required' => true,
            'label' => __('Nombre', 'textdomain'),
            'placeholder' => __('Ingrese su nombre completo', 'textdomain'))
            // $checkout->get_value( 'nombre_pago_movil' )
        );

        woocommerce_form_field('cedula_pago_movil', array(
            'type' => 'text',
            'required' => true,
            'label' => __('Cédula', 'textdomain'),
            'placeholder' => __('Ingrese su número de cédula', 'textdomain'))
            // $checkout->get_value( 'nombre_pago_movil' )
        );

        woocommerce_form_field('telefono_pago_movil', array(
            'type' => 'text',
            'required' => true,
            'label' => __('Teléfono', 'textdomain'),
            'placeholder' => __('Ingrese su número de teléfono', 'textdomain'))
            // $checkout->get_value( 'nombre_pago_movil' )
        );

        woocommerce_form_field( 'banco_pago_movil', array(
            'type'        => 'select',
            'label'       => __('Banco:'),
            'required'    => true,
            'options'     => array(
                '' => 'Seleccionar...',
                'BANCO VENEZUELA' => 'BANCO VENEZUELA',
                'BANCO PROVINCIAL' => 'BANCO PROVINCIAL',
                'BANCO MERCANTIL' => 'BANCO MERCANTIL',
                'BANCO OCCIDENTAL DE DESCUENTO' => 'BANCO OCCIDENTAL DE DESCUENTO',
                'BANCO BANESCO' => 'BANCO BANESCO',
                'BANCO SOFITASA' => 'BANCO SOFITASA',
                'BANCO BICENTENARIO' => 'BANCO BICENTENARIO',
                'BANCO CARIBE' => 'BANCO CARIBE',
                'BANCO EXTERIOR' => 'BANCO EXTERIOR',
                'BANCO DEL TESORO' => 'BANCO DEL TESORO',
                'BANCO VENEZOLANO DE CREDITO' => 'BANCO VENEZOLANO DE CREDITO',
                'BANCO CARONI' => 'BANCO CARONI',
                'BANCO PLAZA' => 'BANCO PLAZA',
                'BANCO FONDO COMUN' => 'BANCO FONDO COMUN',
                '100% BANCO' => '100% BANCO',
                'BANCO DEL SUR' => 'BANCO DEL SUR',
                'BANCRECER' => 'BANCRECER',
                'MI BANCO' => 'MI BANCO',
                'BANCO ACTIVO' => 'BANCO ACTIVO',
                'BANCAMIGA' => 'BANCAMIGA',
                'BANPLUS' => 'BANPLUS',
                'BCO FUERZA ARMADA NAC. BOL' => 'BCO FUERZA ARMADA NAC. BOL',
                'BANCO NAC. DE CREDITO' => 'BANCO NAC. DE CREDITO'
            ),
            'default' => '')
            // $checkout->get_value( 'banco_pago_movil' )
        );

        woocommerce_form_field('fecha_pago_movil', array(
            'type' => 'date',
            'required' => true,
            'label' => __('Fecha de Págo', 'textdomain'),
            'placeholder' => __('Ingrese su número de teléfono', 'textdomain'))
            // $checkout->get_value( 'nombre_pago_movil' )
        );

        woocommerce_form_field('transaccion_id_pago_movil', array(
            'type' => 'text',
            'required' => false,
            'label' => __('Referencia', 'textdomain'),
            'placeholder' => __('Ingrese su referencia de pago', 'textdomain')
            // $checkout->get_value( 'transaccion_id_pago_movil' )
        ));

        // echo '<input type="hidden" name="comprobante-url" id="comprobante-url">';

        // echo wp_nonce_field('upload-comprobante-nonce', 'comprobante-pago-movil');

        // echo '<label>
        //         <b>Comprobante de pago</b>
        //     </label>
        //     <input type="file" id="comprobante_pago_movil" name="comprobante_pago_movil">';

    }

    public function validate_fields() {
        if (empty($_POST['nombre_pago_movil'])) {
            wc_add_notice(__('Por favor ingrese su nombre completo', 'textdomain'), 'error');
        }
        if (empty($_POST['cedula_pago_movil'])) {
            wc_add_notice(__('Por favor ingrese su número de cédula', 'textdomain'), 'error');
        }
        if (empty($_POST['telefono_pago_movil'])) {
            wc_add_notice(__('Por favor ingrese su número de teléfono', 'textdomain'), 'error');
        }
        if (empty($_POST['transaccion_id_pago_movil'])) {
            wc_add_notice(__('Por favor ingrese su referencia de pago', 'textdomain'), 'error');
        }
        if (empty($_POST['banco_pago_movil'])) {
            wc_add_notice(__('Por favor seleccione un banco', 'textdomain'), 'error');
        }
        if (empty($_POST['fecha_pago_movil'])) {
            wc_add_notice(__('Por favor seleccione una fecha de pago', 'textdomain'), 'error');
        }
    }

    /**
     * Procesa el pago
     */
    public function process_payment($order_id) {
        global $woocommerce;

        $order = new WC_Order($order_id);

        // Save custom data
        update_post_meta($order_id, 'nombre_pago_movil', $_POST['nombre_pago_movil']);
        update_post_meta($order_id, 'cedula_pago_movil', $_POST['cedula_pago_movil']);
        update_post_meta($order_id, 'telefono_pago_movil', $_POST['telefono_pago_movil']);
        update_post_meta($order_id, 'transaccion_id_pago_movil', $_POST['transaccion_id_pago_movil']);
        update_post_meta($order_id, 'banco_pago_movil', $_POST['banco_pago_movil']);
        update_post_meta($order_id, 'fecha_pago_movil', $_POST['fecha_pago_movil']);

        // Mark order as processing
        $order->update_status(__('processing', 'woocommerce'), __('Pago recibido mediante Pago Móvil.', 'textdomain'));

        // Reduce stock levels
        $order->reduce_order_stock();

        // Empty cart
        $woocommerce->cart->empty_cart();

        // Return thank you page redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    public function upload_comprobante_pago_movil() {
        // Verificar el nonce
        if (!wp_verify_nonce($_POST['nonce'], 'upload-comprobante-pago-movil-nonce')) {
            wp_die('No autorizado.');
        }

        // Subir el archivo
        $attachment_id = media_handle_upload('comprobante_pago_movil', 0);

        // Obtener la URL del archivo
        $attachment_url = wp_get_attachment_url($attachment_id);

        // Devolver la URL del archivo

        wp_send_json( array( 'url_archivo' => $attachment_url ) );
        wp_die(); // Terminar la ejecución del script
    }

    public function text_thankyou_page($order_id)
    {
        $order = wc_get_order($order_id);
        $payment_method = $order->get_payment_method();

        if ($payment_method == $this->id) {
            echo '<h2>Gracias por pagar con '. $this->get_title() .'</h2>';
            echo '<p>Su compra esta en status pendiente, validáremos su pago y nos comunicaremos con usted.</p>';
        }
    }

    /**
     * Muestra la página de agradecimiento después de que se realiza el pago
     */
    public function thankyou_page() {
        // Implementa la página de agradecimiento
    }

    /**
     * Envía instrucciones por correo electrónico al cliente después de realizar el pedido
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        // Implementa las instrucciones por correo electrónico
    }
}
