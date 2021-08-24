<?php
class ControllerExtensionModuleUniCategoryWall extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('extension/module/uni_category_wall');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/module');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('extension/module/uni_category_wall');
		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_category_wall', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->cache->delete('category.unishop');
			
			$this->session->data['success'] = $this->language->get('text_success');
			
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
			'href'      => $this->url->link('extension/module/uni_category_wall', 'user_token='.$this->session->data['user_token'], true),
   		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_category_wall', 'user_token='.$this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_category_wall', 'user_token='.$this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);
		
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
		
		$data['categories'] = [];
		
		$filter_data = array(
			'sort'        => 'name',
			'order'       => 'ASC'
		);

		$store = $categories = [];
		
		foreach($data['stores'] as $store) {
		
			$categories = $this->model_extension_module_uni_category_wall->getCategories(0, $store['store_id']);
		
			foreach($categories as $category) {
			
				$childs = $this->model_extension_module_uni_category_wall->getCategories($category['category_id'], $store['store_id']);
			
				$data['childs'] = [];
			
				foreach($childs as $child) {
					$data['childs'][] = array(
						'child_id' 	=> $child['category_id'],
						'name'      => $child['name']
					);
				}
			
				$data['categories'][$store['store_id']][] = array(
					'category_id'	=> $category['category_id'],
					'name'       	=> $category['name'],
					'childs'		=> $data['childs']
				);
			}
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
		
		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info['title'])) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = '';
		}

		$data['parent_id'] = $data['child_id'] = [];
		
		if (isset($this->request->post['categories'])) {
			$data['categories_selected'] = $this->request->post['categories'];
		} elseif (!empty($module_info)) {
			$data['categories_selected'] = $module_info['categories'];
		} else {
			$data['categories_selected'] = [];
		}
		
		if (isset($this->request->post['image_width'])) {
			$data['image_width'] = $this->request->post['image_width'];
		} elseif (!empty($module_info)) {
			$data['image_width'] = $module_info['image_width'];
		} else {
			$data['image_width'] = 220;
		}
		
		if (isset($this->request->post['image_height'])) {
			$data['image_height'] = $this->request->post['image_height'];
		} elseif (!empty($module_info)) {
			$data['image_height'] = $module_info['image_height'];
		} else {
			$data['image_height'] = 200;
		}
		
		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($module_info) && isset($module_info['type'])) {
			$data['type'] = $module_info['type'];
		} else {
			$data['type'] = '';
		}
		
		if (isset($this->request->post['columns'])) {
			$data['columns'] = $this->request->post['columns'];
		} elseif (!empty($module_info)) {
			$data['columns'] = $module_info['columns'];
		} else {
			$data['columns'] = '';
		}
		
		if (isset($this->request->post['view_type'])) {
			$data['view_type'] = $this->request->post['view_type'];
		} elseif (!empty($module_info) && isset($module_info['view_type'])) {
			$data['view_type'] = $module_info['view_type'];
		} else {
			$data['view_type'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_category_wall', $data));
	}
	
	public function categories() {
		
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_category_wall')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}