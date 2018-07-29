<?php

add_action('init', 'chats_user_init');
function chats_user_init() {
	add_action('wp_footer', 'chats_app_tag', 0);
}

function chats_app_tag() {
	echo '
<div id="chats-user">
	
</div>
';
}

// Регистрация скриптов и стилей
function chats_user_css_js($value='')
{
	wp_enqueue_script('chats-user-axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js', array(), null, 'in_footer');
	wp_enqueue_script('chats-user-vue', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', array(), null, 'in_footer');
	wp_enqueue_script('chats-user-app', CHATS_PLUGIN_DIR_URL . 'js/app-user.js', array('chats-user-vue'), null, 'in_footer');
	wp_enqueue_style( 'chats-user-css', CHATS_PLUGIN_DIR_URL . 'css/app-user.css' );
	wp_enqueue_style( 'chats-user-font', 'https://fonts.googleapis.com/css?family=Roboto:400,500&amp;subset=cyrillic' );
	// wp_enqueue_style( 'chatsusercss', CHATS_PLUGIN_DIR_URL . 'css/main.css' );
	// wp_enqueue_script('chatsuserjs', CHATS_PLUGIN_DIR_URL . 'js/main.js', array('jquery'), null, 'in_footer');

	wp_localize_script( 'chats-user-app', 'chats_ajax', array('url' => admin_url('admin-ajax.php')));
}
add_action( 'wp_enqueue_scripts', 'chats_user_css_js' );



function get_cart_items()
{
	global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $woocommerce->cart->set_quantity( '182be0c5cdcd5072bb1864cdee4d3d6e', 2, true );
	foreach($items as $item => $values) { 
		$_product =  wc_get_product( $values['data']->get_id()); 
		echo "<b>".$_product->get_title().'</b>  <br> Quantity: '.$values['quantity'].'<br>';
		$price = get_post_meta($values['product_id'] , '_price', true);
		echo $values['product_id'].'<br>';
		echo $values['key'].'<br>';
		echo "  Price: ".$price."<br>";
	} 
    exit();
}
add_action( 'wp_ajax_get_cart_items', 'get_cart_items' );
add_action( 'wp_ajax_nopriv_get_cart_items', 'get_cart_items' );

function get_plus()
{
	global $woocommerce;
    $items = $woocommerce->cart->set_quantity( 'e369853df766fa44e1ed0ff613f563bd', 5, true );
    exit();
}
add_action( 'wp_ajax_get_plus', 'get_plus' );
add_action( 'wp_ajax_nopriv_get_plus', 'get_plus' );