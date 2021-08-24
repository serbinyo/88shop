<?php
class ControllerExtensionModuleUniNewsRelated extends Controller {
	public function index() {
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_news');
		
		$this->load->model('extension/module/uni_news');
		$this->load->model('tool/image');
		
		$settings = $this->config->get('uni_news');
		
		$data['news'] = [];
		
		if(isset($settings['image_width'])) {
			$thumb_width = $settings['image_width'];
			$thumb_height = $settings['image_height'];
			$numchars = $settings['chars'];
		} else {
			$thumb_width = 320;
			$thumb_height = 240;
			$numchars = 140; 
		}
			
		$numchars = 140;
		
		$data['img_width'] = $thumb_width;
		$data['img_height'] = $thumb_height;
		
		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		
		$results = $this->model_extension_module_uni_news->getNewsStoryforRelatedProduct($product_id);
		
		if($results) {
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/news.css');

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $thumb_width, $thumb_height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $thumb_width, $thumb_height);
				}

				$description = utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $numchars) . '..';
				
				$news_category = $this->model_extension_module_uni_news->getCategory($result['category_id']);

				$data['news'][] = array(
					'name'        	=> $result['name'],
					'image'			=> $image,
					'description'	=> $description,
					'href'         	=> $this->url->link('information/uni_news_story', 'news_id=' . $result['news_id']),
					'viewed'   		=> $result['viewed'],
					'posted'   		=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'category_name' => isset($news_category['name']) ? $news_category['name'] : '',
					'category_href' => isset($news_category['category_id']) ? $this->url->link('information/uni_news', 'news_path='.$news_category['category_id']) : '',
				);
			}
		}

		return $this->load->view('extension/module/uni_news_related', $data);
	}
}
?>