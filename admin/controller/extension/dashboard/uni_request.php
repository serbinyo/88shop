<?php
class ControllerExtensionDashboardUniRequest extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/uni_request');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_uni_request', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/uni_request', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/uni_request', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_uni_request_width'])) {
			$data['dashboard_uni_request_width'] = $this->request->post['dashboard_uni_request_width'];
		} else {
			$data['dashboard_uni_request_width'] = $this->config->get('dashboard_uni_request_width');
		}
		
		if (isset($this->request->post['dashboard_uni_request_limit'])) {
			$data['dashboard_uni_request_limit'] = $this->request->post['dashboard_uni_request_limit'];
		} else {
			$data['dashboard_uni_request_limit'] = $this->config->get('dashboard_uni_request_limit');
		}

		$data['columns'] = array();
		
		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}
				
		if (isset($this->request->post['dashboard_uni_request_status'])) {
			$data['dashboard_uni_request_status'] = $this->request->post['dashboard_uni_request_status'];
		} else {
			$data['dashboard_uni_request_status'] = $this->config->get('dashboard_uni_request_status');
		}

		if (isset($this->request->post['dashboard_uni_request_sort_order'])) {
			$data['dashboard_uni_request_sort_order'] = $this->request->post['dashboard_uni_request_sort_order'];
		} else {
			$data['dashboard_uni_request_sort_order'] = $this->config->get('dashboard_uni_request_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/uni_request_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/uni_request')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function dashboard() {
		$this->load->language('extension/dashboard/uni_request');
		
		$this->load->model('extension/module/uni_request');

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['heading_title'] = strip_tags($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
       		'text'		=> $this->language->get('text_home'),
			'href'		=> $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], true)
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
       		'text'		=> $this->language->get('heading_title'),
			'href'		=> $this->url->link('extension/module/uni_request', 'user_token='.$this->session->data['user_token'], true)
   		);		

		$data['requests'] = array();

		$filter_data = array(
			'filter_type'   	=> '',
			'filter_name'     	=> '',
			'filter_status'     => '',
			'filter_date_added' => '',
			'sort'              => 'date_added',
			'order'             => 'DESC',
			'start'             => 0,
			'limit'             => $this->config->get('dashboard_uni_request_limit') ? $this->config->get('dashboard_uni_request_limit') : 5,
		);

		if ($this->config->get('uni_request') && $this->user->hasPermission('access', 'extension/module/uni_request')) {
		
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
		
				$data['requests'][] = array(
					'type'   	=> $result['type'],
					'name'     	=> $result['name'],
					'phone'     => $result['phone'],
					'mail'     	=> $result['mail'],
					'date' 		=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'    => $status,
					'action'	=> $this->url->link('extension/module/uni_request/edit', 'user_token=' . $this->session->data['user_token'] . '&request_id='.$result['request_id'], true),
				);
			}
		}

		return $this->load->view('extension/dashboard/uni_request_info', $data);
	}
}
