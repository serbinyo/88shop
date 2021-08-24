<?php
class ControllerExtensionModuleUniReviews extends Controller {
    public function index($setting) {
		static $module = 0;
		
		$this->load->language('extension/module/uni_reviews');
		
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->model('extension/module/uni_reviews');
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/reviews.css');

		$data['heading_title'] = isset($setting['title'][$lang_id]) ? $setting['title'][$lang_id] : $this->language->get('heading_title');

        $limit = $setting['limit'] > 0 ? $setting['limit'] : 4;
        $text_limit = $setting['text_limit'] > 0 ? $setting['text_limit'] : 50;
		$image_width = isset($setting['image_width']) ? $setting['image_width'] : 100;
		$image_height = isset($setting['image_height']) ? $setting['image_height'] : 100;
		
		$data['img_width'] = $image_width;
		$data['img_height'] = $image_height;
		
		$data['type_view'] = isset($setting['view_type']) ? 'grid' : 'carousel';

        if (isset($setting['category_sensitive']) && !empty($this->request->get['path'])){
            $categories = explode('_', $this->request->get['path']);
            $category_id = (int)array_pop($categories);
        } else {
            $category_id = 0;
        }
		
		$data['reviews'] = [];

        $results = $setting['order_type'] == 'last' ? $this->model_extension_module_uni_reviews->getLatestReviews($limit, $category_id) : $this->model_extension_module_uni_reviews->getRandomReviews($limit, $category_id);
		
        foreach ($results as $result) {
			$rating = $this->config->get('config_review_status') ? $result['rating'] : '';
             
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $image_width, $image_height);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
			}
			
			$link_all_reviews = isset($setting['show_all_button_link']) ? $this->url->link('product/product', 'product_id=' . $result['product_id'], true).'#tab-review' : $this->url->link('product/uni_reviews', '', true);
			
            $data['reviews'][] = array(
                'thumb' 			=> $image,
                'name'  			=> $result['name'],
                'rating'      		=> $rating,
				'description' 		=> utf8_substr(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8')), 0, $text_limit) . '..',
                'date_added' 		=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'href'       		=> $this->url->link('product/product', 'product_id=' . $result['product_id']),
                'author'      		=> $result['author'],
				'link_all_reviews' 	=> $link_all_reviews
            );
        }
		
        $data['text_all_reviews'] = $this->language->get('text_all_reviews');
		$data['show_all_button'] = isset($setting['show_all_button']) ? $setting['show_all_button'] : '';
		
		$data['module'] = $module++;
		
		return $this->load->view('extension/module/uni_reviews', $data);
    }
}