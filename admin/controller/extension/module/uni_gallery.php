<?php 
class ControllerExtensionModuleUniGallery extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/uni_gallery');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('extension/module/uni_gallery');
		$this->getList();
	}

	public function insert() {
		$this->load->language('extension/module/uni_gallery');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('extension/module/uni_gallery');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_uni_gallery->addGallery($this->request->post);

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

			$this->response->redirect($this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		$this->load->language('extension/module/uni_gallery');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('extension/module/uni_gallery');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_uni_gallery->editGallery($this->request->get['gallery_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort='.$this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order='.$this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page='.$this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_gallery', 'user_token='.$this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/uni_gallery');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('extension/module/uni_gallery');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $gallery_id) {
				$this->model_extension_module_uni_gallery->deleteGallery($gallery_id);
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

			$this->response->redirect($this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$this->load->language('extension/module/uni_gallery');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], 'SSL'),
			'separator' => false
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/uni_gallery', 'user_token='.$this->session->data['user_token'].$url, 'SSL')
		);

		$data['insert'] = $this->url->link('extension/module/uni_gallery/insert', 'user_token='.$this->session->data['user_token'].$url, 'SSL');
		$data['delete'] = $this->url->link('extension/module/uni_gallery/delete', 'user_token='.$this->session->data['user_token'].$url, 'SSL');

		$data['galleries'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$gallery_total = $this->model_extension_module_uni_gallery->getTotalGallerys();

		$results = $this->model_extension_module_uni_gallery->getGallerys($filter_data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('extension/module/uni_gallery/update', 'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $result['gallery_id'] . $url, 'SSL')
			);

			$data['galleries'][] = array(
				'gallery_id' => $result['gallery_id'],
				'name'      => $result['name'],	
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),				
				'selected'  => isset($this->request->post['selected']) && in_array($result['gallery_id'], $this->request->post['selected']),				
				'action'    => $action
			);
		}

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');	

		$data['button_insert'] = $this->language->get('button_add');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, 'SSL');
		$data['sort_status'] = $this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $gallery_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_gallery_list', $data));
	}

	protected function getForm() {
		$this->load->language('extension/module/uni_gallery');
		
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_image_manager'] = $this->language->get('text_image_manager');
		$data['text_browse'] = $this->language->get('text_browse');
		$data['text_clear'] = $this->language->get('text_clear');			

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_image'] = $this->language->get('entry_image');		
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add_gallery'] = $this->language->get('button_add_gallery');
		$data['button_image_add'] = $this->language->get('button_image_add');
		$data['button_remove'] = $this->language->get('button_remove');
		
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['gallery_image'])) {
			$data['error_gallery_image'] = $this->error['gallery_image'];
		} else {
			$data['error_gallery_image'] = array();
		}	

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
		);
		
		if(VERSION >= 2.2) {
			$data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_module'),
				'href'      => $this->url->link('extension/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_module'),
				'href'      => $this->url->link('extension/module', 'user_token='.$this->session->data['user_token'], 'SSL')
			);
		}

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
		);

		if (!isset($this->request->get['gallery_id'])) { 
			$data['action'] = $this->url->link('extension/module/uni_gallery/insert', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/uni_gallery/update', 'user_token=' . $this->session->data['user_token'] . '&gallery_id=' . $this->request->get['gallery_id'] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link('extension/module/uni_gallery', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		if (isset($this->request->get['gallery_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$gallery_info = $this->model_extension_module_uni_gallery->getGallery($this->request->get['gallery_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($gallery_info)) {
			$data['name'] = $gallery_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($gallery_info)) {
			$data['status'] = $gallery_info['status'];
		} else {
			$data['status'] = true;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->post['gallery_image'])) {
			$gallery_images = $this->request->post['gallery_image'];
		} elseif (isset($this->request->get['gallery_id'])) {
			$gallery_images = $this->model_extension_module_uni_gallery->getGalleryImages($this->request->get['gallery_id']);	
		} else {
			$gallery_images = array();
		}

		$data['gallery_images'] = array();

		foreach ($gallery_images as $gallery_image) {
			if ($gallery_image['image'] && file_exists(DIR_IMAGE . $gallery_image['image'])) {
				$image = $gallery_image['image'];
			} else {
				$image = 'no_image.jpg';
			}			

			$data['gallery_images'][] = array(
				'image_description' => $gallery_image['image_description'],
				'link'              => $gallery_image['link'],
				'image'             => $image,
				'thumb'             => $this->model_tool_image->resize($image, 100, 100)
			);	
		} 

		$data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_gallery_form', $data));
	}
	
	public function install() {
		$this->load->model('extension/module/uni_gallery');
		$this->model_extension_module_uni_gallery->install();
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_gallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (isset($this->request->post['gallery_image'])) {
			foreach ($this->request->post['gallery_image'] as $gallery_image_id => $gallery_image) {
				foreach ($gallery_image['gallery_image_description'] as $language_id => $gallery_image_description) {
				
				}
			}	
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_gallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>