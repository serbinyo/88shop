<?php
class ControllerExtensionModuleUniNotification extends Controller {
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$notification = (!$this->customer->isLogged() && isset($uniset['notification'])) ? $uniset['notification'] : [];
		
		$bot = explode("\r\n", $this->config->get('config_robots'));
		
		if(!$notification || isset($this->request->cookie['notificationOffTime']) || (isset($notification[$lang_id]['text']) && $notification[$lang_id]['text'] == '&lt;p&gt;&lt;br&gt;&lt;/p&gt;')) {
			return;
		}
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/notification.css');

		$data['status'] = $notification['status'];
		$data['text'] = isset($notification[$lang_id]['text']) ? html_entity_decode($notification[$lang_id]['text'], ENT_QUOTES, 'UTF-8') : '';
		$data['apply_text'] = $notification[$lang_id]['apply_text'];
		$data['cancel_text'] = $notification[$lang_id]['cancel_text'];
		$data['cancel_show'] = isset($notification['cancel_show']) ? true : false;
		$data['cancel_close'] = isset($notification['cancel_close']) ? true : false;
		
		return $this->load->view('extension/module/uni_notification', $data);
	}
	
	public function apply() {
		$uniset = $this->config->get('config_unishop2');
		
		$time = $uniset['notification']['time']*3600;
		
		setcookie('notificationOffTime', true, time()+$time, '/');
	}
}