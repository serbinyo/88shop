<?php  
class ControllerExtensionModuleUniGallery extends Controller {
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
	
		$this->load->model('extension/module/uni_gallery');
		$this->load->model('tool/image');
		
		$this->load->language('product/uni_gallery');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');
		
		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/gallery.css');
		
		$data['shop_name'] = $this->config->get('config_name');
		
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$menu_schema = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		$data['menu_expanded'] = ($uniset['menu_type'] == 1 && in_array($route, $menu_schema)) ? true : false;
		
		$data['breadcrumbs'] = [];

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/gallery'),
   		);
		
		$data['uni_popup_img_effect_in'] = isset($uniset['popup_img_effect_in']) ? 'animated '.$uniset['popup_img_effect_in'] : false;
		$data['uni_popup_img_effect_out'] = isset($uniset['popup_img_effect_out']) ? 'animated '.$uniset['popup_img_effect_out'] : false;
		
		$gallery_id = isset($this->request->get['gallery_id']) ? $this->request->get['gallery_id'] : '';
	
		$gallerys = $this->model_extension_module_uni_gallery->getGallerys();
		
		if($gallerys) {
			foreach ($gallerys as $gallery) {
				$images = array();
		
				$results = $this->model_extension_module_uni_gallery->getGallery($gallery['gallery_id']);
		
				foreach ($results as $result) {
					if (file_exists(DIR_IMAGE . $result['image'])) {
						$images[] = array(
							'title' => $result['title'],
							'link'  => $result['link'],
							'image' => $this->model_tool_image->resize($result['image'], 320, 240),
							'popup' => $this->model_tool_image->resize($result['image'], 1200, 800)
						);
					}
				}
		
				$data['gallerys'][] = array(
					'name' 		=> $gallery['name'],
					'images'    => $images,
				);
			}
				
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->response->setOutput($this->load->view('extension/module/uni_gallery', $data));

		} else {
			$this->document->setTitle($this->language->get('text_error'));
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link('common/home');
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
?>