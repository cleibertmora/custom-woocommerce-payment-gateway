<?php
/**
 * Plugin Name: Págo Móvil Para WooCommerce
 * Description: Registro de Pagó Móvil como método de pago en Venezuela.
 * Version: 1.0.0
 * Author: Cleibert Mora
 * Author URI: https://cleibertmora.com/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// asegurarse de que WooCommerce esté activo
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // agregar una nueva forma de pago
    add_filter( 'woocommerce_payment_gateways', 'agregar_nueva_forma_de_pago' );

    add_action('wp_before_admin_bar_render', 'add_custom_admin_bar_link');

    function add_custom_admin_bar_link() {
        global $wp_admin_bar;
    
        // Agregamos el link a la barra de administración
        $wp_admin_bar->add_menu(array(
            'id' => 'custom-admin-bar-link',
            'title' => 'Pago Móvil',
            'href' => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_pago_movil_forma_de_pago' )
        ));
    }
     
    function agregar_nueva_forma_de_pago( $gateways ) {
        // incluir la clase del gateway de pago
        require_once plugin_dir_path( __FILE__ ) . 'PagoMovil.php';

        // agregar el nuevo gateway de pago a la lista de gateways
        $gateways[] = 'WC_Pago_Movil_Forma_De_Pago';

        return $gateways;
    }

    // cargar la clase del gateway de pago
    add_action( 'plugins_loaded', 'cargar_clase_mi_nueva_forma_de_pago' );
    
    function cargar_clase_mi_nueva_forma_de_pago() {
        require_once plugin_dir_path( __FILE__ ) . 'PagoMovil.php';
    }
}

// Check if woocommerce is activated
add_action( 'admin_init', 'my_plugin_check_dependencies' );

function my_plugin_check_dependencies() {
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        // WooCommerce is not installed or active, show error message
        deactivate_plugins( plugin_basename( __FILE__ ) ); // desactivar el plugin actual
        wp_die( 'La extensión My WooCommerce requiere WooCommerce para funcionar. Por favor, instala y activa WooCommerce antes de activar esta extensión.' );
    }
}

