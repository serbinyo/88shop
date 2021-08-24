<?php
class ControllerExtensionModuleUniCategoryWall extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$this->load->language('extension/module/uni_category_wall');
	
		$this->load->model('extension/module/uni_category_wall');
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/category_wall.css');
		
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$data['heading_title'] = $setting['title'][$lang_id];
		$data['type'] = isset($setting['type']) ? $setting['type'] : 1;
		$data['type_view'] = isset($setting['view_type']) ? 'carousel' : 'grid';
		$data['columns'] = isset($setting['columns']) ? $setting['columns'] : [6, 4, 3, 3, 2];
		
		$image_width = isset($setting['image_width']) ? $setting['image_width'] : 228;
		$image_height = isset($setting['image_height']) ? $setting['image_height'] : 174;
		
		$data['img_width'] = $image_width;
		$data['img_height'] = $image_height;
		
		$result = isset($setting['categories'][$store_id]) ? $setting['categories'][$store_id] : [];
		
		$cache_name = 'category.unishop.catwall.'.substr(md5($setting['title'][$lang_id].count($result).$lang_id.$store_id), 0, 8);
		
		$categories = $this->cache->get($cache_name);
		
		if(!$categories) {
			$categories = $this->model_extension_module_uni_category_wall->getCategories($result);
			
			if($categories){
				$this->cache->set($cache_name, $categories);
			}
		}
		
		$data['categories'] = [];
		
		foreach($categories as $category) {
			$childs_data = [];
			
			if(isset($category['children'])) {
				foreach($category['children'] as $child) {
					$childs_data[] = array(
						'category_id'	=> $child['category_id'],
						'name' 			=> $child['name'],
						'href' 			=> $this->url->link('product/category', 'path='.$category['category_id'].'_'.$child['category_id'])
					);
				}
			}
				
			if ($category['image']) {
				$image = $this->model_tool_image->resize($category['image'], $image_width, $image_height);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
			}
					
			$data['categories'][] = array(
				'category_id' 	=> $category['category_id'],
				'name' 			=> $category['name'],
				'image' 		=> $image,
				'href'        	=> $this->url->link('product/category', 'path='.$category['category_id']),
				'childs'		=> $childs_data
			);
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/uni_category_wall', $data);
	}
}