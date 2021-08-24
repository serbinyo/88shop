<?php
class ControllerExtensionModuleUniRequest extends Controller {
	private $error = false;

	public function index() {		
		$this->load->language('extension/module/uni_request');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('localisation/language');
		$this->load->model('extension/module/uni_request');

		$this->getList();
		$this->check();
	}

	public function edit() {
		$this->load->model('extension/module/uni_request');
		$this->load->language('extension/module/uni_request');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->model_extension_module_uni_request->editRequest($this->request->get['request_id'], $this->request->post);
		
			$language_id = $this->config->get('config_language_id');
			$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : array();
			
			if($this->request->post['type'] == $settings['heading_question'][$language_id] && $this->request->post['status'] == 3) {
				$this->send($this->request->get['request_id']);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_type=' . urlencode(html_entity_decode($this->request->get['filter_type'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status']) && $this->request->get['filter_status'] != '') {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/uni_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/uni_request');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $request_id) {
				$this->model_extension_module_uni_request->deleteRequest($request_id);
			}
			
			//$this->model_extension_module_uni_request->deleteRequest($this->request->get['request_id']);

			$this->session->data['success'] = $this->language->get('text_success_delete');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_author'])) {
				$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$data = array();
		
		$this->load->model('extension/module/uni_request');
		$this->load->model('localisation/language');
	
		$this->load->language('extension/module/uni_request');
		$data['lang'] = array_merge($data, $this->language->load('extension/module/uni_request'));
		
		$this->document->addStyle('view/stylesheet/unishop.css');
	
		if (isset($this->request->get['filter_type'])) {
			$filter_type = $this->request->get['filter_type'];
		} else {
			$filter_type = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_type'])) {
			$url .= '&filter_type=' . urlencode(html_entity_decode($this->request->get['filter_type'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

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
       		'text'		=> $this->language->get('text_home'),
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
       		'text'		=> $this->language->get('heading_title'),
			'href'		=> $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['settings'] = $this->config->get('uni_request');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['add'] = $this->url->link('extension/module/uni_request/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/uni_request/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['link_settings'] = $this->url->link('extension/module/uni_request/setting', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['requests'] = array();

		$filter_data = array(
			'filter_type'   	=> $filter_type,
			'filter_name'     	=> $filter_name,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$request_total = $this->model_extension_module_uni_request->getTotalRequests($filter_data);

		$results = $this->model_extension_module_uni_request->getRequests($filter_data);
		
		$data['types'] = $this->model_extension_module_uni_request->getStatuses();

		foreach ($results as $result) {
			
			if($result['status'] == 1) {
				$status = $this->language->get('text_status_1');	
			} else if($result['status'] == 2) {
				$status = $this->language->get('text_status_2');
			} else if($result['status'] == 3) {
				$status = $this->language->get('text_status_3');
			}
			
			if($result['request_list'] == 0) {
				$request_list = $this->language->get('text_off');	
			} else if($result['request_list'] == 1) {
				$request_list = $this->language->get('text_on');
			}
			
			$product_name = '';
			$product_href = '';
			
			if($result['product_id']) {
				$this->load->model('catalog/product');
				$product_info = $this->model_catalog_product->getProduct($result['product_id']);
				$product_name = $product_info ? $product_info['name'] : 'product may have been removed';
				$product_href = HTTPS_CATALOG.'index.php?route=product/product&product_id='.$result['product_id'];
			}
		
			$data['requests'][] = array(
				'request_id'  	=> $result['request_id'],
				'type'       	=> $result['type'],
				'name'     		=> $result['name'],
				'phone'     	=> $result['phone'],
				'mail'     		=> $result['mail'],
				'product_name'  => $product_name,
				'product_href' 	=> $product_href,
				'comment'     	=>  nl2br($result['comment']),
				'admin_comment' => $result['admin_comment'],
				'date_added' 	=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'status'    	=> $status,
				'request_list'  => $request_list,
				'edit'       	=> $this->url->link('extension/module/uni_request/edit', 'user_token='.$this->session->data['user_token'].'&request_id='.$result['request_id'].$url, true),
				'delete'       	=> $this->url->link('extension/module/uni_request/delete', 'user_token='.$this->session->data['user_token'].'&request_id='.$result['request_id'].$url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_status_1'] = $this->language->get('text_status_1');
		$data['text_status_2'] = $this->language->get('text_status_2');
		$data['text_status_3'] = $this->language->get('text_status_3');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['column_type'] = $this->language->get('column_type');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_phone'] = $this->language->get('column_phone');
		$data['column_mail'] = $this->language->get('column_mail');
		$data['column_comment'] = $this->language->get('column_comment');
		$data['column_admin_comment'] = $this->language->get('column_admin_comment');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_date_modified'] = $this->language->get('column_date_modified');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_type'] = $this->language->get('entry_type');
		
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_rating'] = $this->language->get('entry_rating');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_date_added'] = $this->language->get('entry_date_added');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['user_token'] = $this->session->data['user_token'];

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
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

		$data['sort_type'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=type' . $url, true);
		$data['sort_name'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_phone'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=phone' . $url, true);
		$data['sort_mail'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=mail' . $url, true);
		$data['sort_date_added'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_type'])) {
			$url .= '&filter_type=' . urlencode(html_entity_decode($this->request->get['filter_type'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $request_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($request_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($request_total - $this->config->get('config_limit_admin'))) ? $request_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $request_total, ceil($request_total / $this->config->get('config_limit_admin')));

		$data['filter_type'] = $filter_type;
		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_request_list', $data));
	}

	protected function getForm() {
		$data = array();
		
		$this->load->model('extension/module/uni_request');
		$this->load->model('localisation/language');
	
		$this->load->language('extension/module/uni_request');
		
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
	
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['request_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['article'])) {
			$data['error_article'] = $this->error['article'];
		} else {
			$data['error_article'] = '';
		}

		if (isset($this->error['author'])) {
			$data['error_author'] = $this->error['author'];
		} else {
			$data['error_author'] = '';
		}

		if (isset($this->error['text'])) {
			$data['error_text'] = $this->error['text'];
		} else {
			$data['error_text'] = '';
		}

		if (isset($this->error['rating'])) {
			$data['error_rating'] = $this->error['rating'];
		} else {
			$data['error_rating'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true),
   		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['action'] = $this->url->link('extension/module/uni_request/edit', 'user_token=' . $this->session->data['user_token'] . '&request_id=' . $this->request->get['request_id'] . $url, true);
		$data['cancel'] = $this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->response->redirect($this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		if (isset($this->request->get['request_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$request_info = $this->model_extension_module_uni_request->getRequest($this->request->get['request_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($request_info)) {
			$data['type'] = $request_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($request_info)) {
			$data['name'] = $request_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} elseif (!empty($request_info)) {
			$data['phone'] = $request_info['phone'];
		} else {
			$data['phone'] = '';
		}
		
		if (isset($this->request->post['mail'])) {
			$data['mail'] = $this->request->post['mail'];
		} elseif (!empty($request_info)) {
			$data['mail'] = $request_info['mail'];
		} else {
			$data['mail'] = $settings['email_cap'];
		}
		
		$data['product_id'] = isset($request_info['product_id']) ? $request_info['product_id'] : 0;
		
		$data['product_name'] = $data['product_href'] = '';
		
		if($data['product_id']) {
			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($request_info['product_id']);
			$data['product_name'] = $product_info['name'];
			$data['product_href'] = HTTPS_CATALOG.'index.php?route=product/product&product_id=' . $request_info['product_id'];
		}

		if (isset($this->request->post['comment'])) {
			$data['comment'] = html_entity_decode($this->request->post['comment']);
		} elseif (!empty($request_info)) {
			$data['comment'] = html_entity_decode($request_info['comment']);
		} else {
			$data['comment'] = '';
		}
		
		if (isset($this->request->post['admin_comment'])) {
			$data['admin_comment'] = $this->request->post['admin_comment'];
		} elseif (!empty($request_info)) {
			$data['admin_comment'] = $request_info['admin_comment'];
		} else {
			$data['admin_comment'] = '';
		}
		
		if (isset($this->request->post['date_added'])) {
			$data['date_added'] = $this->request->post['date_added'];
		} elseif (!empty($request_info)) {
			$data['date_added'] = $request_info['date_added'];
		} else {
			$data['date_added'] = '';
		}
		
		if (isset($this->request->post['request_list'])) {
			$data['request_list'] = $this->request->post['request_list'];
		} elseif (!empty($request_info)) {
			$data['request_list'] = $request_info['request_list'];
		} else {
			$data['request_list'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($request_info)) {
			$data['status'] = $request_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_request_form', $data));
	}
	
	public function setting() {	
		$this->load->language('extension/module/uni_request');
		
		$this->load->model('localisation/language');
		$this->load->model('setting/setting');
		
		$this->document->addStyle('view/stylesheet/unishop.css');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('text_home'),
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('extension/module/uni_request/setting', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('heading_title'),
		);

		$data['uni_request'] = $this->config->get('uni_request') ? $this->config->get('uni_request') : array();
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['uni_request'])) {
			if($this->validate()) {
				$this->model_setting_setting->editSetting('uni_request', $this->request->post);
				
				$this->session->data['success'] = $this->language->get('text_success');
				
				$this->response->redirect($this->url->link('extension/module/uni_request', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
		
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_request_setting', $data));
	}
	
	public function check() {
		$this->load->model('extension/module/uni_request');
		$this->load->model('catalog/product');
		
		$language_id = $this->config->get('config_language_id');
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : array();
		
		$filter_data = array();
		
		$comment = $this->language->get('text_comment');
		$status = 3;
		
		$results = $this->model_extension_module_uni_request->getRequests($filter_data);
		
		if($results && $settings) {
			foreach($results as $result) {
				if($result['product_id'] > 0 && $result['type'] == $settings['heading_notify'][$language_id] && $result['mail'] !='' && $result['status'] != $status) {
					$products = $this->model_catalog_product->getProduct($result['product_id']);
					if(isset($products['quantity']) && $products['quantity'] > 0) {
						$this->model_extension_module_uni_request->setComment($comment, $result['request_id']);
						$this->model_extension_module_uni_request->setStatus($status, $result['request_id']);
						$this->send($result['request_id']);
					}
				}
			}
		}
	}
	
	public function install() {
		$this->load->model('setting/setting');
		$this->load->model('extension/module/uni_request');
		$this->model_extension_module_uni_request->Install();
		
		$default_settings['uni_request'] = [
			'heading_notify' => [1 => 'Уведомить о наличии', 2 => 'Уведомить о наличии', 3 => 'Уведомить о наличии'],
			'notify_email' => 1,
			'notify_email_required' => 1,
			'heading_question' => [1 => 'Вопрос о товаре', 2 => 'Вопрос о товаре', 3 => 'Вопрос о товаре'],
            'question_list' => 1,
			'question_email' => 1,
			'question_email_required' => 1,
			'question_captcha' => 1,
			'email_cap' => 'mail@localhost'
		];
	
		if(!$this->config->get('uni_request')) {
			$this->model_setting_setting->editSetting('uni_request', $default_settings);
		}
	}
	
	protected function send($request_id) {
		$this->load->model('extension/module/uni_request');
		$this->load->model('catalog/product');
		$this->load->language('extension/module/uni_request');
		
		$language_id = $this->config->get('config_language_id');
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
		
		$result = $this->model_extension_module_uni_request->getRequest($request_id);
		$product = $this->model_catalog_product->getProduct($result['product_id']);
		
		$customer_mail = $result['mail'] && $result['mail'] != '' ? $result['mail'] : $settings['email_cap'];
		
		$host = $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG;
		
		switch ($result['type']) {
			case $settings['heading_notify'][$language_id]:
				$subject = sprintf($this->language->get('text_subject_notify'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$message = sprintf($this->language->get('text_message_notify'), $host, $product['product_id'], html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'))."\n";
				break;
			case $settings['heading_question'][$language_id]:
				$subject = sprintf($this->language->get('text_subject_question'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$message = sprintf($this->language->get('text_message_question'), $host, $product['product_id'], html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'), $result['comment'], $result['admin_comment'])."\n";
				break;
		}

		//$mail = new Mail();
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($customer_mail);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($subject);
		$mail->setHtml(html_entity_decode($message));
		$mail->send();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_request')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

}