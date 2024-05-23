<?php

if (!defined('ABSPATH')) exit;

require WRP_PATH . 'vendor/autoload.php';

use Automattic\WooCommerce\Client;

add_filter('woocommerce_payment_gateways', 'wrp_add_gateway_class');

function wrp_add_gateway_class($gateways)
{
    $gateways[] = 'WRP_Payment';
    return $gateways;
}

function wrp_exec_gateway()
{
    class WRP_Payment extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'wrp_payment';
            $this->icon = '';
            $this->has_fields = false;
            $this->method_title = 'Woo Payment Redirect';
            $this->method_description = 'Redirect user to checkout store using wrp Payment Redirect';

            $this->supports = array(
                'products'
            );

            $this->init_form_fields();

            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->consumer_key = $this->get_option('consumer_key');
            $this->consumer_secret = $this->get_option('consumer_secret');
            $this->store_url = $this->get_option('store_url');


            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }


        public function create_order($data)
        {
            $client = new Client(
                $this->get_option('store_url'),
                $this->get_option('consumer_key'),
                $this->get_option('consumer_secret'),
                [
                    'version' => 'wc/v3'
                ]
            );

            $order = $client->post('orders', $data);

            return $order;
        }

        public function create_product($data){
            $client = new Client(
                $this->get_option('store_url'),
                $this->get_option('consumer_key'),
                $this->get_option('consumer_secret'),
                [
                    'version' => 'wc/v3'
                ]
            );

            try{
                $product = $client->post('products', $data);
                return $product->id ;
            }catch(error){
                return false ;
            }
        }

        public function delete_product($id){
            $client = new Client(
                $this->get_option('store_url'),
                $this->get_option('consumer_key'),
                $this->get_option('consumer_secret'),
                [
                    'version' => 'wc/v3'
                ]            
            );

            try{
                $removed = $client->delete('products/'.$id, ['force' => true]);

                return $removed->id ? true : false ;
            }catch(error){
                return false ;
            }
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Woo Payment Redirect',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),

                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title of the payment gateway which users see during checkout',
                    'default' => 'Woo Payment Redirect',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout',
                    'default' => 'Redirect to payment page.',
                ),
                'store_url' => array(
                    'title'       => 'Store URL',
                    'type'        => 'text'
                ),
                'consumer_key' => array(
                    'title'       => 'Consumer Key',
                    'type'        => 'text'
                ),
                'consumer_secret' => array(
                    'title'       => 'Consumer Secret',
                    'type'        => 'password',
                )
            );
        }

        public function process_payment($order_id)
        {

            // We need to get the order object from order id ;
            $order = wc_get_order($order_id);
            $line_items = [];
            $product_ids = [];
            foreach($order->get_items() as $item_id => $item){
                $product  = $item->get_product();
                $name = $item->get_name();
                $price = $product->get_price();
                $quantity = $item->get_quantity();
                $image_url = wp_get_attachment_url($product->get_image_id());
                $product_data = [
                    'name' => $name,
                    'type' => 'simple',
                    'regular_price' => $price,
                    'images' => [
                        [
                            'src' => $image_url
                        ]
                    ]
                ];
                
                $product_id = $this->create_product($product_data);
                
                if($product_id){
                    $line_items[] = [
                        'product_id' => $product_id,
                        'quantity' => $quantity,
                    ];

                    $product_ids[] = $product_id;
                }
            }

            if(count($line_items) == 0){
                return array(
                    'result' => 'error'
                );
            }

            $data = [
                'payment_method' => '',
                'payment_method_title' => '',
                'set_paid' => false,
                'billing' => [
                    'first_name' => $order->get_billing_first_name(),
                    'last_name' => $order->get_billing_last_name(),
                    'address_1' => $order->get_billing_address_1(),
                    'address_2' => $order->get_billing_address_2(),
                    'city' => $order->get_billing_city(),
                    'state' => $order->get_billing_state(),
                    'postcode' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                    'email' => $order->get_billing_email(),
                    'phone' => $order->get_billing_phone()
                ],
                'shipping' => [
                    'first_name' => $order->get_shipping_first_name(),
                    'last_name' => $order->get_shipping_last_name(),
                    'address_1' => $order->get_shipping_address_1(),
                    'address_2' => $order->get_shipping_address_2(),
                    'city' => $order->get_shipping_city(),
                    'state' => $order->get_shipping_state(),
                    'postcode' => $order->get_shipping_postcode(),
                    'country' => $order->get_shipping_country(),
                ],
                'line_items' => $line_items,
            ];

            $api_order = $this->create_order($data);

            if ($api_order) {
                $count = 0 ;
                foreach($product_ids as $id){
                    $delete = $this->delete_product($id);
                    if($delete){
                        $count++;
                    }
                }

                if($count == count($product_ids)){
                    $api_order_id = $api_order->id;
                    $api_order_key = $api_order->order_key;
    
                    $payment_url = $this->get_option('store_url') . "/checkout/order-pay/$api_order_id/?pay_for_order=true&key=$api_order_key";

                    $order->reduce_order_stock();
                    WC()->cart->empty_cart();
    
                    return array(
                        'result' => 'success',
                        'redirect' => $payment_url
                    );
                }else{
                    return array(
                        'result' => 'error'
                    );
                }
            } else {
                return array(
                    'result' => 'error',
                );
            }
        }
    }
}

add_action('plugins_loaded', 'wrp_exec_gateway');
