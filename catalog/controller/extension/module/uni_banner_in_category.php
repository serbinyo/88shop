<?php  
class ControllerExtensionModuleUniBannerInCategory extends Controller {
	public function index($category_id = 0) {
		
		$data['banners'] = [];
		
		if($category_id && $this->config->get('module_uni_banner_in_category_status')) {
			$this->load->model('extension/module/uni_banner_in_category');
			
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/banner-in-category.css');
			
			$category_id = (int)$category_id;
			$language_id = (int)$this->config->get('config_language_id');
			$store_id = (int)$this->config->get('config_store_id');
			
			$cache_name = 'unishop.banner.in.category_.'.$category_id.'.'.$language_id.'.'.$store_id;
			
			$data['banners'] = $this->cache->get($cache_name);
			
			if(!$data['banners']) {
				$banners = $this->model_extension_module_uni_banner_in_category->getBanners((int)$category_id);
		
				foreach($banners as $banner) {	
					//$image = $banner['image'] ? $this->model_tool_image->resize($banner['image'], $banner['width'], $banner['height']) : '';
			
					if (is_file(DIR_IMAGE . $banner['image'])) {
						$size = getimagesize(DIR_IMAGE . $banner['image']);
						$image = $this->model_tool_image->resize($banner['image'], $size[0], $size[1]);
					} else {
						$image = '';
					}
			
					$description = ($banner['description'] != '<p><br></p>') && ($banner['description'] != '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') ? html_entity_decode(trim($banner['description']), ENT_QUOTES, 'UTF-8') : '';
						
					$data['banners'][] = [
						'banner_id'   => $banner['banner_id'],
						'name' 		  => $banner['name'],
						'image' 	  => $image,
						'description' => preg_replace('/\r\n|\r|\n/u', '', $description),
						'description' => trim(str_replace(["\r\n", "\r", "\n", "'", '&nbsp;'], ' ',  $description)),
						'button' 	  => $banner['button'],
						'href' 		  => $banner['link'],
						'type' 	  	  => $banner['type'],
						'height'	  => $banner['height'],
						'position' 	  => $banner['position'],
						'position2'   => $banner['position2']
					];
				}
				
				if($data['banners']) {
					$this->cache->set($cache_name, $data['banners']);
				}
			}
		}

		return $this->load->view('extension/module/uni_banner_in_category', $data);
  	}
}
?>