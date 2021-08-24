<?php
class ControllerExtensionModuleUniManufacturer extends Controller {
	private $error = array(); 
	
	public function index() {   
		$this->load->language('extension/module/uni_manufacturer');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('setting/module');
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_uni_manufacturer', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
				
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_view_resolution'] = $this->language->get('entry_view_resolution');
		$data['text_res_768'] = $this->language->get('text_res_768');
		$data['text_res_992'] = $this->language->get('text_res_992');
		$data['text_res_1200'] = $this->language->get('text_res_1200');
		
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'),
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/uni_manufacturer', 'user_token=' . $this->session->data['user_token'], 'SSL'),
   		);
		
		$data['action'] = $this->url->link('extension/module/uni_manufacturer', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);
		
		if (isset($this->request->post['module_uni_manufacturer_view_res'])) {
			$data['uni_manufacturer_view_res'] = $this->request->post['module_uni_manufacturer_view_res'];
		} else {
			$data['uni_manufacturer_view_res'] = $this->config->get('module_uni_manufacturer_view_res');
		}
		
		if (isset($this->request->post['module_uni_manufacturer_status'])) {
			$data['uni_manufacturer_status'] = $this->request->post['module_uni_manufacturer_status'];
		} else {
			$data['uni_manufacturer_status'] = $this->config->get('module_uni_manufacturer_status');
		}
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_manufacturer', $data));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_manufacturer')) {
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