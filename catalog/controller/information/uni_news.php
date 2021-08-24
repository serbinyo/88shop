<?php
class ControllerInformationUniNews extends Controller {

	public function index() {
		$this->language->load('information/uni_news');
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_news');
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$data['shop_name'] = $this->config->get('config_name');
		
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$menu_schema = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		$data['menu_expanded'] = ($uniset['menu_type'] == 1 && in_array($route, $menu_schema)) ? true : false;
		$data['hide_last_breadcrumb'] = isset($uniset['breadcrumbs']['hide']['last']) ? true : false;
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/news.css');
		
		$settings = $this->config->get('uni_news');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'href'		=> $this->url->link('common/home'),
			'text'		=> $this->language->get('text_home')
		);
		
		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'n.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}
			
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else { 
			$page = 1;
		}
			
		if (isset($this->request->get['limit']) && (int)$this->request->get['limit'] > 0) {
			$limit = (int)$this->request->get['limit'];
		} else { 
			$limit = $this->config->get('theme_'.$this->config->get('config_theme').'_product_limit');
			
			if(isset($uniset['catalog']['limit']['status'])) {
				$new_limit = explode(',', $uniset['catalog']['limit']['value']);
	
				$limit = $new_limit[0] ? (int)$new_limit[0] : $limit;
			}
		}
		
		if (isset($this->request->get['news_path'])) {
			
			$path = '';

			$parts = explode('_', (string)$this->request->get['news_path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_extension_module_uni_news->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('information/uni_news', 'news_path='.$path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}
		
		$category_info = $this->model_extension_module_uni_news->getCategory($category_id);

		if ($category_info) {

			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('information/uni_news', 'news_path='.$this->request->get['news_path'])
			);
			
			if ($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
				if(method_exists('document', 'setOgImage')) {
					$this->document->setOgImage($data['thumb']);
				}
			} else {
				$data['thumb'] = '';
			}
			
			$this->document->setTitle($category_info['name']);
			
			if ($category_info['meta_title']) {
				$this->document->setTitle($category_info['meta_title']);
			} else {
				$this->document->setTitle($category_info['name']);
			}
			
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);
			
			if ($category_info['meta_h1']) {
				$data['heading_title'] = $category_info['meta_h1'];
			} else {
				$data['heading_title'] = $category_info['name'];
			}
			
			$data['description'] = ($category_info['description'] != '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') ? html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8') : '';
			$data['subcategory_column'] = isset($settings['subcategory_column']) ? implode(' ', $settings['subcategory_column']) : '';
			
			$data['categories'] = [];
			
			$img_width = isset($settings['image_width']) ? $settings['image_width'] : 220;
			$img_height = isset($settings['image_height']) ? $settings['image_height'] : 160;
			
			$data['img_width'] = $img_width;
			$data['img_height'] = $img_height;

			$results = $this->model_extension_module_uni_news->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);
				
				if ($result['image']) {
					$thumb = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
				} else {
					$thumb = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
				}

				$data['categories'][] = array(
					'name'	=> $result['name'],
					'thumb' => $thumb,
					'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '_' . $result['category_id'] . $url)
				);
			}
		
			$filter_data = array(
				'filter_category_id'	=> $category_id,
				'filter_sub_category' 	=> isset($settings['subcategory']) ? true : false,
				'sort'               	=> $sort,
				'order'              	=> $order,
				'limit'					=> $limit,
				'start'					=> $limit * ($page - 1),
			);
			
			$data['news_data'] = [];
		
			$news_total = $this->model_extension_module_uni_news->getTotalNews($filter_data);
			$news_data = $this->model_extension_module_uni_news->getNews($filter_data);

			foreach ($news_data as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $img_width, $img_height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $img_width, $img_height);
				}
				
				$data['news_data'][] = array(
					'id'  				=> $result['news_id'],
					'image'  			=> $image,
					'name'				=> $result['name'],
					'description'		=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $settings['chars']) . '..',
					'href'        		=> $this->url->link('information/uni_news_story', 'news_path='.$this->request->get['news_path'].'&news_id='.$result['news_id'] . $url),
					'viewed' 			=> $result['viewed'],
					'posted'			=> date($this->language->get('date_format_short'), strtotime($result['date_added']))
				);
			}
			
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . (int)$this->request->get['limit'];
			}

			$data['sorts'] = [];
			
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_date_desc'),
				'value' => 'n.date_added-DESC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=n.date_added&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_date_asc'),
				'value' => 'n.date_added-ASC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=n.date_added&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'nd.name-ASC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=nd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'nd.name-DESC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=nd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_viewed_asc'),
				'value' => 'n.viewed-ASC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=n.viewed&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_viewed_desc'),
				'value' => 'n.viewed-DESC',
				'href' 	=> $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . '&sort=n.viewed&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = [];

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));
			
			if(isset($uniset['catalog']['limit']['status'])) {
				$new_limits = array_unique(explode(',', $uniset['catalog']['limit']['value']));

				$limits = $new_limits ? $new_limits : $limits;
			}

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
				
			$pagination = new Pagination();
			$pagination->total = $news_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('information/uni_news', 'news_path=' . $this->request->get['news_path'] . $url . '&page={page}', true);
			$data['pagination'] = $pagination->render();
			
			$data['results'] = sprintf($this->language->get('text_pagination'), ($news_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($news_total - $limit)) ? $news_total : ((($page - 1) * $limit) + $limit), $news_total, ceil($news_total / $limit));
			
			if ($page == 1) {
			    $this->document->addLink($this->url->link('information/uni_news', 'news_path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('information/uni_news', 'news_path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($news_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('information/uni_news', 'news_path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}
			
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			
			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$this->language->load('information/uni_news');
			
			$this->response->setOutput($this->load->view('information/uni_news', $data));
		} else {		
			$this->document->setTitle($this->language->get('text_error_category'));
			
	     	$data['heading_title'] = $this->language->get('text_error_category');
			$data['text_error'] = $this->language->get('text_error_category');
				
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
				
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
				
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
?>