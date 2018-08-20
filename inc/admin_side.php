<?php

add_action('admin_menu', function() {
	$page = add_menu_page(
		'BMW parser',
		'BMW parser', 
		'manage_options',
		'bmw_parser',
		'bmw_parser_admin',
		WCST_PARSER_PLUGIN_DIR_URL.'images/bmw-plugin-icon.png',
		100
	);

	add_action( 'admin_print_styles-' . $page, 'bmw_parser_admin_css' );
	add_action( 'admin_print_scripts-' . $page, 'bmw_parser_admin_js' );
});

// Регистрация скриптов и стилей админки
function bmw_parser_admin_css()
{
	wp_enqueue_style( 'bmw_parser-admin-font', 'https://fonts.googleapis.com/css?family=Roboto:400,500&amp;subset=cyrillic' );
	wp_enqueue_style( 'bmw_parser-admin-css', WCST_PARSER_PLUGIN_DIR_URL . 'css/app-admin.css' );
	// wp_enqueue_style( 'bmw_parserusercss', WCST_PARSER_PLUGIN_DIR_URL . 'css/main.css' );
}

function bmw_parser_admin_js()
{ 
	wp_enqueue_script('bmw_parser-admin-axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js', array(), null, 'in_footer');
	wp_enqueue_script('bmw_parser-admin-vue', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', array(), null, 'in_footer');
	wp_enqueue_script('bmw_parser-admin-app', WCST_PARSER_PLUGIN_DIR_URL . 'js/app-admin.js', array('bmw_parser-admin-vue'), null, 'in_footer');
	// wp_enqueue_script('bmw_parseruserjs', WCST_PARSER_PLUGIN_DIR_URL . 'js/main.js', array('jquery'), null, 'in_footer');
}

function bmw_parser_admin()
{
	echo '
<div id="bmw_parser-admin" class="bmw_parser-admin">
	<input type="text" name="url" placeholder="URL модели" v-model="url">
	<button @click="sendUrl()">Спарсить главную страницу модели</button>

	<table v-if="pagesData != null">
		<tr>
			<td>Текст ссылки</td>
			<td>href</td>
		</tr>
		<tr v-for="pageData in pagesData">
			<td><input type="text" :placeholder="pageData.titlep" v-model="pageData.title"></td>
			<td><input type="text" :placeholder="pageData.slugp" v-model="pageData.slug"></td>
		</tr>
	</table>
	<button v-if="pagesData != null" @click="parseAll()">Спарсить все страницы</button>
</div>
';
}



function pre_parse_run() {
	if ( !empty($_POST['url']) ) {
		include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
		$start_page_html = file_get_html($_POST['url']);
		$simple_menu_items = $start_page_html->find('.simple_menu', 1)->find('.simple_menu_a');

		$i = 0;
		foreach ( $simple_menu_items as $simple_menu_item ) {
			$data[$i]['title'] = $simple_menu_item->innertext;
			$data[$i]['titlep'] = $simple_menu_item->innertext;
			$data[$i]['slug'] = $simple_menu_item->href;
			$data[$i]['slugp'] = $simple_menu_item->href;
			$i++;
		}

		echo json_encode($data);
		exit();
	}
}
add_action( 'wp_ajax_pre_parse_run', 'pre_parse_run' );



function parse_run()
{
	if ( !empty($_POST['url']) ) {
		// include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
		// $start_page_html = file_get_html($_POST['url']);
		// $simple_menu_items = $start_page_html->find('.simple_menu', 1)->find('.simple_menu_a');

		// $url_parts_arr = parse_url($_POST['url']);

		// foreach ( $simple_menu_items as $simple_menu_item ) {
		// 	$html = file_get_contents( $url_parts_arr['scheme'].'://'.$url_parts_arr['host'].'/ru/'.$simple_menu_item->href );
		// 	save_all_page_files($html);
		// 	$page_sctipts = get_page_sctipts($html);
		// 	$urls[] = create_page($html, $simple_menu_item->href, $simple_menu_item->innertext, $page_sctipts);
		// }

		$url_parts_arr = parse_url($_POST['url']);
		
		$pages_data = json_decode( stripslashes( $_POST['pagesdata'] ) );

		foreach ( $pages_data as $page_data ) {
			if ( pathinfo($_POST['url'], PATHINFO_BASENAME ) == $page_data->slugp ) {
				$html = file_get_contents( $url_parts_arr['scheme'].'://'.$url_parts_arr['host'].'/ru/'.$page_data->slugp );
				save_all_page_files($html);
				$page_sctipts = get_page_sctipts($html);

				$parent_id = create_page($html, $page_data->slug, $page_data->title, $page_sctipts);
			}
		}
 
		foreach ( $pages_data as $page_data ) {
			if ( pathinfo($_POST['url'], PATHINFO_BASENAME ) != $page_data->slugp ) {
				$html = file_get_contents( $url_parts_arr['scheme'].'://'.$url_parts_arr['host'].'/ru/'.$page_data->slugp );
				save_all_page_files($html);
				$page_sctipts = get_page_sctipts($html);
				
				$technical_id = create_page($html, $page_data->slug, $page_data->title, $page_sctipts, $parent_id);

				if ( strtolower($page_data->titlep) == strtolower('Технические характеристики') ) {
					include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
					$technical_page_html = file_get_html( $url_parts_arr['scheme'].'://'.$url_parts_arr['host'].'/ru/'.$page_data->slugp );
					$technical_page_items = $technical_page_html->find('.chose_chars_list', 0)->find('a');

					foreach ($technical_page_items as $technical_page_item) {
						$technical_html = file_get_contents( $url_parts_arr['scheme'].'://'.$url_parts_arr['host'].'/ru/'.$technical_page_item->href );
						save_all_page_files($technical_html);
						$technical_sctipts = get_page_sctipts($technical_html);
						create_page($technical_html, $technical_page_item->href, $technical_page_item->innertext, $technical_sctipts, $technical_id);
					}
				}
			}
		}

		echo $parent_id;
		exit();
	}
	else {
		echo 'empty';
		exit();
	}
}
add_action( 'wp_ajax_parse_run', 'parse_run' );



function save_all_page_files($html)
{
	preg_match_all('~<.*img.*src="(.*)"[^>]*>~Uis', $html, $images);

	foreach ($images[1] as $image_url) {

		$image_url_arr = parse_url($image_url);
		$path_arr = pathinfo($image_url_arr['path']);

		if ( !file_exists(WCST_PARSER_WP_UPLOADS_DIR_PATH.$path_arr['dirname']) ) {
			mkdir(WCST_PARSER_WP_UPLOADS_DIR_PATH.$path_arr['dirname'], 0777, true);
		}

		if ( !file_exists(WCST_PARSER_WP_UPLOADS_DIR_PATH.$image_url_arr['path']) ) {
			file_put_contents(WCST_PARSER_WP_UPLOADS_DIR_PATH.$image_url_arr['path'], file_get_contents($image_url));
		}
	}

	return true;
}


function get_page_sctipts($html)
{
	include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
	$html = str_get_html($html);
	$scripts = $html->find('script');
	foreach ($scripts as $script) {
		if ( $script->innertext ) {
			$scripts_concat .= $script->outertext;
		}
	}
	return $scripts_concat;
}


function upgrade_files_url($html)
{
	// preg_match_all('~<.*img.*src="(.*)"[^>]*>~Uis', $html, $images);

	// foreach ( $images[1] as $image_url ) {
	// 	$image_url_arr = parse_url($image_url);
	// 	$path_arr = pathinfo($image_url_arr['path']);
	// 	$new_url = '/wp-content/uploads'.$path_arr['dirname'].'/'.$path_arr['basename'];
	// 	$html = preg_replace('~"'.$image_url.'"~Uis', '"'.$new_url.'"', $html);
	// }

	include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
	$html = str_get_html($html);
	$imgs = $html->find('img');

	foreach ($imgs as $img) {
		$image_url_arr = parse_url($img->src);
		$path_arr = pathinfo($image_url_arr['path']);
		$new_url = '/wp-content/uploads'.$path_arr['dirname'].'/'.$path_arr['basename'];
		$html = preg_replace('~"'.$img->src.'"~Uis', '"'.$new_url.'"', $html);
	}

	return $html;
}


function create_page($html, $slug, $page_title, $page_sctipts, $parent_id = 0)
{
	global $wpdb;

	// preg_match_all('~<.*h3[^>]*>(.*)</h3>~Uis', $html, $title); // $title[1][0]

	include_once(__DIR__.'/simplehtmldom/simple_html_dom.php');
	include_once(__DIR__.'/HtmlFormatter.php');
	$html = str_get_html($html);
	$main_wrap = $html->find(".main-wrap", 0);
	$post_content = upgrade_files_url($main_wrap->outertext);

	// Mihaeu\HtmlFormatter::format($post_content)
	$wpdb->insert( 'wp_posts', array('post_parent' => $parent_id, 'post_title' => $page_title, 'post_name' => $slug, 'post_type' => 'page', 'post_content' => Mihaeu\HtmlFormatter::format($post_content).$page_sctipts) );
	return $wpdb->insert_id;
}