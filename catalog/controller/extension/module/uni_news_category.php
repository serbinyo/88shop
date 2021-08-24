<?php
class ControllerExtensionModuleUniNewsCategory extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_news');
		
		$this->load->model('extension/module/uni_news');
		
		$lang_id = $this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		if (isset($this->request->get['news_path'])) {
			$data['category_arr'] = explode('_', (string)$this->request->get['news_path']);
		} else {
			$data['category_arr'] = [];
		}
		
		$cache_name = 'unishop.news.category.module.'.$lang_id.'.'.$store_id;
		
		$data['categories'] = $this->cache->get($cache_name);
		
		if(!$data['categories']) {
			
			$categories = $this->model_extension_module_uni_news->getCategories();

			foreach ($categories as $category) {
				
				$children_data = [];

				$children = $this->model_extension_module_uni_news->getCategories($category['category_id']);
				
				if($children) {
					foreach($children as $child) {
						$children_data[] = array(
							'category_id' => $child['category_id'],
							'name' 		  => $child['name'],
							'href'		  => $this->url->link('information/uni_news', 'news_path='.$category['category_id'].'_'.$child['category_id'])
						);
					}
				}

				$data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'],
					'children'	  => $children_data,
					'href'     	  => $this->url->link('information/uni_news', 'news_path='.$category['category_id']),
				);
			}
		
			if($data['categories']) {
				$this->cache->set($cache_name, $data['categories']);
			}
		}
		
		$data['module'] = $module++;

		return $this->load->view('extension/module/uni_news_category', $data);
	}
}
?>