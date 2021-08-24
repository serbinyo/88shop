<?php
class ControllerProductUniReviews extends Controller {
    public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('product/uni_reviews');
		
		$this->load->model('catalog/product');
        $this->load->model('extension/module/uni_reviews');
        $this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/reviews.css');
			
		$data['shop_name'] = $this->config->get('config_name');
		
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$menu_schema = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		$data['menu_expanded'] = ($uniset['menu_type'] == 1 && in_array($route, $menu_schema)) ? true : false;
			
		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : ''; 
		$data['menu_schema'] = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		
		$data['show_grid_button'] = isset($uniset['show_grid_button']) ? true : false;
		$data['show_list_button'] = isset($uniset['show_list_button']) ? true : false;
		$data['show_compact_button'] = isset($uniset['show_compact_button']) ? true : false;
		
		if(isset($uniset['catalog']['limit']['status'])) {
			$new_limit = explode(',', $uniset['catalog']['limit']['value']);
			$limit = $new_limit[0] ? (int)$new_limit[0] : $limit;
	
			$this->config->set('theme_'.$this->config->get('config_theme').'_product_limit', $limit);
		}

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_'.$this->config->get('config_theme').'_product_limit');
		}
		
		$data['limit'] = $limit;

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('product/uni_reviews', $url),
            'separator' => $this->language->get('text_separator')
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_empty'] = $this->language->get('text_empty');

        $data['reviews'] = [];

        $reviews_total = $this->model_extension_module_uni_reviews->getTotalReviews();

        $results = $this->model_extension_module_uni_reviews->getAllReviews(($page - 1) * $limit, $limit, $page);

        foreach ($results as $result) {
            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }

            if ($result['product_id']) {
                $product = $this->model_catalog_product->getProduct($result['product_id']);
				
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_height'));

				$data['reviews'][] = array(
					'review_id'   => $result['review_id'],
					'rating'      => $rating,
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'author'      => $result['author'],
					'product_id'  => $product['product_id'],
					'prod_thumb'  => $image,
					'prod_name'   => $product['name'],
					'prod_model'  => $product['model'],
					'prod_href'   => $this->url->link('product/product', 'product_id=' . $product['product_id']),
				);
			}
        }
		
		$data['limits'] = [];

		$limits = array_unique(array($this->config->get('theme_'.$this->config->get('config_theme').'_product_limit'), 25, 50, 75, 100));
		
		if(isset($uniset['catalog']['limit']['status'])) {
			$new_limits = array_unique(explode(',', $uniset['catalog']['limit']['value']));

			$limits = $new_limits ? $new_limits : $limits;
		}

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('product/uni_reviews', '&limit='.$value)
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit='.(int)$this->request->get['limit'];
		}

        $pagination = new Pagination();
        $pagination->total = $reviews_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('product/uni_reviews', '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($reviews_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($reviews_total - $limit)) ? $reviews_total : ((($page - 1) * $limit) + $limit), $reviews_total, ceil($reviews_total / $limit));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('/product/uni_reviews', $data));
    }
}