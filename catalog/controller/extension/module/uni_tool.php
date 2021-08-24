<?php
class ControllerExtensionModuleUniTool extends Controller {
	private $uniset;
	
	public function index() {
		$uniset = $this->uniset = $this->config->get('config_unishop2');
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$store_id = (int)$this->config->get('config_store_id');
		
		//$dir_template = 'catalog/view/theme/'.$this->config->get('theme_unishop2_directory').'/';
		$dir_template = 'catalog/view/theme/unishop2/';
		$dir_style = $dir_template.'stylesheet/';
		$dir_script = $dir_template.'js/';
		$dir_font = $dir_template.'fonts/';
		
		
		$data['font_preload'] = [
			'regular' => $dir_font.$uniset['font'].'/'.$uniset['font'].'-regular.woff2',
			'medium'  => $dir_font.$uniset['font'].'/'.$uniset['font'].'-medium.woff2'
		];
		
		$generated_style = $dir_style.'generated.'.(int)$this->config->get('config_store_id').'.css';
		
		$rel = 'stylesheet';
		$media = 'screen';
		
		$uni_styles = [
			['href' => $dir_style.'bootstrap.min.css', 'rel' => $rel, 'media' => $media],
			['href' => $dir_style.$uniset['font'].'.css', 'rel' => $rel, 'media' => $media],
			['href' => $dir_style.'stylesheet.css?v='.$uniset['version'], 'rel' => $rel, 'media' => $media],
			['href' => $dir_style.'font-awesome.min.css', 'rel' => $rel, 'media' => $media],
			['href' => $dir_style.'animate.css', 'rel' => $rel, 'media' => $media],
			['href' => $generated_style.'?v='.$uniset['save_date'], 'rel' => $rel, 'media' => $media],
        ];

		$this->setGeneratedStyle($generated_style);
		
		//user css
		$user_style = $dir_style.'generated-user-style.'.$store_id.'.css';
		
		if(isset($uniset['user_css']) && $uniset['user_css'] != '' && !file_exists($user_style)) {
			file_put_contents($user_style, html_entity_decode($uniset['user_css'], ENT_QUOTES, 'UTF-8'));
		}
		
		if(file_exists($user_style)) {
			$this->document->addStyle($user_style);
		}
		
		if(isset($uniset['custom_style']) && $uniset['custom_style'] != '' && file_exists($dir_style.$uniset['custom_style'])) {
			$this->document->addStyle($dir_style.$uniset['custom_style']);
		}
		
		$styles = array_merge($uni_styles, $this->document->getStyles());
		
		$merged_style = $this->getMergedStyle($styles, $route, $dir_style);
		
		if($merged_style) {
			$data['styles'] = $merged_style;
		} else {
			$data['styles'] = $styles;
		}
		
		$uni_scripts = [
			$dir_script.'jquery-2.2.4.min.js',
			$dir_script.'bootstrap.min.js',
			$dir_script.'common.js',
			$dir_script.'menu-aim.min.js',
			$dir_script.'owl.carousel.min.js',
			$dir_script.'jquery.highlight.min.js',
		];
		
		$scripts = array_merge($uni_scripts, $this->document->getScripts());
		
		$merged_script = $this->getMergedScript($scripts, $route, $dir_script);
		
		if($merged_script) {
			$data['scripts'] = $merged_script;
		} else {
			$data['scripts'] = $scripts;
		}
		
		return $data;
	}
	
	private function getMergedStyle($styles, $route, $dir_style) {
		$uniset = $this->uniset;
		
		if(!isset($uniset['merge_css'])) {
			return false;
		}
		
		$stop_routes = [
			'extension/module/uni_pwa/fallbackPage',
			'checkout/simplecheckout',
			'checkout/uni_checkout',
			'checkout/checkout'
		];
		
		if(in_array($route, $stop_routes)) {
			return false;
		}
		
		$stop_styles = [
			//'catalog/view/theme/unishop2/stylesheet/slideshow.css'
		];
		
		$merged_file = $dir_style.'merged.'.substr(md5(json_encode($styles)), 0, 10).'.min.css';
		
		$files = [];
		
		$results = [['href' => $merged_file.'?v='.$this->uniset['version'], 'rel' => 'stylesheet', 'media' => 'screen']];
		
		foreach($styles as $style) {
			if ($stop_styles && in_array($style['href'], $stop_styles)) {
				$results[] = $style;
			} else {
				$files[] = $style['href'];
			}
		}
		
		if (!file_exists($merged_file)) {
			
			$contents = '';
		
			foreach($files as $filename) {
				if(strpos($filename, 'css?v')) {
					$filename = substr($filename, 0, strpos($filename, 'css?v')+3);
				}
				
				$filename = ltrim($filename, '/');
				
				if(file_exists($filename)) {
					$handle = fopen($filename, "r");
					$contents .= fread($handle, filesize($filename));
					fclose($handle);
				} else {
					$this->log->write('Warning: not found '.$filename);
				}
			}
			
			//stackoverflow.com/questions/15195750/minify-compress-css-with-regex
			//github.com/matthiasmullie/minify
		
			$contents = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);
			$contents = preg_replace('/^\s*/m', '', $contents);
			$contents = preg_replace('/\s*$/m', '', $contents);
			$contents = preg_replace('/\s+/', ' ', $contents);
			$contents = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $contents);
			//$contents = preg_replace('/([\[(:>\+])\s+/', '$1', $contents);
			//$contents = preg_replace('/\s+([\]\)>\+])/', '$1', $contents);
			$contents = preg_replace('/\s+(:)(?![^\}]*\{)/', '$1', $contents);
			
			$contents = trim($contents);
			
