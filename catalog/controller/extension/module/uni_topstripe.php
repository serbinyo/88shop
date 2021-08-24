<?php
class ControllerExtensionModuleUniTopStripe extends Controller {
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$this->load->language('extension/module/uni_othertext');
		
		$topstripe = isset($uniset['topstripe']['status']) ? $uniset['topstripe'] : [];
		
		$data['topstripe'] = [];
		
		if(!$topstripe || isset($this->request->cookie['topstripeOffTime'])) {
			return;
		}
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/topstripe.css');
		
		if (is_file(DIR_IMAGE . $topstripe['img'])) {
			$image = 'image/'.$topstripe['img'];
		} else {
			$image = '';
		}
		
		$data['topstripe'] = [
			'text'	=> isset($topstripe['text'][$lang_id]) ? html_entity_decode($topstripe['text'][$lang_id], ENT_QUOTES, 'UTF-8') : '',
			'image'	=> $image
		];
		
		return $this->load->view('extension/module/uni_topstripe', $data);
	}
	
	public function apply() {
		$uniset = $this->config->get('config_unishop2');
		
		$time = $uniset['topstripe']['time']*3600;
		
		setcookie('topstripeOffTime', true, time()+$time, '/');
	}
}