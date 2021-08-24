<?php
class ControllerExtensionModuleUniPwa extends Controller {
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$data['pwa_notification'] = [];
		
		if(!isset($uniset['pwa']['status']) || !$uniset['pwa']['icon'] || !$this->request->server['HTTPS']) {
			return;
		}
		
		$sw_name = 'uni-sw.'.$store_id.'.js';
		
		$this->setManifest();
		$this->installSW($sw_name);
		$this->setSW($sw_name);
		
		if(!isset($this->request->cookie['pwaOffTime'])) {
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/pwa.css');
			
			//$this->document->addScript('catalog/view/theme/unishop2/js/pwacompat.js');
			
			$data['notification'] = [
				'text_chromium' => html_entity_decode($uniset['pwa']['banner']['text_chromium'][$lang_id], ENT_QUOTES, 'UTF-8'),
				'text_other'	=> html_entity_decode($uniset['pwa']['banner']['text_other'][$lang_id], ENT_QUOTES, 'UTF-8'),
				'text_install'	=> $this->language->get('text_pwa_install'),
				'text_close'	=> $this->language->get('text_pwa_not_now')
			];
		}
		
		
		
		return $this->load->view('extension/module/uni_pwa_notification', $data);
	}
	
	public function setPwaBannerTopTimeOff() {
		$uniset = $this->config->get('config_unishop2');
		$time = $uniset['pwa']['banner']['time']*3600;
		
		setcookie('pwaOffTime', true, time()+$time, '/');
	}
	
	public function fallbackPage() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$data = [];
		
		if(isset($uniset['pwa']['status'])) {
			if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
				$icon = $this->config->get('config_ssl') . 'image/' . $this->config->get('config_icon');
			} else {
				$icon = '';
			}
			
			$style  = 'a {color:#'.$uniset['a_color'].'}';
			$style .= '.btn-primary {color:#'.$uniset['btn_primary_color'].';background:#'.$uniset['btn_primary_bg'].'}';
			$style .= '.btn-primary:hover, .btn-primary:focus {color:#'.$uniset['btn_primary_color_hover'].';background:#'.$uniset['btn_primary_bg_hover'].'}';
			
			$data['result'] = [
				'title' 		=> $uniset['pwa']['fallbackpage']['title'][$lang_id],
				'icon'			=> $icon,
				'style' 		=> $style,
				'font'			=> $uniset['font'],
				'image'			=> $this->getImg(),
				'description' 	=> html_entity_decode(trim($uniset['pwa']['fallbackpage']['description'][$lang_id]), ENT_QUOTES, 'UTF-8')
			];
		} else {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
		}
		
		$this->response->setOutput($this->load->view('extension/module/uni_pwa_fallback_page', $data));
	}
	
	private function setManifest() {
		$uniset = $this->config->get('config_unishop2');
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$this->load->model('tool/image');
		
		$manifest = 'catalog/view/theme/unishop2/manifest/manifest.'.$lang_id.'.'.$store_id.'.json';
		
		if (!file_exists($manifest)) {
			$img_sizes = [16, 32, 72, 76, 96, 128, 144, 152, 192, 384, 512];
		
			$icons = '';
			
			$image_old = $uniset['pwa']['icon'];
			$extension = pathinfo($image_old, PATHINFO_EXTENSION);
				
			foreach($img_sizes as $key => $size) {
				$icons .= ' {"src": "'.$this->getImg($size).'", "type": "image/png", "sizes": "'.$size.'x'.$size.'"'. ($key + 1 == count($img_sizes) ? ', "purpose": "any maskable"}' : '},');
			}
		
			$manifest_data = '{
				"dir": "ltr",
				"lang": "'.$this->language->get('code').'",
				"name": "'.$uniset['pwa']['name'][$lang_id].'",
				"short_name": "'.$uniset['pwa']['short_name'][$lang_id].'",
				"scope": "/",
				"display": "standalone",
				"start_url": "/",
				"background_color": "#ffffff",
				"theme_color": "#'.(($uniset['menu_type'] == 1) ? $uniset['main_menu_bg'] : $uniset['main_menu2_bg']).'",
				"orientation": "any",
				"related_applications": [],
				"prefer_related_applications": false,
				"icons": ['.$icons.'],
				"url": "/"
			}';
		
			file_put_contents($manifest, $manifest_data);
		}
		
		$this->document->addLink($manifest, 'manifest');
		$this->document->addLink($this->getImg(152), 'apple-touch-icon');
		
		//if(method_exists('document', 'getOgImage') && !$this->document->getOgImage()) {
		//	$this->document->setOgImage($this->getImg(512));
		//}
	}
	
	private function getImg($size = 512){
		//it's all here, because the modules for converting images to webp format can be installed 
		
		$uniset = $this->config->get('config_unishop2');
		
		$image_old = $uniset['pwa']['icon'];
		
		if(!file_exists(DIR_IMAGE.$image_old)) {
			return;
		}
		
		$size = (int)$size;
		
		$extension = pathinfo($image_old, PATHINFO_EXTENSION);
		
		$image_new = 'cache/' . utf8_substr($image_old, 0, utf8_strrpos($image_old, '.')) . '-' . $size . 'x' . $size . '.' . $extension;
				
		if(!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}
					
			$image = new Image(DIR_IMAGE . $image_old);
			$image->resize($size, $size);
			$image->save(DIR_IMAGE . $image_new);
		}
		
		return $this->config->get('config_ssl') . 'image/'.$image_new;
	}
	
	private function installSW($sw_name) {
		$uniset = $this->config->get('config_unishop2');
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$date = isset($uniset['save_date']) ? $uniset['save_date'] : strtotime('now');
		
		$install_sw_name = 'catalog/view/theme/unishop2/js/install-sw.'.$store_id.'.js';
		
		if (!file_exists($install_sw_name )) {
			$install_sw_data = '
				const swUrl = "'.$sw_name.'";
				const userAgent = navigator.userAgent.toLowerCase();
				const touchSupport = (\'ontouchstart\' in document.documentElement);
				const pwaOffTimeCookie = document.cookie.match(\'pwaOffTime\') ? true : false;
				
				let displayStandalone = window.matchMedia(\'(display-mode:standalone)\').matches;
				
				if((\'standalone\' in navigator) && navigator.standalone) {
					displayStandalone = true;
				}

				window.addEventListener(\'load\', function() {
					if (\'serviceWorker\' in navigator) {
						if (!navigator.serviceWorker.controller) {
							navigator.serviceWorker.register(swUrl, {scope: "/"});
						}
					}
		
					function showNetworkStatusAlert() {
						if(navigator.onLine) {
							uniFlyAlert(\'success\', uniJsVars.pwa.text_online);
						} else {
							uniFlyAlert(\'danger\', uniJsVars.pwa.text_offline);
						}
					}
					
					window.addEventListener(\'online\', showNetworkStatusAlert);
					window.addEventListener(\'offline\', showNetworkStatusAlert);
					
					$(document).ajaxError(function() {
						if(!navigator.onLine) {
							uniFlyAlert(\'danger\', uniJsVars.pwa.text_offline);
						}
					});
					
					if((/iphone|ipad|ipod|firefox|opr/.test(userAgent)) && touchSupport) {
						uniShowPWABanner(\'other\');
					}
					
					uniReloadSW();
				});
				
				let deferredPrompt;

				window.addEventListener(\'beforeinstallprompt\', function(e) {
					e.preventDefault();

					deferredPrompt = e;
					
					uniShowPWABanner(\'chromium\');
							
					$(document).on(\'click\', \'.pwa-notification__install\', function() {
						deferredPrompt.prompt();
					});
				});
				
				window.addEventListener(\'appinstalled\', function() {
					$(\'.pwa-notification .container\').removeClass(\'active\');
					deferredPrompt = null;
				});
				
				function uniShowPWABanner(newClass){
					if (!pwaOffTimeCookie && !displayStandalone) {
						if(newClass == \'chromium\') $(\'.pwa-notification\').removeClass(\'other\');
							
						$(\'header\').before($(\'.pwa-notification\').removeClass(\'hidden\').addClass(newClass));
						
						setTimeout(function() {			
							$(\'.pwa-notification .container\').addClass(\'active\');
						}, 50);
							
						$(document).on(\'click\', \'.pwa-notification__close\', function() {
							$.get(\'index.php?route=extension/module/uni_pwa/setPwaBannerTopTimeOff\');
							$(\'.pwa-notification .container\').removeClass(\'active\');
						});
					}
				}
				
				function uniSendNotification(title, options) {
					if (\'Notification\' in window) {
						navigator.serviceWorker.ready.then(function(reg) {
							if (Notification.permission === \'granted\') {
								reg.showNotification(title, options);
							} else if (Notification.permission !== \'denied\') {
								Notification.requestPermission(function (permission) {
									if (permission === "granted") {
										reg.showNotification(title, options);
									}
								});
							}
						});
					}
				}
				
				function uniReloadSW() {
					if (\'serviceWorker\' in navigator) {
						navigator.serviceWorker.ready.then(function(reg) {
							if (reg.waiting) {
								reg.waiting.postMessage({ type: \'SKIP_WAITING\' });
							
								if (displayStandalone) {
									uniSendNotification(\'Update\', {
										body: uniJsVars.pwa.text_reload,
										vibrate: [100, 50, 100],
										tag: \'uniReloadSW\'
									});
								};
								
								uniDelPageCache();
							};
						});
					};
				};
				
				function uniDelPageCache() {
					caches.keys().then(function(cacheNames) {
						cacheNames.forEach(function(cacheName) {
							if(cacheName == \'page\') {
								caches.delete(cacheName);
							}
						});
					});
				};
				
				$(document).on(\'click\', \'.top-menu__currency-item, .top-menu__language-item\', function() {
					uniDelPageCache();
				});
			';
			
			file_put_contents($install_sw_name, $install_sw_data);
		}
		
		$this->document->addScript($install_sw_name.'?v='.$date);
		//$this->document->addScript($install_sw_name);
	}
	
	private function setSW($sw_name) {
		$uniset = $this->config->get('config_unishop2');
		
		if (file_exists($sw_name)) {
			return;
		}
		
		$sw_data = '
			importScripts("https://storage.googleapis.com/workbox-cdn/releases/6.1.5/workbox-sw.js");
			
			const pageCache = "page";
			const jsCache = "js";
			const cssCache = "css";
			const imgCache = "img";
			const fontCache = "fonts";
			const preCache = "fallback";
			const fallbackPage = "index.php?route=extension/module/uni_pwa/fallbackPage";
			const fallbackCss = "catalog/view/theme/unishop2/stylesheet/stylesheet.css";
			const fallbackFontCss = "catalog/view/theme/unishop2/stylesheet/'.$uniset['font'].'.css";
			const fallbackImg = "'.$this->getImg().'";
			
			self.addEventListener(\'activate\', async (event) => {
				event.waitUntil(
					caches.open(preCache).then(function(cache) {
						cache.addAll([
							fallbackPage,
							fallbackCss,
							fallbackFontCss,
							fallbackImg
						]);
					})
				);
				
				event.waitUntil(clients.claim());
			});

			self.addEventListener(\'message\', async (event) => {
				if (event.data && event.data.type === "SKIP_WAITING") {
					self.skipWaiting();
					
					caches.keys().then(cacheNames => {
						cacheNames.forEach(cacheName => {
							caches.delete(cacheName);
						});
					});
				}
			});
			
			const documentStrategy = \''.$uniset['pwa']['cache']['document']['strategy'].'\';

			if (documentStrategy == \'NetworkFirst\' && workbox.navigationPreload.isSupported()) {
				//workbox.navigationPreload.enable();
			}

			workbox.routing.registerRoute(
				({event, url}) => event.request.destination === \'document\' && !(/account|wishlist|compare|checkout|cart|admin|captcha/i.test(url.href)),
				new workbox.strategies.'.$uniset['pwa']['cache']['document']['strategy'].'({
					cacheName: pageCache,
					plugins: [
						new workbox.expiration.ExpirationPlugin({
							maxEntries: '.$uniset['pwa']['cache']['document']['items'].',
							maxAgeSeconds: 60 * 60 * 24 * '.$uniset['pwa']['cache']['document']['lifetime'].',
						}),
					],
				})
			);

			workbox.routing.registerRoute(
				({event, url}) => event.request.destination === \'script\' && !url.pathname.startsWith(\'/admin/\'),
				new workbox.strategies.'.$uniset['pwa']['cache']['script']['strategy'].'({
					cacheName: jsCache,
					plugins: [
						new workbox.expiration.ExpirationPlugin({
							maxEntries: '.$uniset['pwa']['cache']['script']['items'].',
							maxAgeSeconds: 60 * 60 * 24 * '.$uniset['pwa']['cache']['script']['lifetime'].',
						}),
					],
				})
			);

			workbox.routing.registerRoute(
				({event, url}) => event.request.destination === \'style\' && !url.pathname.startsWith(\'/admin/\'),
				new workbox.strategies.'.$uniset['pwa']['cache']['style']['strategy'].'({
					cacheName: cssCache,
					plugins: [
						new workbox.expiration.ExpirationPlugin({
							maxEntries: '.$uniset['pwa']['cache']['style']['items'].',
							maxAgeSeconds: 60 * 60 * 24 * '.$uniset['pwa']['cache']['style']['lifetime'].',
						}),
					],
				})
			);

			workbox.routing.registerRoute(
				({event, url}) => event.request.destination === \'image\' && !(/captcha/i.test(url.href)),
				new workbox.strategies.'.$uniset['pwa']['cache']['image']['strategy'].'({
					cacheName: imgCache,
					plugins: [
						new workbox.expiration.ExpirationPlugin({
							maxEntries: '.$uniset['pwa']['cache']['image']['items'].',
							maxAgeSeconds: 60 * 60 * 24 * '.$uniset['pwa']['cache']['image']['lifetime'].',
							purgeOnQuotaError: true
						}),
					],
				})
			);

			workbox.routing.registerRoute(
				({event}) => event.request.destination === \'font\',
				new workbox.strategies.CacheFirst({
					cacheName: fontCache,
					plugins: [
						new workbox.expiration.ExpirationPlugin({
							maxEntries: 15,
						}),
					],
				})
			);

			workbox.routing.setCatchHandler(
				({event, url}) => {
					if ((event.request.destination) == \'document\') return caches.match(fallbackPage);
				}
			);
		';
		
		file_put_contents($sw_name, $sw_data);
	}
}