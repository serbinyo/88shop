<?php
class ControllerExtensionModuleUniNews extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');
		
		$this->install();
		
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->getModule();
	}

	public function insert() {
		$this->load->model('extension/module/uni_news');
		
		$this->install();
	
		$this->load->language('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_extension_module_uni_news->addNews($this->request->post);

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

			$this->response->redirect($this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function update() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm())) {
			$this->model_extension_module_uni_news->editNews($this->request->get['news_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (isset($this->request->post['selected']) && $this->validatePermission()) {
			foreach ($this->request->post['selected'] as $news_id) {
				$this->model_extension_module_uni_news->deleteNews($news_id);
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

			$this->response->redirect($this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function reset() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (isset($this->request->post['selected']) && $this->validateReset()) {
			foreach ($this->request->post['selected'] as $news_id) {
				$news_info = $this->model_extension_module_uni_news->getNewsStory($news_id);

				if ($news_info && ($news_info['viewed'] > 0)) {
					$this->model_extension_module_uni_news->resetViews($news_id);
				}
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

			$this->response->redirect($this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function listing() {
		$this->load->model('extension/module/uni_news');
		
		$this->install();
	
		$this->load->language('extension/module/uni_news');
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$data['heading_title'] = strip_tags($this->language->get('heading_title'));
		
		$this->getList();
	}
	
	public function category_list() {
		$this->load->model('extension/module/uni_news');
		
		$this->install();
	
		$this->load->language('extension/module/uni_news');
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$data['heading_title'] = strip_tags($this->language->get('heading_title'));
		
		$this->getCategoryList();
	}

	private function getModule() {
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->document->addStyle('view/stylesheet/unishop.css');
		
		$this->language->load('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');
		
		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		
		$data['heading_title'] = strip_tags($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_news', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->cache->delete('unishop.news');

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
 		$data['error_numchars'] = isset($this->error['numchars']) ? $this->error['numchars'] : '';
		$data['error_newspage_thumb'] = isset($this->error['newspage_thumb']) ? $this->error['newspage_thumb'] : '';
		$data['error_newspage_popup'] = isset($this->error['newspage_popup']) ? $this->error['newspage_popup'] : '';

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
			'href'		=> $this->url->link('extension/module/uni_news', 'user_token=' . $this->session->data['user_token'], true)
   		);

		$data['news_list'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'], true);
		
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_news', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_news', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);

		$data['categories'] = [];

		$filter_data = array(
			'start' 	=> 0,
			'limit' 	=> 500
		);

		$category_total = $this->model_extension_module_uni_news->getTotalCategory();

		$results = $this->model_extension_module_uni_news->getCategories($filter_data);

		foreach ($results as $result) {
			$data['categories'][] = array(
				'category_id'	=> $result['category_id'],
				'name'			=> $result['name']
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

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info)) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = 4;
		}

		if (isset($this->request->post['thumb_width'])) {
			$data['thumb_width'] = $this->request->post['thumb_width'];
		} elseif (!empty($module_info)) {
			$data['thumb_width'] = $module_info['thumb_width'];
		} else {
			$data['thumb_width'] = 320;
		}

		if (isset($this->request->post['thumb_height'])) {
			$data['thumb_height'] = $this->request->post['thumb_height'];
		} elseif (!empty($module_info)) {
			$data['thumb_height'] = $module_info['thumb_height'];
		} else {
			$data['thumb_height'] = 240;
		}
		
		if (isset($this->request->post['numchars'])) {
			$data['numchars'] = $this->request->post['numchars'];
		} elseif (!empty($module_info)) {
			$data['numchars'] = $module_info['numchars'];
		} else {
			$data['numchars'] = 100;
		}
		
		if (isset($this->request->post['category'])) {
			$data['category_selected'] = $this->request->post['category'];
		} elseif (!empty($module_info['category'])) {
			$data['category_selected'] = $module_info['category'];
		} else {
			$data['category_selected'] = 0;
		}
		
		if (isset($this->request->post['sub_category'])) {
			$data['sub_category'] = $this->request->post['sub_category'];
		} elseif (!empty($module_info['sub_category'])) {
			$data['sub_category'] = $module_info['sub_category'];
		} else {
			$data['sub_category'] = '';
		}
		
		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 5;
		}
		
		if (isset($this->request->post['view_type'])) {
			$data['view_type'] = $this->request->post['view_type'];
		} elseif (isset($module_info['view_type'])) {
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

		$this->response->setOutput($this->load->view('extension/module/uni_news', $data));
	}

	private function getList() {
		$this->load->language('extension/module/uni_news');
		
		$this->load->model('localisation/language');
		
		$this->document->addStyle('view/stylesheet/unishop.css');		

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'n.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
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
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('text_home'),
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true),
			'text'		=> $this->language->get('heading_title')
		);
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['module'] = $this->url->link('extension/module/uni_news', 'user_token=' . $this->session->data['user_token'], true);

		$data['insert'] = $this->url->link('extension/module/uni_news/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['reset'] = $this->url->link('extension/module/uni_news/reset', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/uni_news/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['link_settings'] = $this->url->link('extension/module/uni_news/setting', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['setting'] = $this->config->get('uni_news') ? $this->config->get('uni_news') : array();
		
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('extension/module/uni_news');
		$this->load->model('tool/image');

		$data['news'] = array();

		$filter_data = array(
			'sort'  	=> $sort,
			'order' 	=> $order,
			'start' 	=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 	=> $this->config->get('config_limit_admin')
		);

		$news_total = $this->model_extension_module_uni_news->getTotalNews();

		$data['totalnews'] = $news_total;

		$results = $this->model_extension_module_uni_news->getNews($filter_data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text'	=> $this->language->get('text_edit'),
				'href'	=> $this->url->link('extension/module/uni_news/update', 'user_token=' . $this->session->data['user_token'] . '&news_id='.$result['news_id'], true)
			);

			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 40, 40);
			}

			$data['news'][] = array(
				'news_id'		=> $result['news_id'],
				'name'			=> $result['name'],
				'image'			=> $image,
				'date_added'	=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'viewed'		=> $result['viewed'],
				'status'		=> $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'selected'		=> isset($this->request->post['selected']) && in_array($result['news_id'], $this->request->post['selected']),
				'shop_href'	 	=> HTTPS_CATALOG.'index.php?route=information/uni_news_story&news_id='.$result['news_id'],
				'action'		=> $action
			);
		}

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

		$data['sort_name'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . '&sort=nd.name' . $url, true);
		$data['sort_date_added'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . '&sort=n.date_added' . $url, true);
		$data['sort_viewed'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . '&sort=n.viewed' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . '&sort=n.status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $news_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_news_list', $data));
	}

	private function getForm() {
		$this->load->language('extension/module/uni_news');
		
		$this->document->addStyle('view/stylesheet/unishop.css');
		
		$this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
		$this->document->addStyle('view/javascript/codemirror/theme/monokai.css');
		$this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
		$this->document->addScript('view/javascript/codemirror/lib/xml.js');
		$this->document->addScript('view/javascript/codemirror/lib/formatting.js');
		
		if ($this->config->get('config_editor_default')) {
			$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
	        $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
	    } else {
			$this->document->addStyle('view/javascript/summernote/summernote.css');
			$this->document->addScript('view/javascript/summernote/summernote.js');
			$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
			$this->document->addScript('view/javascript/summernote/opencart.js');
		}
		
		$data['ckeditor'] = $this->config->get('config_editor_default');
		
		$data['lang'] = $this->language->get('lang');

		$this->load->model('extension/module/uni_news');

		$data['user_token'] = $this->session->data['user_token'];
		$data['ckeditor'] = $this->config->get('config_editor_default');
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
		$data['error_description'] = isset($this->error['description']) ? $this->error['description'] : '';
		$data['error_news_description'] = ($data['error_name'] || $data['error_description']) ?  $this->language->get('error_news_description') : '';
		$data['error_category'] = isset($this->error['category']) ? $this->error['category'] : '';
		$data['error_keyword'] = isset($this->error['keyword']) ? $this->error['keyword'] : '';

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('text_home'),
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('heading_title'),
		);

		if (!isset($this->request->get['news_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_news/insert', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_news/update', 'user_token=' . $this->session->data['user_token'] . '&news_id=' . $this->request->get['news_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'], true);

		if ((isset($this->request->get['news_id'])) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$news_info = $this->model_extension_module_uni_news->getNewsStory($this->request->get['news_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (isset($news_info['category_id'])) {
			$data['category_id'] = $news_info['category_id'];
		} else {
			$data['category_id'] = 0;
		}
		
		$data['path'] = '';
		
		if($data['category_id']) {
			$category_info = $this->model_extension_module_uni_news->getCategory($data['category_id']);
			$data['path'] = $category_info['path'] ? $category_info['path'].' &gt; '.$category_info['name'] : $category_info['name'];
		}
		
		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		$news_id = isset($this->request->get['news_id']) ? $this->request->get['news_id'] : 0;

		$news_description = $this->model_extension_module_uni_news->getNewsDescriptions($news_id);
		
		if (isset($this->request->post['news_description'])) {
			$data['news_description'] = $this->request->post['news_description'];
		} elseif ($news_description) {
			$data['news_description'] = $news_description;
		} else {
			$data['news_description'] = [];
		}

		$news_store = $this->model_extension_module_uni_news->getNewsStores($news_id);
		
		if (isset($this->request->post['news_store'])) {
			$data['news_store'] = $this->request->post['news_store'];
		} elseif ($news_store) {
			$data['news_store'] = $news_store;
		} else {
			$data['news_store'] = array(0);
		}
		
		if (isset($this->request->post['date_added'])) {
			$data['date_added'] = $this->request->post['date_added'];
		} elseif (isset($news_info['date_added'])) {
			$data['date_added'] = $news_info['date_added'];
		} else {
			$data['date_added'] = date('Y-m-d H:i:s');
		}

		$news_seo_url = $this->model_extension_module_uni_news->getNewsSeoUrls($news_id);
		
		if (isset($this->request->post['news_seo_url'])) {
			$data['news_seo_url'] = $this->request->post['news_seo_url'];
		} elseif ($news_seo_url) {
			$data['news_seo_url'] = $news_seo_url;
		} else {
			$data['news_seo_url'] = [];
		}
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (isset($news_info['image'])) {
			$data['image'] = $news_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		if (isset($news_info['image']) && file_exists(DIR_IMAGE . $news_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($news_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
		
		$this->load->model('catalog/product');
		
		$related_products = $this->model_extension_module_uni_news->getNewsRelatedProduct($news_id);
		
		$data['related_products'] = array();
			
		foreach ($related_products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['related_products'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($news_info)) {
			$data['status'] = $news_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_news_form', $data));
	}
	
	public function addCategory() {
		$this->load->model('extension/module/uni_news');
	
		$this->load->language('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateCategoryForm())) {
			$this->model_extension_module_uni_news->addCategory($this->request->post);

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

			$this->response->redirect($this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		$this->getCategoryForm();
	}

	public function editCategory() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateCategoryForm()) {
			
			$this->model_extension_module_uni_news->editCategory($this->request->get['category_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		$this->getCategoryForm();
	}

	public function deleteCategory() {
		$this->load->language('extension/module/uni_news');

		$this->load->model('extension/module/uni_news');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		if (isset($this->request->post['selected']) && $this->validatePermission()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_extension_module_uni_news->deleteCategory($category_id);
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

			$this->response->redirect($this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		$this->getCategoryList();
	}
	
	private function getCategoryList() {
		$this->load->language('extension/module/uni_news');
		
		$this->load->model('localisation/language');
		
		$this->document->addStyle('view/stylesheet/unishop.css');		

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
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('text_home'),
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'] . $url, true),
			'text'		=> $this->language->get('heading_title')
		);
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['module'] = $this->url->link('extension/module/uni_news', 'user_token=' . $this->session->data['user_token'], true);

		$data['add'] = $this->url->link('extension/module/uni_news/addCategory', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/uni_news/deleteCategory', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['link_settings'] = $this->url->link('extension/module/uni_news/setting', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if ($this->config->get('uni_news')) {
			$data['uni_news'] = $this->config->get('uni_news');
		} else {
			$data['uni_news'] = array();
		}

		$this->load->model('extension/module/uni_news');
		$this->load->model('tool/image');

		$data['categories'] = array();

		$filter_data = array(
			'sort'  	=> $sort,
			'order' 	=> $order,
			'start' 	=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 	=> $this->config->get('config_limit_admin')
		);

		$category_total = $this->model_extension_module_uni_news->getTotalCategory();

		$results = $this->model_extension_module_uni_news->getCategories($filter_data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text'	=> $this->language->get('text_edit'),
				'href'	=> $this->url->link('extension/module/uni_news/editCategory', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'], true)
			);

			$data['categories'][] = array(
				'category_id'	=> $result['category_id'],
				'name'			=> $result['name'],
				'sort_order'	=> $result['sort_order'],
				'selected'		=> isset($this->request->post['selected']) && in_array($result['news_id'], $this->request->post['selected']),
				'shop_href'	 	=> HTTPS_CATALOG.'index.php?route=information/uni_news&news_path=' . ($result['category_id']),
				'action'		=> $action
			);
		}

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

		$data['sort_name'] = $this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/uni_news/category_list', 'user_token='.$this->session->data['user_token'].$url.'&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_news_category_list', $data));
	}
	
	private function getCategoryForm() {
		$this->load->language('extension/module/uni_news');
		
		$this->document->addStyle('view/stylesheet/unishop.css');
		
		if ($this->config->get('config_editor_default')) {
			$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
	        $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
	    } else {
			$this->document->addStyle('view/javascript/summernote/summernote.css');
			$this->document->addScript('view/javascript/summernote/summernote.js');
			$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
			$this->document->addScript('view/javascript/summernote/opencart.js');
		}
		
		$data['ckeditor'] = $this->config->get('config_editor_default');
		
		$data['lang'] = $this->language->get('lang');

		$this->load->model('extension/module/uni_news');

		$data['user_token'] = $this->session->data['user_token'];
		$data['ckeditor'] = $this->config->get('config_editor_default');
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
		$data['error_description'] = isset($this->error['description']) ? $this->error['description'] : '';
		$data['error_keyword'] = isset($this->error['keyword']) ? $this->error['keyword'] : '';

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('text_home'),
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('heading_title'),
		);

		if (!isset($this->request->get['category_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_news/addCategory', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_news/editCategory', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $this->request->get['category_id'], true);
		}
		
		$data['category_id'] = isset($this->request->get['category_id']) ? $this->request->get['category_id'] : 0;

		$data['cancel'] = $this->url->link('extension/module/uni_news/category_list', 'user_token=' . $this->session->data['user_token'], true);

		if ((isset($this->request->get['category_id'])) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_extension_module_uni_news->getCategory($this->request->get['category_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['description'] = $this->model_extension_module_uni_news->getCategoryDescriptions($this->request->get['category_id']);
		} else {
			$data['description'] = array();
		}
		
		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($category_info)) {
			$data['path'] = $category_info['path'];
		} else {
			$data['path'] = '';
		}
		
		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}
		
		if (isset($this->request->post['category_stores'])) {
			$data['category_stores'] = $this->request->post['category_stores'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_stores'] = $this->model_extension_module_uni_news->getCategoryStores($this->request->get['category_id']);
		} else {
			$data['category_stores'] = array(0);
		}
		
		if (isset($this->request->post['seo_url'])) {
			$data['seo_url'] = $this->request->post['seo_url'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['seo_url'] = $this->model_extension_module_uni_news->getCategorySeoUrls($this->request->get['category_id']);
		} else {
			$data['seo_url'] = [];
		}
		
		if (isset($category_info['image'])) {
			$data['image'] = $category_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		if (isset($category_info['image']) && file_exists(DIR_IMAGE . $category_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
		
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = true;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_news_category_form', $data));
	}
	
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/module/uni_news');

			$filter_data = array(
				'name' => $this->request->get['filter_name'],
				'sort'        => 'cd.name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_extension_module_uni_news->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function setting() {	
		$this->load->language('extension/module/uni_news');
		
		$this->load->model('localisation/language');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		
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
			'href'		=> $this->url->link('extension/module/uni_news/setting', 'user_token=' . $this->session->data['user_token'], true),
			'text'		=> $this->language->get('heading_title'),
		);

		$data['uni_news'] = $this->config->get('uni_news') ? $this->config->get('uni_news') : array();
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['uni_news'])) {
			if($this->validatePermission()) {
				$this->model_setting_setting->editSetting('uni_news', $this->request->post);
				
				$this->session->data['success'] = $this->language->get('text_success');
				
				$this->response->redirect($this->url->link('extension/module/uni_news/listing', 'user_token=' . $this->session->data['user_token'], true));
			}
		}
		
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		$data['sitemaps'] = [];

		$data['sitemaps'][] = [
			'name'     => $this->config->get('config_name'),
			'url'      => ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG).'index.php?route=extension/feed/uni_news_sitemap',
		];

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['sitemaps'][] = [
				'name'     => $result['name'],
				'url'      => $result['ssl'].'index.php?route=extension/feed/uni_news_sitemap',
			];
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_news_setting', $data));
	}
	
	public function install() {
		$this->load->model('setting/setting');
		$this->load->model('extension/module/uni_news');
		$this->model_extension_module_uni_news->install();
		
		$default_settings['uni_news'] = [
			'subcategory_column' => ['col-lg-2', 'col-md-3', 'col-sm-4', 'col-xs-6'],
			'subcategory' => 1,
			'image_width' => 480,
			'image_height' => 360,
			'image' => 1,
			'thumb_width' => 480,
			'thumb_height' => 360,
			'popup_width' => 800,
			'popup_height' => 600,
			'addthis' => 1,
			'chars' => 300,
			'related_product_title' => [1 => 'Связанные товары', 2 => 'Связанные товары', 3 => 'Связанные товары'],
			'sitemap' => 1,
		];
		
		if(!$this->config->get('uni_news')) {
			$this->model_setting_setting->editSetting('uni_news', $default_settings);
		}

	}
	
	private function validateSettings() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['numchars']) {
			$this->error['numchars'] = $this->language->get('error_numchars');
		}

		if (!$this->request->post['thumb_width'] || !$this->request->post['thumb_height']) {
			$this->error['newspage_thumb'] = $this->language->get('error_newspage_thumb');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['numchars']) {
			$this->error['numchars'] = $this->language->get('error_numchars');
		}

		if (!$this->request->post['thumb_width'] || !$this->request->post['thumb_height']) {
			$this->error['newspage_thumb'] = $this->language->get('error_newspage_thumb');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['news_description'] as $language_id => $value) {
			if ((strlen($value['name']) < 3) || (strlen($value['name']) > 250)) {
				$this->error['name'][$language_id] = $this->language->get('error_title');
			}

			if (strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
		}
		
		if($this->request->post['category_id'] == 0) {
			$this->error['category'] = $this->language->get('error_category');
		}
		
		if ($this->request->post['news_seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['news_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
							$this->error['warning'] = $this->language->get('error_news_description');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
	
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['news_id']) || ($seo_url['query'] != 'news_id=' . $this->request->get['news_id']))) {		
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
								$this->error['warning'] = $this->language->get('error_news_description');
								break;
							}
						}
					}
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	
	private function validateCategoryForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['description'] as $language_id => $value) {
			if ((strlen($value['name']) < 3) || (strlen($value['name']) > 250)) {
				$this->error['name'][$language_id] = $this->language->get('error_title_category');
			}
		}
		
		if ($this->request->post['seo_url']) {
			$this->load->model('design/seo_url');
			
			foreach ($this->request->post['seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
							$this->error['warning'] = $this->language->get('error_news_description');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
	
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['category_id']) || ($seo_url['query'] != 'news_category_id=' . $this->request->get['category_id']))) {		
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
								$this->error['warning'] = $this->language->get('error_news_description');
								break;
							}
						}
					}
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validatePermission() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validateReset() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_news')) {
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