<?php
class ControllerExtensionModuleUniSlideshow extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/uni_slideshow');
		
		//$this->document->addStyle('view/stylesheet/unishop.css');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/module');
		$this->load->model('localisation/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_slideshow', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_slideshow', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_slideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_slideshow', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_slideshow', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

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
		
		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 405;
		}
		
		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 318;
		}
		
		if (isset($this->request->post['effect_in'])) {
			$data['effect_in'] = $this->request->post['effect_in'];
		} elseif (!empty($module_info)) {
			$data['effect_in'] = $module_info['effect_in'];
		} else {
			$data['effect_in'] = '';
		}
		
		if (isset($this->request->post['effect_out'])) {
			$data['effect_out'] = $this->request->post['effect_out'];
		} elseif (!empty($module_info)) {
			$data['effect_out'] = $module_info['effect_out'];
		} else {
			$data['effect_out'] = '';
		}
		
		if (isset($this->request->post['delay'])) {
			$data['delay'] = $this->request->post['delay'];
		} elseif (!empty($module_info)) {
			$data['delay'] = $module_info['delay'];
		} else {
			$data['delay'] = 5;
		}
		
		if (isset($this->request->post['fullwidth'])) {
			$data['fullwidth'] = $this->request->post['fullwidth'];
		} elseif (!empty($module_info['fullwidth'])) {
			$data['fullwidth'] = $module_info['fullwidth'];
		} else {
			$data['fullwidth'] = '';
		}
		
		if (isset($this->request->post['hide'])) {
			$data['hide'] = $this->request->post['hide'];
		} elseif (!empty($module_info['hide'])) {
			$data['hide'] = $module_info['hide'];
		} else {
			$data['hide'] = '';
		}
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('tool/image');
		
		$slides = isset($module_info['slides']) ? $module_info['slides'] : array();
		
		$data['slides'] = array();
		
		foreach($slides as $slide) {
			$data['slides'][] = array(
				'image' 	=> $slide['image'],
				'thumb' 	=> $this->model_tool_image->resize($slide['image'], 100, 100),
				'title' 	=> $slide['title'],
				'text' 		=> $slide['text'],
				'link' 		=> $slide['link'],
				'button'	=> $slide['button'],
				'sort' 		=> $slide['sort']
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
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

		$this->response->setOutput($this->load->view('extension/module/uni_slideshow', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_slideshow')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}