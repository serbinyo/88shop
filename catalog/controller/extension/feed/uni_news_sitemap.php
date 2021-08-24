<?php
class ControllerExtensionFeedUniNewsSitemap extends Controller {
	public function index() {
		
		$settings = $this->config->get('uni_news');
		
		if (isset($settings['sitemap'])) {
			$this->load->model('extension/module/uni_news');
			$this->load->model('localisation/language');
			
			$output  = '<?xml version="1.0" encoding="UTF-8"?>';
			
			$output .= '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="https://www.w3.org/1999/xhtml">';
			//$output .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.w3.org/TR/xhtml11/xhtml11_schema.html http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/TR/xhtml11/xhtml11_schema.html">';
			//$output .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" >';

			$output .= $this->getCategories(0);

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategories($parent_id, $current_path = '') {
		$output = '';
		
		$lang_id = $this->config->get('config_language_id');
		$languages = $this->model_localisation_language->getLanguages();
		$results = $this->model_extension_module_uni_news->getCategories($parent_id);
	
		foreach ($results as $result) {
			if (!$current_path) {
				$news_path = $result['category_id'];
			} else {
				$news_path = $current_path.'_'.$result['category_id'];
			}

			$output .= '<url>';
			$output .= '<loc>'.$this->url->link('information/uni_news', 'news_path='.$news_path).'</loc>';
			
			if(count($languages) > 1) {
				foreach($languages as $lang){
					$this->config->set('config_language_id', $lang['language_id']);	
					$output .= '<xhtml:link rel="alternate" hreflang="'.$lang['code'].'" href="'.$this->url->link('information/uni_news', 'news_path='.$news_path).'" />';
				}
			
				$this->config->set('config_language_id', $lang_id);
			}
			
			$output .= '<changefreq>weekly</changefreq>';
			$output .= '<priority>0.7</priority>';
			$output .= '</url>';

			$news = $this->model_extension_module_uni_news->getNews(array('filter_category_id' => $result['category_id']));
			
			foreach ($news as $news) {
				$output .= '<url>';
				$output .= '<loc>'.$this->url->link('information/uni_news_story', 'news_path='.$news_path.'&news_id='.$news['news_id']).'</loc>';
				
				if(count($languages) > 1) {
					foreach($languages as $lang){
						$this->config->set('config_language_id', $lang['language_id']);	
						$output .= '<xhtml:link rel="alternate" hreflang="'.$lang['code'].'" href="'.$this->url->link('information/uni_news_story', 'news_path='.$news_path . '&news_id='.$news['news_id']).'" />';
					}
				
					$this->config->set('config_language_id', $lang_id);
				}
				
				//$output .= '<lastmod>'.date('Y-m-d\TH:i:sP', strtotime($news['date_added'])).'</lastmod>';
				$output .= '<lastmod>'.date('c', strtotime($news['date_added'])).'</lastmod>';
				$output .= '<changefreq>weekly</changefreq>';
				$output .= '<priority>1.0</priority>';
				$output .= '</url>';
			}

			$output .= $this->getCategories($result['category_id'], $news_path);
		}

		return $output;
	}
}
