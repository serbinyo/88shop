<?php
class ControllerExtensionModuleUniLiveSearch extends Controller {
	public function index() {
		
		if (!isset($this->request->server['HTTP_X_REQUESTED_WITH']) || strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
			
			return;
		}
		
		$this->load->model('extension/module/uni_search');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$this->load->language('extension/module/uni_othertext');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		$currency = $this->session->data['currency'];
			
		$search = isset($this->request->post['filter_name']) ? trim($this->request->post['filter_name']) : '';
		$search_sort = isset($uniset['livesearch']['sort']) ? $uniset['livesearch']['sort'] : '';
		$search_order = isset($uniset['livesearch']['order']) ? $uniset['livesearch']['order'] : '';
			
		$search_description = isset($uniset['search']['types']['description']) ? true : false;
			
		$category_id = isset($this->request->post['category_id']) ? (int)$this->request->post['category_id'] : 0;
			
		$data['categories'] = [];
		$data['manufacturers'] = [];
		$data['products'] = [];
		
		if ($search) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $search,
				'filter_description'  => $search_description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => 1,
				'sort'                => $search_sort,
				'order'               => $search_order,
				'start'               => 0,
				'limit'               => $uniset['livesearch']['limit']
			);
			
			if(isset($uniset['search']['enabled'])) {
				if(isset($uniset['search']['condition']['category'])) {
					$categories = $this->model_extension_module_uni_search->getCategories($filter_data);
	
					foreach ($categories as $category) {
						$data['categories'][] = array(
							'category_id' => $category['category_id'],
							'name' 		  => $category['name'],
							'href'        => $this->url->link('product/category', 'path='.$category['category_id'], true)
						);
					}
				}
				
				if(isset($uniset['search']['condition']['manufacturer'])) {
					$manufacturers = $this->model_extension_module_uni_search->getManufacturers($filter_data);
	
					foreach ($manufacturers as $manufacturer) {
						$data['manufacturers'][] = array(
							'manufacturer_id' => $manufacturer['manufacturer_id'],
							'name' 		  	  => $manufacturer['name'],
							'href'   		  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'], true)
						);
					}
				}
			}
				
			$products = $this->{isset($uniset['search']['enabled']) ? 'model_extension_module_uni_search' : 'model_catalog_product'}->getProducts($filter_data);
			$products_total = $this->{isset($uniset['search']['enabled']) ? 'model_extension_module_uni_search' : 'model_catalog_product'}->getTotalProducts($filter_data);

			foreach ($products as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $uniset['livesearch']['image_w'], $uniset['livesearch']['image_h']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $uniset['livesearch']['image_w'], $uniset['livesearch']['image_h']);
				}
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$price = false;
				}
				
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)round($result['rating']);
				} else {
					$rating = false;
				}
					
				$data['products'][] = array(
					'product_id'  	=> $result['product_id'],
					'image'      	=> isset($uniset['livesearch']['image']) ? $image : '',
					'name' 			=> utf8_substr(strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')), 0, $uniset['livesearch']['name_length']) . '..',
					'description' 	=> isset($uniset['livesearch']['show_description']) ? utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $uniset['livesearch']['description_length']) . '..' : '',
					'rating'		=> isset($uniset['livesearch']['rating']) ? $rating : -1,
					'price'      	=> isset($uniset['livesearch']['price']) ? $price : '',
					'special'     	=> $special,
					'href'       	=> $this->url->link('product/product', 'product_id='.$result['product_id'])
				);
			}
			
			$data['products_total'] = $products_total;
			$data['show_more'] = $products_total > $uniset['livesearch']['limit'] ? true : false;
				
			$link = '&search='.rawurlencode(html_entity_decode($search, ENT_QUOTES, 'UTF-8'));
			$link .= $category_id ? '&category_id='.$category_id.'&sub_category=true' : '';
			$link .= $search_description ? '&description=true' : '';
			//$link .= '&sort='.$search_sort.'&order='.$search_order;
				
			$data['show_more_link'] = $this->url->link('product/search', $link, true);
		}
		
		$this->response->setOutput($this->load->view('extension/module/uni_live_search', $data));
	}
}
