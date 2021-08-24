<?php  
class ControllerExtensionModuleUniManufacturer extends Controller {
	public function index() {
		$this->load->language('extension/module/uni_manufacturer');
		
    	$data['heading_title'] = $this->language->get('heading_title');
				
		$this->load->model('catalog/manufacturer');
		
		$data['manufacturer_view_res'] = $this->config->get('uni_manufacturer_view_res');
				
		$results = $this->model_catalog_manufacturer->getManufacturers();
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/manufacturer.css');
	
		foreach ($results as $result) {
			if (is_numeric(utf8_substr($result['name'], 0, 1))) {
				$key = '0 - 9';
			} else {
				$key = utf8_substr(utf8_strtoupper($result['name']), 0, 1);
			}
						
			$data['manufacturers'][$key]['name'] = $key;
			
			$data['manufacturers'][$key]['manufacturer'][] = array(
				'name' => $result['name'],
				'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
			);
			
		}
		
		$data['href'] = $this->url->link('product/manufacturer');

		return $this->load->view('extension/module/uni_manufacturer', $data);
  	}
}
?>