			file_put_contents($merged_file, $contents);
		}
		
		return $results;
	}
	
	private function getMergedScript($scripts, $route, $dir_script) {
		$uniset = $this->uniset;
		
		if(!isset($uniset['merge_js'])) {
			return false;
		}
		
		$stop_routes = [
			'checkout/simplecheckout',
			'checkout/uni_checkout',
			'checkout/checkout'
		];
		
		if(in_array($route, $stop_routes)) {
			return false;
		}
		
		$stop_scripts = [
			//'catalog/view/theme/unishop2/js/login-register.js'
		];
		
		$merged_file = $dir_script.'merged.'.substr(md5(json_encode($scripts)), 0, 10).'.min.js';
		
		$files = [];
		
		$results = [$merged_file];
		
		foreach($scripts as $script) {
			if ($stop_scripts && in_array($script, $stop_scripts)) {
				$results[] = $script;
			} else {
				$files[] = $script;
			}
		}
		
		if (!file_exists($merged_file)) {
			
			$contents = '';
			
			$google_min = isset($uniset['merge_js_closure']) ? true : false;
			
			foreach($files as $filename) {
				
				if(strpos($filename, 'js?v')) {
					$filename = substr($filename, 0, strpos($filename, 'js?v')+2);
				}
				
				$filename = ltrim($filename, '/');
				
				if(file_exists($filename)) {
					$handle = fopen($filename, "r");
					$data = fread($handle, filesize($filename));
					fclose($handle);
				
					if($google_min && substr($filename, -6) != 'min.js') {
						$output = $this->GoogleMin($data);
				
						if($output) {
							$data = $output;
						} else {
							$this->log->write('Warning: Google Closure Compiler not compile '.$filename);
						}
					}
				
					$contents .= $data;
				} else {
					$this->log->write('Warning: not found '.$filename);
				}
			}
			
			//github.com/matthiasmullie/minify
			
			$contents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $contents);
			$contents = preg_replace('/^\/\/!.+(?:\r\n|\r|\n)/m', '', $contents);
			$contents = preg_replace('/[^\S\n]+/', ' ', $contents);
			$contents = str_replace(array(" \n", "\n "), "\n", $contents);
			$contents = preg_replace('/\n+/', "\n", $contents);
			$contents = preg_replace('/\breturn\s+(["\'\/\+\-])/', 'return$1', $contents);
			$contents = preg_replace('/\)\s+\{/', '){', $contents);
			$contents = preg_replace('/}\n(else|catch|finally)\b/', '}$1', $contents);
			
			$contents = trim($contents);
			
			file_put_contents($merged_file, $contents);
		}
		
		return $results;
	}
	
	private function GoogleMin($data) {
		$post_data = http_build_query(
			array(
				'compilation_level'	=> 'SIMPLE_OPTIMIZATIONS',
				'js_code' 			=> $data,
				'output_format' 	=> 'text',
				'output_info' 		=> 'compiled_code'
			),
			null,
			'&'
		);
			
		$curl = curl_init('https://closure-compiler.appspot.com/compile');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		$result = curl_exec($curl);
		curl_close($curl);
		
		return ($result !== false && $result != '' && substr($result, 0, 5) != 'Error') ? $result : '';
	}
	
	private function setGeneratedStyle($generated_file) {
		$uniset = $this->uniset;
		
		if(file_exists($generated_file)) {
			return false;
		}
			
		$style = '';
		
		//if background image or background color
		if((isset($uniset['background_image']) && $uniset['background_image'] != '') || (isset($uniset['background_color']) && $uniset['background_color'] != 'fff' && $uniset['background_color'] != 'ffffff')) {
			
			if($uniset['background_image'] != '') {
				$style .= 'body {background-image:url("/image/'.$uniset['background_image'].'")}';
			}
			
			if($uniset['background_color'] != 'ffffff') {
				$style .= 'body {background-color:#'.$uniset['background_color'].'}';
			}
			
			$style .= '@media (min-width:992px) {';
			if($uniset['menu_type'] == 1) {
				$style .= 'header{margin:0 auto;padding:0 5px;background:#fff}';
				$style .= '.top-menu {margin:0 -5px}';
				$style .= 'main{margin:0 auto;padding:20px 5px;background:#fff}';
				$style .= '.footer{margin:0 auto;padding-left:5px;padding-right:5px}';
				$style .= '#subscribe{margin:-15px -5px 15px !important}';
			} else {
				$style .= 'header > .container, footer > .container, .top-menu > .container, .subscribe > .container, .footer__socials-payments .container {padding:0}';
				$style .= 'main{margin:20px auto 30px;background:#fff;border-radius:6px}';
				$style .= 'main > .container {padding:0px 20px}';
				$style .= '.fly-menu > .container{padding:0 20px}';
			}
			$style .= '}';
			
			$style .= '@media (min-width:767px) and (max-width:992px) {header, main {margin:0 10px}}';
		}
		
		if(isset($uniset['topstripe']['status'])) {
			$style .= '.topstripe {color:#'.$uniset['topstripe']['color'].'; background:#'.$uniset['topstripe']['bg'].'}';
			$style .= '.topstripe a {color:#'.$uniset['topstripe']['color'].' !important;text-decoration:underline}';
			$style .= '.topstripe__close {color:#'.$uniset['topstripe']['color'].'}';
		}
		
		if(isset($uniset['pwa']['status'])) {
			$style .= '.pwa-notification {color:#'.$uniset['pwa']['banner']['color'].'; background:#'.$uniset['pwa']['banner']['bg'].'}';
			$style .= '.pwa-notification__install {color:#'.$uniset['pwa']['banner']['color_btn'].'; background:#'.$uniset['pwa']['banner']['color_btn_bg'].'}';
			$style .= '.pwa-notification__close {color:#'.$uniset['pwa']['banner']['color_btn_bg'].'}';
		}
		
		//basic  elements
		$style .= 'body {color:#'.$uniset['text_color'].'}';
		$style .= 'h1 {color:#'.$uniset['h1_color'].'}';
		$style .= 'h2 {color:#'.$uniset['h2_color'].'}';
		$style .= 'h3 {color:#'.$uniset['h3_color'].'}';
		$style .= 'h4 {color:#'.$uniset['h4_color'].'}';
		$style .= 'h5 {color:#'.$uniset['h5_color'].'}';
		$style .= 'div.heading {color:#'.$uniset['h3_heading_color'].'}';
		$style .= 'a, .dropdown-menu li > a i{color:#'.$uniset['a_color'].'}';
		$style .= 'a:hover, a:focus, a:active {color:#'.$uniset['a_color_hover'].'}';
		$style .= '.rating i, .rating sup a {color:#'.$uniset['rating_star_color'].'}';
		//$style .= 'label.input input[type="radio"]:checked + span, label.input input[type="checkbox"]:checked + span {background:#'.$uniset['checkbox_radiobutton_bg'].'}';
		
		$style .= 'label.input input[type="radio"]:checked, label.input input[type="checkbox"]:checked{background:#'.$uniset['checkbox_radiobutton_bg'].'}';
		
		$style .= '.tooltip-inner {color:#'.$uniset['tooltip_color'].';background:#'.$uniset['tooltip_bg'].'}';
		$style .= '.tooltip.top .tooltip-arrow {border-top-color:#'.$uniset['tooltip_bg'].'}';
		$style .= '.tooltip.bottom .tooltip-arrow {border-bottom-color:#'.$uniset['tooltip_bg'].'}';
		$style .= '.tooltip.left .tooltip-arrow {border-left-color:#'.$uniset['tooltip_bg'].'}';
		$style .= '.tooltip.right .tooltip-arrow {border-right-color:#'.$uniset['tooltip_bg'].'}';
			
		$style .= '.form-control.input-warning{border-color:#'.$uniset['text_alert_color'].'}';
		$style .= '.text-danger{color:#'.$uniset['text_alert_color'].'}';
		
		$style .= isset($uniset['menu_type']) && $uniset['menu_type'] == 2 ? '.breadcrumb.col-md-offset-4.col-lg-offset-3{margin-left:0 !important}' : '';
				
		//top menu
		$style .= '.top-menu {background:#'.$uniset['top_menu_bg'].'}';
		$style .= '.top-menu__btn {color:#'.$uniset['top_menu_color'].'}';
		$style .= '.top-menu__btn:hover, #top .open .btn-group__btn {color:#'.$uniset['top_menu_color_hover'].'}';
		$style .= '@media (min-width:992px) {';
		$style .= '.top-links__a {color:#'.$uniset['top_menu_color'].'!important}';
		$style .= '.top-links__a:hover {color:#'.$uniset['top_menu_color_hover'].'!important}';
		$style .= '}';
			
		//search block
		$style .= '.header-search__category-btn {color:#'.$uniset['search_btn_color'].';background:#'.$uniset['search_btn_bg'].'}';
		$style .= '.header-search__btn {color:#'.$uniset['search_input_color'].'}';
		$style .= '.header-search__input{color:#'.$uniset['search_input_color'].'}';
		$style .= '.header-search__input::-webkit-input-placeholder{color:#'.$uniset['search_input_color'].'}';
		$style .= '.header-search__input::-moz-placeholder{color:#'.$uniset['search_input_color'].' }';
		$style .= '.header-search__input:-ms-input-placeholder{color:#'.$uniset['search_input_color'].'}';
		$style .= '.header-search__input:-input-placeholder{color:#'.$uniset['search_input_color'].'}';
			
		//phone block
		$style .= '.header-phones__main{color:#'.$uniset['header']['contacts']['color']['main_phone'].'}';
		$style .= '.header-phones__main:hover{color:#'.$uniset['header']['contacts']['color']['main_phone_hover'].'}';
		$style .= '.header-phones__additional{color:#'.$uniset['a_color'].'}';
		$style .= '.header-phones__additional.selected{color:#'.$uniset['a_color_hover'].'}';
		$style .= '.header-phones__a{color:#'.$uniset['additional_phone_color'].'!important}';
		$style .= '.header-phones__callback{color:#'.$uniset['a_color'].'!important}';
			
		//cart block
		$style .= '.header-cart__icon{color:#'.$uniset['cart_color'].'}';
		$style .= '.header-cart__total-items {color:#'.$uniset['cart_color_total'].';background:#'.$uniset['cart_bg_total'].'}';
		
		//new style
		if($uniset['menu_type'] == 1) {
			$menu_open_color = $uniset['main_menu_color'];
			$menu_open_bg = $uniset['main_menu_bg'];
			$menu_wrapper_bg = $uniset['main_menu_parent_bg'];
		} else {
			$menu_open_color = $uniset['main_menu2_color'];
			$menu_open_bg = $uniset['main_menu2_bg'];
			$menu_wrapper_bg = $uniset['main_menu2_bg'];
		}
		
		$style .= '@media (max-width:992px){';
		$style .= '.menu-wrapper{background:#'.$menu_wrapper_bg.'}';
		$style .= '.menu-open{color:#'.$menu_open_color.';background:#'.$menu_open_bg.'}';
		$style .= '}';
		
		//main menu
		if($uniset['menu_type'] == 1) {
			$style .= '.menu1 {color:#'.$uniset['main_menu_color'].';background:#'.$uniset['main_menu_bg'].'}';
			$style .= '.menu1 .menu__header{color:#'.$uniset['main_menu_color'].'}';
			$style .= '.menu1 .menu__collapse {background:#'.$uniset['main_menu_parent_bg'].'}';
			$style .= '.menu1 .menu__level-1-li:hover {background:#'.$uniset['main_menu_children_bg'].'}';
			$style .= '.menu1 .menu__level-1-a {color:#'.$uniset['main_menu_parent_color'].'}';
			$style .= '.menu1 .menu__level-1-a:hover {color:#'.$uniset['main_menu_parent_color_hover'].'}';
			$style .= '.menu1 .menu__level-1-pm {color:#'.$uniset['main_menu_parent_color'].'}';
			$style .= '.menu1 .menu__level-2 {background:#'.$uniset['main_menu_children_bg'].'}';
			$style .= '.menu1 .menu__level-2-a {color:#'.$uniset['main_menu_children_color'].'}';
			$style .= '.menu1 .menu__level-2-a:hover {color:#'.$uniset['main_menu_children_color_hover'].'}';
			$style .= '.menu1 .menu__level-2-pm {color:#'.$uniset['main_menu_children_color'].'}';
			$style .= '.menu1 .menu__level-3-a {color:#'.$uniset['main_menu_children_color2'].'}';
			$style .= '.menu1 .menu__level-3-a:hover {color:#'.$uniset['main_menu_children_color2_hover'].'}';
			
			$style .= '.menu1 .menu__more {color:#'.$uniset['main_menu_children_color'].'}';
			
			$style .= '@media (min-width:992px) {';
				if((isset($uniset['background_image']) && $uniset['background_image'] != '') || (isset($uniset['background_color']) && $uniset['background_color'] != 'fff' && $uniset['background_color'] != 'ffffff')) {
					$style .= '.menu2.menu-right:before{position:absolute;left:0;width:100px;height:100%;content:"";background:inherit;transform:translateX(-50%)}';
				} else {
					$style .= '.menu {border-radius:0px !important}';
					$style .= '.menu2:before{position:absolute;top:0;left:50%;width:calc(100vw + 320px);height:100%;content:"";background:inherit;transform:translateX(-50%)}';
				}
			$style .= '}';
		}
		
		//main menu second level top position
		if(isset($uniset['main_menu_sec_lev_pos'])) {
			$style .= '@media (min-width:992px){';
			$style .= '.menu:not(.menu2) .menu__level-1-li {position:static}';
			$style .= '.menu:not(.menu2) .menu__level-1-li.has-children:hover {border-right:solid 1px transparent}';
			$style .= '.menu:not(.menu2) .menu__level-2 {top:0;min-height:100%;border-left:0;}';
			$style .= '}';
		}
		
		//main menu type2
		if($uniset['menu_type'] == 2) {
			$style .= '.menu2 {color:#'.$uniset['main_menu2_color'].';background:#'.$uniset['main_menu2_bg'].'}';
			$style .= '.menu2 .menu__level-1-a, .menu2 .menu__level-1-pm, .menu2 .menu__header {color:#'.$uniset['main_menu2_color'].'}';
			$style .= '.menu .menu__level-2 {background:#'.$uniset['main_menu2_children_bg'].'}';
			$style .= '.menu .menu__level-2-a {color:#'.$uniset['main_menu2_children_color'].'}';
			$style .= '.menu2 .menu__level-2-a:hover {color:#'.$uniset['main_menu2_children_color_hover'].'}';
			$style .= '.menu2 .menu__level-2-pm {color:#'.$uniset['main_menu2_children_color'].'}';
			$style .= '.menu2 .menu__level-3-a {color:#'.$uniset['main_menu2_children_color2'].'}';
			$style .= '.menu2 .menu__level-3-a:hover {color:#'.$uniset['main_menu2_children_color2_hover'].'}';
			
			$style .= '.menu2 .menu__more {color:#'.$uniset['main_menu2_children_color'].'}';
	
			$style .= '@media (max-width:992px) {';
			$style .= '.menu2 .menu__collapse {color:#'.$uniset['main_menu2_color'].' !important;background:#'.$uniset['main_menu2_bg'].' !important}';
			$style .= '}';
			$style .= '@media (min-width:992px) {';
			$style .= '.menu2:before{position:absolute;left:50%;width:100vw;height:100%;content:"";background:inherit;transform:translateX(-50%)}';
			$style .= '}';
		}
			
		//custom menu
		$style .= '#custom_menu .nav {background:#'.$uniset['main_menu_parent_bg'].'}';
		$style .= '#custom_menu .nav > li > a, #custom_menu .nav li > .visible-xs i {color:#'.$uniset['main_menu_parent_color'].'}';
		$style .= '#custom_menu .nav > li:hover > a, #custom_menu .nav > li:hover > .visible-xs i {color:#'.$uniset['main_menu_parent_color_hover'].'}';
		$style .= '#custom_menu .nav > li > .dropdown-menu {background:#'.$uniset['main_menu_children_bg'].'}';
		$style .= '#custom_menu .nav > li:hover {background:#'.$uniset['main_menu_children_bg'].'}';
		$style .= '#custom_menu .nav > li.has_chidren:hover:before {background:#'.$uniset['main_menu_children_bg'].'}';
		$style .= '#custom_menu .nav > li ul > li > a {color:#'.$uniset['main_menu_children_color'].'}';
		$style .= '#custom_menu .nav > li ul li ul > li > a {color:#'.$uniset['main_menu_children_color2'].'}';
			
		//other menu
		$style .= '.list-group a, .list-group a:focus {color:#'.$uniset['main_menu_parent_color'].';background:#'.$uniset['main_menu_parent_bg'].'}';
		$style .= '.list-group a.active, .list-group a.active:hover, .list-group a.active:focus, .list-group a:hover {color:#'.$uniset['main_menu_parent_color_hover'].';background:#'.$uniset['main_menu_children_bg'].'}';
			
		//right menu
		$style .= '.menu-right  {background:#'.$uniset['right_menu']['bg'].'}';
		$style .= '.menu-right .menu__level-1-a{color:#'.$uniset['right_menu']['col'].'}';
		$style .= '.menu-right .menu__level-1-a:hover {color:#'.$uniset['right_menu']['col_hov'].'}';
		$style .= '.menu-right .menu__level-2 {background:#'.$uniset['right_menu']['child_bg'].'}';
		$style .= '.menu-right .menu__level-2-a {color:#'.$uniset['right_menu']['child_col'].'}';
		$style .= '.menu-right .menu__level-2-a:hover {color:#'.$uniset['right_menu']['child_col_hov'].'}';
		$style .= '.menu-right .menu__level-3-a {color:#'.$uniset['right_menu']['child2_col'].'}';
		$style .= '.menu-right .menu__level-3-a:hover {color:#'.$uniset['right_menu']['child2_col_hov'].'}';
		
		//breadcrumb
		if(isset($uniset['breadcrumbs']['hide']['mobile'])) {
			$style .= '@media (max-width:768px) {';
			$style .= '.breadcrumb.mobile li:not(:first-child):not(:last-child) {display:none}';
			$style .= '}';
		}
		
		//buttons
		$style .= '.btn-default {color:#'.$uniset['btn_default_color'].';background:#'.$uniset['btn_default_bg'].'}';
		$style .= '.btn-default:hover, .btn.btn-default:focus {color:#'.$uniset['btn_default_color_hover'].';background:#'.$uniset['btn_default_bg_hover'].'}';
		$style .= '.btn-primary {color:#'.$uniset['btn_primary_color'].';background:#'.$uniset['btn_primary_bg'].'}';
		$style .= '.btn-primary:hover, .btn.btn-primary:focus {color:#'.$uniset['btn_primary_color_hover'].';background:#'.$uniset['btn_primary_bg_hover'].'}';
		$style .= '.btn-danger {color:#'.$uniset['btn_danger_color'].';background:#'.$uniset['btn_danger_bg'].'}';
		$style .= '.btn-danger:hover, .btn.btn-danger:focus {color:#'.$uniset['btn_danger_color_hover'].';background:#'.$uniset['btn_danger_bg_hover'].'}';
		
		//tabs
		$style .= '.nav-tabs > li > a {color:#'.$uniset['a_color'].'}';
		$style .= '.nav-tabs > li.active > a, .nav-tabs > li.active >a:focus, .nav-tabs > li.active > a:hover {color:#'.$uniset['a_color_hover'].'}';
		
		if(isset($uniset['tabs']['mobile']['without_scroll'])) {
			$style .= '@media (max-width:767px){';
			$style .= '.nav-tabs{flex-wrap:wrap;padding:0 5px}';
			$style .= '.product-page-tabs{flex-direction:column}';
			$style .= '.nav-tabs li {height:40px;padding:0 10px 0}';
			$style .= '.product-page-tabs li:not(:first-child){border-top:solid 1px rgba(0, 0, 0, .07)}';
			$style .= '.product-page-tabs li a:after{display:none}';
			$style .= '.nav-tabs .uni-badge {position:absolute;right:0;top:50%;transform:translateY(-50%)}';
			$style .= '}';
		} else {
			$style .= '.nav-tabs{overflow-x:auto;overflow-y:hidden}';
			$style .= '@media (max-width:767px){';
			$style .= '.nav-tabs{margin-left:-15px;margin-right:-15px}';
			$style .= '.product-page-tabs {position:sticky;top:0;z-index:1060}';
			$style .= '}';
		}
		
		//special timer
		$style .= '.uni-timer:before {position:absolute;top:0;left:0;width:100%;height:100%;content:"";background:#'.$uniset['special_timer_bg'].';opacity:.8}';
		$style .= '.uni-timer__text {color:#'.$uniset['special_timer_text_color'].'}';
		$style .= '.uni-timer__digit, .uni-timer__colon, .product-page__timer-heading {color:#'.$uniset['special_timer_color'].'}';
		
		//stock indicator
		if(isset($uniset['show_stock_indicator']) && $uniset['show_stock_indicator'] > 0) {
			if($uniset['show_stock_indicator'] == 1) {
				$style .= '.qty-indicator__percent.percent-5 {background:#'.$uniset['stock_i_c_5'].'}';
				$style .= '.qty-indicator__percent.percent-4 {background:#'.$uniset['stock_i_c_4'].'}';
				$style .= '.qty-indicator__percent.percent-3 {background:#'.$uniset['stock_i_c_3'].'}';
				$style .= '.qty-indicator__percent.percent-2 {background:#'.$uniset['stock_i_c_2'].'}';
				$style .= '.qty-indicator__percent.percent-1 {background:#'.$uniset['stock_i_c_1'].'}';
				$style .= '.qty-indicator__bar, .qty-indicator__percent.percent-0 {background:#'.$uniset['stock_i_c_0'].'}';
			} else {
				$style .= '.qty-indicator__text.text-5 {color:#'.$uniset['stock_i_c_5'].'}';
				$style .= '.qty-indicator__text.text-4 {color:#'.$uniset['stock_i_c_4'].'}';
				$style .= '.qty-indicator__text.text-3 {color:#'.$uniset['stock_i_c_3'].'}';
				$style .= '.qty-indicator__text.text-2 {color:#'.$uniset['stock_i_c_2'].'}';
				$style .= '.qty-indicator__text.text-1, .qty-indicator__text.text-0 {color:#'.$uniset['stock_i_c_1'].'}';
			}
		}
		
		//fly menu
		$style .= '.fly-menu__block {background:#'.$uniset['fly_menu']['bg'].'}';
		$style .= '.fly-menu__phone, .fly-menu__icon {color:#'.$uniset['fly_menu']['color'].'}';
		$style .= '.fly-menu__total {color:#'.$uniset['cart_color_total'].';background:#'.$uniset['cart_bg_total'].'}';
		
		if($uniset['menu_type'] == 1) {
			$style .= '.fly-menu .menu__header{color:#'.$uniset['main_menu_color'].';background:#'.$uniset['main_menu_bg'].'}';
		}
		
		if($uniset['menu_type'] == 2) {
			$style .= '@media (min-width:992px) {';
			$style .= '#fly-menu .menu__header, #fly-menu .menu__collapse {color:#'.$uniset['main_menu2_color'].' !important;background:#'.$uniset['main_menu2_bg'].' !important}';
			$style .= '#fly-menu .menu__level-1-li:hover{background:rgba(0, 0, 0, 0.06) !important}';
			$style .= '#fly-menu .menu__level-1-a, #fly-menu .menu__level-1-pm  {color:#'.$uniset['main_menu2_color'].'}';
			$style .= '#fly-menu .menu__level-2-a {color:#'.$uniset['main_menu2_children_color'].'}';
			$style .= '#fly-menu .menu__level-2-a:hover {color:#'.$uniset['main_menu2_children_color_hover'].'}';
			$style .= '#fly-menu .menu__level-3-a {color:#'.$uniset['main_menu2_children_color2'].'}';
			$style .= '#fly-menu .menu__level-3-a:hover {color:#'.$uniset['main_menu2_children_color2_hover'].'}';
			$style .= '#fly-menu .menu__more {color:#'.$uniset['main_menu2_children_color'].'}';
			$style .= '}';
		}
			
		//slideshow
		$style .= '.swiper-viewport .title{color:#'.$uniset['slideshow_title_color'].';background:#'.$uniset['slideshow_title_bg'].'}';
		$style .= '.swiper-viewport .swiper-pager .swiper-button-next:before, .swiper-viewport .swiper-pager .swiper-button-prev:before {color:#'.$uniset['slideshow_pagination_bg_active'].' !important}';
		$style .= isset($uniset['hide_slideshow_title']) ? '.swiper-viewport .title{display:none}' : '';
		$style .= '.swiper-viewport .swiper-pagination .swiper-pagination-bullet{background:#'.$uniset['slideshow_pagination_bg'].' !important}';
		$style .= '.swiper-viewport .swiper-pagination .swiper-pagination-bullet-active{background:#'.$uniset['slideshow_pagination_bg_active'].' !important}';
		
		//carousel
		$style .= '.owl-carousel .owl-dots .owl-dot span {background:#'.$uniset['carousel_pagination_bg'].'}';
		$style .= '.owl-carousel .owl-dots .owl-dot.active span {background:#'.$uniset['carousel_pagination_bg_active'].'}';
		$style .= '.owl-carousel .owl-nav button {color:#'.$uniset['slideshow_pagination_bg_active'].'}';
		
		//unislideshow
		$style .= '.uni-slideshow__title{color:#'.$uniset['unislideshow_title_color'].'}';
		$style .= '.uni-slideshow__text{color:#'.$uniset['unislideshow_text_color'].'}';
		$style .= '.uni-slideshow__btn, .uni-slideshow__btn:hover, .uni-slideshow__btn:focus{color:#'.$uniset['unislideshow_button_color'].';background:#'.$uniset['unislideshow_button_bg'].'}';
		$style .= '.uni-slideshow .owl-nav button {color:#'.$uniset['unislideshow_nav_bg_active'].'}';
		$style .= '.uni-slideshow .owl-dots .owl-dot span{background:#'.$uniset['unislideshow_nav_bg'].'}';
		$style .= '.uni-slideshow .owl-dots .owl-dot.active span{background:#'.$uniset['unislideshow_nav_bg_active'].'}';

		//banners
		$style .= '.uni-banner__item:hover .btn-primary{color:#'.$uniset['btn_primary_color_hover'].';background:#'.$uniset['btn_primary_bg_hover'].'}';
			
		//home text banners
		$style .= '.home-banner__item {background:#'.$uniset['home_banners_bg'].'}';
		$style .= '.home-banner__icon {color:#'.$uniset['home_banners_icon_color'].'}';
		$style .= '.home-banner__text {color:#'.$uniset['home_banners_text_color'].'}';
		
		//cat description
		$style .= $uniset['catalog']['cat_description']['position'] == 'bottom' ? '.category-page.category-info, .manufacturer-page.category-info{display:none}' : '';
		$style .= $uniset['catalog']['cat_description']['height'] > 0 ? '.category-page.category-info, .manufacturer-page.category-info{height:'.$uniset['catalog']['cat_description']['height'].'px}' : '';
			
		//product-thumb
		$style .= '.product-thumb__name{color:#'.$uniset['product_thumb_h4_color'].'}';
		$style .= '.product-thumb__name:hover{color:#'.$uniset['product_thumb_h4_color_hover'].'}';
		$style .= '.product-thumb__attribute-value{color:#'.$uniset['text_color'].'}';
		$style .= '.product-thumb__addit-dot.active{background:#'.$uniset['a_color'].'}';
		
		$style .= '@media (min-width:992px) {';
		
		if(isset($uniset['catalog']['description_hover'])) {
			$style .= 'body:not(.touch-support) .product-thumb .description {display:none}';
			$style .= 'body:not(.touch-support) .product-thumb .attribute {display:block}';
			$style .= '.product-thumb.hover .description{height:100% !important}';
		}
		
		if(isset($uniset['catalog']['attr_hover'])) {
			$style .= 'body:not(.touch-support) .product-thumb .attribute {display:none}';
			$style .= '.product-thumb.hover .attribute{height:100% !important}';
		}
		
		if(isset($uniset['catalog']['option_hover'])) {
			$style .= 'body:not(.touch-support) .product-thumb .option {display:none}';
			$style .= '.product-thumb.hover .option{height:100% !important}';
		}
		
		$style .= '}';
		
		//product
		$style .= '.product-page__image-addit-img.selected {border-color:#'.$uniset['a_color'].'}';
		$style .= '.quick-order__image-additional-img.selected {border-color:#'.$uniset['a_color'].'}';
			
		$style .= '.option select{color:#'.$uniset['options_color'].'}';
		$style .= '.option__name{color:#'.$uniset['options_color'].';background:#'.$uniset['options_bg'].'}';
		$style .= '.option__name:hover{border:solid 1px #'.$uniset['options_bg_active'].' !important}';
		$style .= '.option input:checked + .option__name{color:#'.$uniset['options_color_active'].';background:#'.$uniset['options_bg_active'].'}';
		$style .= '.option__img:hover, .option input:checked + .option__img{border-color:#'.$uniset['options_bg_active'].'}';
		$style .= '.option__popup.module{width:'.$uniset['options_popup_img_width'].'px}';
		$style .= '.option__popup.product{width:'.$uniset['options_popup_img_width_p'].'px}';
		$style .= '.option__popup.quick-order{width:'.$uniset['options_popup_img_width_q'].'px}';
			
		//price
		$style .= '.price {color:#'.$uniset['price_color'].'}';
		$style .= '.price .price-old {color:#'.$uniset['price_color_old'].'}';
		$style .= '.price .price-new {color:#'.$uniset['price_color_new'].'}';
			
		//cart btn
		$style .= '.add_to_cart {color:#'.$uniset['cart_btn_color'].';background:#'.$uniset['cart_btn_bg'].'}';
		$style .= '.add_to_cart:hover, .add_to_cart:focus, .add_to_cart:active {color:#'.$uniset['cart_btn_color_hover'].';background:#'.$uniset['cart_btn_bg_hover'].'}';
		$style .= '.add_to_cart.in_cart, .add_to_cart.in_cart:hover, .add_to_cart.in_cart:focus, .add_to_cart.in_cart:active{color:#'.$uniset['cart_btn_color_incart'].';background:#'.$uniset['cart_btn_bg_incart'].'}';
		$style .= '.add_to_cart.qty-0, .add_to_cart.qty-0:hover, .add_to_cart.qty-0:focus, .add_to_cart.qty-0:active{color:#'.$uniset['cart_btn_color_disabled'].';background:#'.$uniset['cart_btn_bg_disabled'].'}';	
		$style .= '.add_to_cart.disabled, .add_to_cart.disabled:hover, .add_to_cart.disabled:focus, .add_to_cart.disabled:active{color:#'.$uniset['cart_btn_color_disabled'].';background:#'.$uniset['cart_btn_bg_disabled'].'}';				
			
		//quick order btn
		$style .= '.btn.quick-order {color:#'.$uniset['quick_order_btn_color'].';background:#'.$uniset['quick_order_btn_bg'].'}';
		$style .= '.btn.quick-order:hover, .btn.quick-order:focus, .btn.quick-order:active {color:#'.$uniset['quick_order_btn_color_hover'].';background:#'.$uniset['quick_order_btn_bg_hover'].'}';
		$style .= isset($uniset['show_quick_order_always']) ? '.product-thumb__quick-order {opacity:1}' : '';
			
		//wishlist&compare btn
		$style .= '.wishlist, .wishlist a {color:#'.$uniset['wishlist']['btn_color'].'}';
		$style .= '.wishlist:hover, .wishlist a:hover, .wishlist.active{color:#'.$uniset['wishlist']['btn_color_hover'].'}';
		$style .= '.compare, .compare a {color:#'.$uniset['compare']['btn_color'].'}';
		$style .= '.compare:hover, .compare a:hover, .compare.active{color:#'.$uniset['compare']['btn_color_hover'].'}';
			
		//stickers
		$style .= '.sticker__item.reward {color:#'.$uniset['sticker_reward_text_color'].';background:#'.$uniset['sticker_reward_background_color'].'}';
		$style .= '.sticker__item.reward:before {border-top-color:#'.$uniset['sticker_reward_background_color'].';border-right-color:#'.$uniset['sticker_reward_background_color'].'}';
		$style .= '.sticker__item.special {color:#'.$uniset['sticker_special_text_color'].';background:#'.$uniset['sticker_special_background_color'].'}';
		$style .= '.sticker__item.special:before {border-top-color:#'.$uniset['sticker_special_background_color'].';border-right-color:#'.$uniset['sticker_special_background_color'].'}';
		$style .= '.sticker__item.bestseller {color:#'.$uniset['sticker_bestseller_text_color'].';background:#'.$uniset['sticker_bestseller_background_color'].'}';
		$style .= '.sticker__item.bestseller:before {border-top-color:#'.$uniset['sticker_bestseller_background_color'].';border-right-color:#'.$uniset['sticker_bestseller_background_color'].'}';
		$style .= '.sticker__item.new {color:#'.$uniset['sticker_new_text_color'].';background:#'.$uniset['sticker_new_background_color'].'}';
		$style .= '.sticker__item.new:before {border-top-color:#'.$uniset['sticker_new_background_color'].';border-right-color:#'.$uniset['sticker_new_background_color'].'}';
		$style .= '.sticker__item.sku {color:#'.$uniset['sticker_sku_text_color'].';background:#'.$uniset['sticker_sku_background_color'].'}';
		$style .= '.sticker__item.sku:before {border-top-color:#'.$uniset['sticker_sku_background_color'].';border-right-color:#'.$uniset['sticker_sku_background_color'].'}';
		$style .= '.sticker__item.upc {color:#'.$uniset['sticker_upc_text_color'].';background:#'.$uniset['sticker_upc_background_color'].'}';
		$style .= '.sticker__item.upc:before {border-top-color:#'.$uniset['sticker_upc_background_color'].';border-right-color:#'.$uniset['sticker_upc_background_color'].'}';
		$style .= '.sticker__item.ean {color:#'.$uniset['sticker_ean_text_color'].';background:#'.$uniset['sticker_ean_background_color'].'}';
		$style .= '.sticker__item.ean:before {border-top-color:#'.$uniset['sticker_ean_background_color'].';border-right-color:#'.$uniset['sticker_ean_background_color'].'}';
		$style .= '.sticker__item.jan {color:#'.$uniset['sticker_jan_text_color'].';background:#'.$uniset['sticker_jan_background_color'].'}';
		$style .= '.sticker__item.jan:before {border-top-color:#'.$uniset['sticker_jan_background_color'].';border-right-color:#'.$uniset['sticker_jan_background_color'].'}';
		$style .= '.sticker__item.isbn {color:#'.$uniset['sticker_isbn_text_color'].';background:#'.$uniset['sticker_isbn_background_color'].'}';
		$style .= '.sticker__item.isbn:before {border-top-color:#'.$uniset['sticker_isbn_background_color'].';border-right-color:#'.$uniset['sticker_isbn_background_color'].'}';
		$style .= '.sticker__item.mpn {color:#'.$uniset['sticker_mpn_text_color'].';background:#'.$uniset['sticker_mpn_background_color'].'}';
		$style .= '.sticker__item.mpn:before {border-top-color:#'.$uniset['sticker_mpn_background_color'].';border-right-color:#'.$uniset['sticker_mpn_background_color'].'}';
			
		//product text banners
		$style .= '.product-banner__item {background:#'.$uniset['product_banners_bg'].'}';
		$style .= '.product-banner__icon {color:#'.$uniset['product_banners_icon_color'].'}';
		$style .= '.product-banner__text {color:#'.$uniset['product_banners_text_color'].'}';
		
		//pagination
		$style .= '.pagination li a, .pagination li a:hover, .pagination li a:visited{color:#'.$uniset['pagination_color'].';background:#'.$uniset['pagination_bg'].'}';
		$style .= '.pagination li.active span, .pagination li.active span:hover, .pagination li.active span:focus{color:#'.$uniset['pagination_color_active'].';background:#'.$uniset['pagination_bg_active'].'}';
			
		//footer
		$style .= 'footer{background:#'.$uniset['footer_bg'].'}';
		$style .= 'footer h5{color:#'.$uniset['footer_h5_color'].'}';
		$style .= 'footer, footer a, footer a:hover, footer a:visited{color:#'.$uniset['footer_text_color'].'}';
		
		//subscribe
		$style .= '.subscribe__info {color:#'.$uniset['subscribe_text_color'].'}';
		$style .= '.subscribe__info div {color:#'.$uniset['subscribe_points_color'].'}';
		$style .= '.subscribe__input {color:#'.$uniset['subscribe_input_color'].';background:#'.$uniset['subscribe_input_bg'].'}';
		$style .= '.subscribe__input::-webkit-input-placeholder{color:#'.$uniset['subscribe_input_color'].'}';
		$style .= '.subscribe__input::-moz-placeholder{color:#'.$uniset['subscribe_input_color'].' }';
		$style .= '.subscribe__input:-ms-input-placeholder{color:#'.$uniset['subscribe_input_color'].'}';
		$style .= '.subscribe__input:-input-placeholder{color:#'.$uniset['subscribe_input_color'].'}';
		$style .= '.subscribe__btn, .subscribe__btn:hover {color:#'.$uniset['subscribe_button_color'].' !important;background:#'.$uniset['subscribe_button_bg'].' !important}';
		
		//catwall
		$style .= '.category-wall__title a{color:#'.$uniset['a_color_hover'].'}';
		$style .= '.category-wall__title a:hover{color:#'.$uniset['a_color'].'}';
		$style .= '.category-wall__more{color:#'.$uniset['a_color_hover'].'}';
		
		//fly wishlist & compare
		$style .= '.fly-block__wishlist, .fly-block__wishlist-total {color:#'.$uniset['wishlist']['fly_btn_color'].';background:#'.$uniset['wishlist']['fly_btn_bg'].'}';
		$style .= '.fly-block__compare, .fly-block__compare-total {color:#'.$uniset['compare']['fly_btn_color'].';background:#'.$uniset['compare']['fly_btn_bg'].'}';
		
		//fly callback button
		$style .= '.fly-block__callback {color:#'.$uniset['fly_callback_color'].';background:#'.$uniset['fly_callback_bg'].'}';
		$style .= '.fly-block__callback:before, .fly-block__callback:after {border:solid 1px;border-color:#'.$uniset['fly_callback_bg'].' transparent}';
		$style .= isset($uniset['hide_fly_callback']) ? '@media (max-width:767px){.fly-block__callback {display:none}}' : '';
		
		//notification window
		$notification = isset($uniset['notification']) ? $uniset['notification'] : [];
		if($notification) {
			$style .= '.notification .modal-body {background:#'.$notification['bg'].'}';
			$style .= '.notification.fixed:before {background:#'.$notification['bg'].';opacity:.8}';
			$style .= '.notification__text {color:#'.$notification['color'].'}';
			$style .= '.notification__button.cancel {color:#'.$notification['color'].'}';
		}
		
		//manufacturer module
		if($uniset['menu_type'] == 1) {
			$style .= '#manufacturer_module .heading, #manufacturer_module .heading:after {color:#'.$uniset['main_menu_color'].' !important;background:#'.$uniset['main_menu_bg'].' !important}';
		} else {
			$style .= '#manufacturer_module .heading, #manufacturer_module .heading:after {color:#'.$uniset['main_menu2_color'].' !important;background:#'.$uniset['main_menu2_bg'].' !important}';
		}
		
		//ocfilter
		if($this->config->get('module_ocfilter_status')) {
			$style .= '.noUi-horizontal .noUi-handle {background:#'.$uniset['checkbox_radiobutton_bg'].'}';
		}
		
		//alerts
		$style .= '.alert-success{color:#'.$uniset['alert']['success']['color'].';background:#'.$uniset['alert']['success']['bg'].'}';
		$style .= '.alert-success a{color:#'.$uniset['alert']['success']['color'].'}';
		$style .= '.alert-warning{color:#'.$uniset['alert']['warning']['color'].';background:#'.$uniset['alert']['warning']['bg'].'}';
		$style .= '.alert-warning a{color:#'.$uniset['alert']['warning']['color'].'}';
		$style .= '.alert-danger{color:#'.$uniset['alert']['danger']['color'].';background:#'.$uniset['alert']['danger']['bg'].'}';
		$style .= '.alert-danger a{color:#'.$uniset['alert']['danger']['color'].'}';
		
		//blur on hover menu
		if($uniset['main_menu_blur'] == 1) {
			$style .= '.blur > *{filter:blur(2px);-webkit-filter:blur(2px)}';
			$style .= '#top:after {display:block;position:fixed;z-index:99;top:0;bottom:0;left:0;width:100%;content:"";background:#fff;visibility:hidden;opacity:0;transition:opacity linear .1s}';
			$style .= '#top.blur:after{visibility:visible;opacity:.5}';
		} elseif($uniset['main_menu_blur'] == 2) {
			$style .= '.blur > *{filter:blur(2px);-webkit-filter:blur(2px)}';
			$style .= '#top:after {display:block;position:fixed;z-index:99;top:0;bottom:0;left:0;width:100%;content:"";background:#000;visibility:hidden;opacity:0;transition:opacity linear .1s}';
			$style .= '#top.blur:after{visibility:visible;opacity:.5}';
		}
		
		//blur on popup show
		if(isset($uniset['popup_blur'])) {
			$style .= 'body.modal-open header, body.modal-open main, body.modal-open footer{filter:blur(2px)}';
		}
		
		//blur on popup img show
		if(isset($uniset['popup_img_blur'])) {
			$style .= 'body.magnific-open > *:not(.mfp-wrap){filter:blur(2px);transition:.1s}';
		}
			
		file_put_contents($generated_file, $style);
	}
}
?>