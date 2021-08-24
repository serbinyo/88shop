<?php
class ControllerExtensionModuleUniReviews extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/uni_reviews');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->document->addStyle('view/stylesheet/unishop.css');

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_reviews', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->cache->delete('unishop.reviews');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['heading_title'] = $this->language->get('heading_title');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
        $data['error_header'] = isset($this->error['module_header']) ? $this->error['module_header'] : '';
		$data['error_order_type'] = isset($this->error['order_type']) ? $this->error['order_type'] : '';
        $data['error_layout'] = isset($this->error['layout']) ? $this->error['layout'] : '';
        $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
		$data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);			
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		$data['name'] = isset($module_info['name']) ? $module_info['name'] : '';
		$data['title'] = isset($module_info['title']) ? $module_info['title'] : '';
		$data['order_type'] = isset($module_info['order_type']) ? $module_info['order_type'] : '';
		
		if (isset($this->request->post['image_width'])) {
			$data['image_width'] = $this->request->post['image_width'];
		} elseif (!empty($module_info)) {
			$data['image_width'] = $module_info['image_width'];
		} else {
			$data['image_width'] = 100;
		}
		
		if (isset($this->request->post['image_height'])) {
			$data['image_height'] = $this->request->post['image_height'];
		} elseif (!empty($module_info)) {
			$data['image_height'] = $module_info['image_height'];
		} else {
			$data['image_height'] = 100;
		}
		
		$data['text_limit'] = isset($module_info['text_limit']) ? $module_info['text_limit'] : 100;
		$data['category_sensitive'] = isset($module_info['category_sensitive']) ? $module_info['category_sensitive'] : '';
		$data['show_all_button'] = isset($module_info['show_all_button']) ? $module_info['show_all_button'] : '';
		$data['show_all_button_link'] = isset($module_info['show_all_button_link']) ? $module_info['show_all_button_link'] : '';
		$data['limit'] = isset($module_info['limit']) ? $module_info['limit'] : 4;
		$data['view_type'] = isset($module_info['view_type']) ? $module_info['view_type'] : '';
		$data['status'] = isset($module_info['status']) ? $module_info['status'] : '';

        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_reviews', $data));
	}
	
	public function install() {	
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		
		$query = $this->db->query("SELECT query FROM `".DB_PREFIX."seo_url` WHERE `keyword` LIKE 'reviews' LIMIT 1");
		if ($query->num_rows == 0) {
			foreach ($languages as $language) {
				$this->db->query("INSERT INTO `".DB_PREFIX . "seo_url` SET store_id = 0, language_id = '".(int)$language['language_id']."', query = 'product/uni_reviews', keyword = 'reviews'");
			}
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_reviews')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}