<?php
class ControllerExtensionModuleUniDownload extends Controller {
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$this->load->language('extension/module/uni_download');
		
		$status = isset($uniset['product']['download_tab']['status']) ? $uniset['product']['download_tab']['status'] : 0;
		
		$data['downloads'] = [];
		
		if($status) {
		
			$only_customer = ($status >= 2) ? true : false;
		
			if(!$only_customer || ($only_customer && $this->customer->isLogged())) {
		
				$this->load->model('extension/module/uni_download');
		
				$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		
				$start = 0;
				$limit = 50;

				$results = $this->model_extension_module_uni_download->getDownloads($product_id, $start, $limit);

				foreach ($results as $result) {
					if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
						$size = filesize(DIR_DOWNLOAD . $result['filename']);

						$i = 0;

						$suffix = ['B', 'KB', 'MB', 'GB', 'TB'];

						while (($size / 1024) > 1) {
							$size = $size / 1024;
							$i++;
						}
					
						$extension = strtolower(pathinfo(DIR_DOWNLOAD . $result['mask'], PATHINFO_EXTENSION));

						if(in_array($extension, ['jpg', 'png', 'gif', 'webp', 'bmp'])) {
							$icon = 'file-image';
						} elseif (in_array($extension, ['mp4', 'avi', 'mkv', 'wmv', 'flv', 'mpeg'])) {
							$icon = 'file-video';
						} elseif (in_array($extension, ['mp3', 'wav', 'midi', 'aac', 'flac'])) {
							$icon = 'file-audio';
						} elseif (in_array($extension, ['zip', 'rar', '7z', 'gzip'])) {
							$icon = 'file-archive';
						} elseif (in_array($extension, ['doc', 'docx'])) {
							$icon = 'file-word';
						} elseif (in_array($extension, ['xls', 'xlr'])) {
							$icon = 'file-excel';
						} elseif ($extension == 'pdf') {
							$icon = 'file-pdf';
						} else {
							$icon = 'file-alt';
						}

						$data['downloads'][] = array(
							'name'       => $result['name'],
							'size'       => round(substr($size, 0, strpos($size, '.') + 4), 2) .' '. $suffix[$i],
							'icon'	 	 => $icon,
							//'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
							'href'       => $href = $this->url->link('extension/module/uni_download/download', 'pid='.$product_id.'&did='.$result['download_id'], true)
						);
					}
				}
			}
		}
		
		return $this->load->view('extension/module/uni_download', $data);
	}
	
	public function download() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$status = isset($uniset['product']['download_tab']['status']) ? $uniset['product']['download_tab']['status'] : 0;
		
		if($status) {
		
			$only_customer = ($status >= 2) ? true : false;
		
			if(!$only_customer || ($only_customer && $this->customer->isLogged())) {

				$this->load->model('extension/module/uni_download');
			
				$product_id = isset($this->request->get['pid']) ? (int)$this->request->get['pid'] : 0;
				$download_id = isset($this->request->get['did']) ? (int)$this->request->get['did'] : 0;

				$download_info = $this->model_extension_module_uni_download->getDownload($product_id, $download_id);

				if ($download_info) {
					$file = DIR_DOWNLOAD . $download_info['filename'];
					$mask = basename($download_info['mask']);
					$extension = strtolower(pathinfo($mask, PATHINFO_EXTENSION));

					if (!headers_sent()) {
						if (file_exists($file)) {
							if($extension == 'pdf') {
								$content_type = 'application/pdf';
								$content_disposition = 'inline';
							} else {
								$content_type = 'application/octet-stream';
								$content_disposition = 'attachment';
							}
							
							header('Content-Type: ' . $content_type);
							header('Content-Disposition: ' . $content_disposition . '; filename = "' . ($mask ? $mask : basename($file)) . '"');
							header('Expires: 0');
							header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
							header('Pragma: public');
							header('Content-Length: ' . filesize($file));

							if (ob_get_level()) {
								ob_end_clean();
							}

							readfile($file, 'rb');

							exit();
						} else {
							exit('Error: Could not find file ' . $file . '!');
						}
					} else {
						exit('Error: Headers already sent out!');
					}
				} else {
					$this->return404();
				}
			} else {
				$this->return404();
			}
		} else {
			$this->return404();
		}
	}
	
	private function return404() {
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