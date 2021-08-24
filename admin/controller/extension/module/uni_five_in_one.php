<?php
class ControllerExtensionModuleUniFiveInOne extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('extension/module/uni_five_in_one');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/module');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('localisation/language');
		
		$this->document->addStyle('view/stylesheet/unishop.css');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['lang_code'] = $this->config->get('config_admin_language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_five_in_one', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->cache->delete('product.unishop.five');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = [];
		
		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true),
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/uni_five_in_one', 'user_token='.$this->session->data['user_token'], true),
   		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_five_in_one', 'user_token='.$this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_five_in_one', 'user_token='.$this->session->data['user_token'] . '&module_id='.$this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$stores = $this->model_setting_store->getStores();
		
		$data['stores'][] = array(
			'store_id' 	=> 0,
			'name'    	=> $this->config->get('config_name'),
		);
 
    	foreach ($stores as $store) {		
			$data['stores'][] = array(
				'store_id'	=> $store['store_id'],
				'name'     	=> html_entity_decode($store['name'], ENT_QUOTES, 'UTF-8'),
			);
		}
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['tabs'])) {
			$data['tabs'] = $this->request->post['tabs'];
		} elseif (!empty($module_info)) {
			$data['tabs'] = $module_info['tabs'];
		} else {
			$data['tabs'] = '';
		}
		
		foreach($data['stores'] as $store) {
			$data['tab_name'][$store['store_id']] = [
				'latest' => ['title' => $this->language->get('entry_tab_latest')], 
				'special' => ['title' => $this->language->get('entry_tab_special')], 
				'popular' => ['title' => $this->language->get('entry_tab_popular')], 
				'bestseller' => ['title' => $this->language->get('entry_tab_bestseller')], 
				'featured' => ['title' => $this->language->get('entry_tab_featured')]
			];
		}
		
		$data['tab_names'] = $data['tabs'] ? $data['tabs'] : $data['tab_name'];
			
		if (isset($this->request->post['set'])) {
			$data['module'] = $this->request->post['set'];
		} elseif (!empty($module_info['set'])) {
			$data['module']= $module_info['set'];
		} else {
			$data['module'] = [];
		}
		
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		
		foreach($data['stores'] as $store) {
			
			$store_id = $store['store_id'];
			
			if(!isset($data['tab_names'][$store_id])) {
				$data['tab_names'][$store_id] = $data['tab_name'][$store_id];
			}
			
			foreach($data['tab_names'][$store_id] as $key => $tab_name) {
				
				$products = isset($data['module'][$store_id][$key]['product']) ? $data['module'][$store_id][$key]['product'] : [];

				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						$data['module'][$store_id][$key]['products'][] = array(
							'product_id' => $product_info['product_id'],
							'name'       => $product_info['name']
						);
					}
				}
			}
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_five_in_one', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_category_wall')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}