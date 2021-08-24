<?php
class ControllerExtensionModuleUniBannerInCategory extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('extension/module/uni_banner_in_category');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');
		$this->load->model('extension/module/uni_banner_in_category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_uni_banner_in_category', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$this->getList();
	}
	
	public function insert() {
		$this->load->language('extension/module/uni_banner_in_category');
		
		$this->load->model('extension/module/uni_banner_in_category');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_extension_module_uni_banner_in_category->addBanner($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/module/uni_banner_in_category');

		$this->load->model('extension/module/uni_banner_in_category');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_extension_module_uni_banner_in_category->editBanner($this->request->get['banner_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/uni_banner_in_category');
		
		$this->load->model('extension/module/uni_banner_in_category');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $banner_id) {
				$this->model_extension_module_uni_banner_in_category->deleteBanner($banner_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	
	private function getList() {
		$this->load->model('catalog/category');
		$this->load->model('extension/module/uni_banner_in_category');
		$this->load->model('tool/image');
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'], true)
		];
		
		$data['insert'] = $this->url->link('extension/module/uni_banner_in_category/insert', 'user_token=' . $this->session->data['user_token'], true);
		$data['edit'] = $this->url->link('extension/module/uni_banner_in_category/edit', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = $this->url->link('extension/module/uni_banner_in_category/delete', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$data['status'] = $this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'], true);
		
		if (isset($this->request->post['module_uni_banner_in_category_status'])) {
			$data['module_status'] = $this->request->post['module_uni_banner_in_category_status'];
		} else {
			$data['module_status'] = $this->config->get('module_uni_banner_in_category_status');
		}
		
		$banners = $this->model_extension_module_uni_banner_in_category->getBanners();
		
		$data['banners'] = [];
		
		foreach($banners as $banner) {
			$categories = $this->model_extension_module_uni_banner_in_category->getBannerCategories($banner['banner_id']);
				
			$cat_names = [];
				
			if($categories) {
				foreach($categories as $cat_id){
					$cat = $this->model_catalog_category->getCategory((int)$cat_id);
					
					$cat_names[] = [
						'name' => ($cat['path']) ? $cat['path'] . ' &gt; ' . $cat['name'] : $cat['name'],
						'href' => HTTPS_CATALOG.'index.php?route=product/category&path='.$cat_id,
					];
				}
			}
			
			$data['banners'][] = [
				'banner_id'  => $banner['banner_id'],
				'image' 	 => $banner['image'] ? $this->model_tool_image->resize($banner['image'], 150, 80) : '',
				'name' 		 => $banner['name'],
				'date_start' => $banner['date_start'] != '0000-00-00' ? date('d.m.y', strtotime($banner['date_start'])) : $this->language->get('text_date_undefined'),
				'date_end'   => $banner['date_end'] != '0000-00-00' ? date('d.m.y', strtotime($banner['date_end'])) : $this->language->get('text_date_undefined'),
				'status'	 => $banner['status'] == 1 ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'categories' => $cat_names,
			];
			
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_banner_in_category_list', $data));
	}
	
	private function getForm() {
		
		$this->load->model('catalog/category');
		$this->load->model('extension/module/uni_banner_in_category');
		$this->load->model('localisation/language');
		$this->load->model('tool/image');
		$this->load->model('setting/store');
		
		$this->document->addStyle('view/javascript/summernote/summernote.css');
		$this->document->addScript('view/javascript/summernote/summernote.js');
		$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
		$this->document->addScript('view/javascript/summernote/opencart.js');
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'], true)
		];
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (!isset($this->request->get['banner_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_banner_in_category/insert', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_banner_in_category/edit', 'user_token=' . $this->session->data['user_token'] . '&banner_id=' . $this->request->get['banner_id'], true);
		}
		
		$data['cancel'] = $this->url->link('extension/module/uni_banner_in_category', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 200, 100);
		
		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (isset($this->request->get['banner_id'])) {
			$data['description'] = $this->model_extension_module_uni_banner_in_category->getBannerDescription($this->request->get['banner_id']);
		} else {
			$data['description'] = [];
		}
		
		if ((isset($this->request->get['banner_id'])) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$banner_info = $this->model_extension_module_uni_banner_in_category->getBanner($this->request->get['banner_id']);
		}
		
		$data['thumb'] = [];
		
		foreach($data['description'] as $key => $desc) {
			$data['thumb'][$key] = ($desc['image'] && file_exists(DIR_IMAGE . $desc['image'])) ? $this->model_tool_image->resize($desc['image'], 200, 100) : '';
		}
		
		if (isset($this->request->post['categories'])) {
			$data['categories_selected'] = $this->request->post['categories'];
		} elseif (isset($this->request->get['banner_id'])) {
			$data['categories_selected'] = $this->model_extension_module_uni_banner_in_category->getBannerCategories($this->request->get['banner_id']);
		} else {
			$data['categories_selected'] = [];
		}
		
		if (isset($this->request->post['stores'])) {
			$data['stores_selected'] = $this->request->post['stores'];
		} elseif (isset($this->request->get['banner_id'])) {
			$data['stores_selected'] = $this->model_extension_module_uni_banner_in_category->getBannerStores($this->request->get['banner_id']);
		} else {
			$data['stores_selected'] = array(0);
		}
		
		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($banner_info)) {
			$data['type'] = $banner_info['type'];
		} else {
			$data['type'] = 1;
		}
		
		if (isset($this->request->post['position'])) {
			$data['position'] = $this->request->post['position'];
		} elseif (!empty($banner_info)) {
			$data['position'] = $banner_info['position'];
		} else {
			$data['position'] = 4;
		}
		
		if (isset($this->request->post['position2'])) {
			$data['position2'] = $this->request->post['position2'];
		} elseif (!empty($banner_info)) {
			$data['position2'] = $banner_info['position2'];
		} else {
			$data['position2'] = 5;
		}
		
		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($banner_info)) {
			$data['width'] = $banner_info['width'];
		} else {
			$data['width'] = 1980;
		}
		
		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($banner_info)) {
			$data['height'] = $banner_info['height'];
		} else {
			$data['height'] = 100;
		}
		
		if (isset($this->request->post['date_start'])) {
			$data['date_start'] = $this->request->post['date_start'];
		} elseif (!empty($banner_info)) {
			$data['date_start'] = $banner_info['date_start'] != '0000-00-00' ? $banner_info['date_start'] : '';
		} else {
			$data['date_start'] = '';
		}
		
		if (isset($this->request->post['date_end'])) {
			$data['date_end'] = $this->request->post['date_end'];
		} elseif (!empty($banner_info)) {
			$data['date_end'] = $banner_info['date_end'] != '0000-00-00' ? $banner_info['date_end'] : '';
		} else {
			$data['date_end'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($banner_info)) {
			$data['status'] = $banner_info['status'];
		} else {
			$data['status'] = 1;
		}
		
		$filter_data = [
			'sort' => 'name',
			'order' => 'ASC',
		];
		
		$data['categories'] = $this->model_catalog_category->getCategories($filter_data);

		$data['stores'] = [];
		
		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_banner_in_category_form', $data));
	}
	
	public function install() {
		$this->load->model('extension/module/uni_banner_in_category');
		
		$this->model_extension_module_uni_banner_in_category->install();
	}
	
	public function uninstall() {
		$this->load->model('extension/module/uni_banner_in_category');
		
		$this->model_extension_module_uni_banner_in_category->uninstall();
	}
	
	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_banner_in_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_banner_in_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}