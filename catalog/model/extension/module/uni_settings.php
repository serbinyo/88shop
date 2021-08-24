<?php
class ModelExtensionModuleUniSettings extends Model {	
	public function getSetting() {
		$store_id = (int)$this->config->get('config_store_id');
		
		$data = $this->cache->get('unishop.settings.'.$store_id);
		
		if (!$data) {
			if($this->config->get('cache_engine') == 'file') {
				$cache = new Cache('file', 60*60*24*5);
			}
			
			$data = [];
			
			$query = $this->db->query("SELECT data FROM `".DB_PREFIX."uni_setting` WHERE store_id = '".$store_id."'");
			
			if($query->rows) {
				$data = json_decode($query->row['data'], true);
				
				if($this->config->get('cache_engine') == 'file') {
					$cache->set('unishop.settings.'.$store_id, $data);
				} else {
					$this->cache->set('unishop.settings.'.$store_id, $data);
				}
				
				$this->removeFiles();
				
				$this->cache->delete('product.unishop');
			}
		}
		
		$this->config->set('config_unishop2', $data);
	}
	
	private function removeFiles() {
		$store_id = (int)$this->config->get('config_store_id');
		
		$files_arr = ['stylesheet/merged*', 'stylesheet/generated*', 'js/merged*', 'js/install-sw*', 'manifest/manifest*'];
		
		$files = [];
		
		foreach($files_arr as $file) {
			$files = array_merge($files, glob(DIR_TEMPLATE.'unishop2/'.$file));
		}
		
		$files[] = 'uni-sw.'.$store_id.'.js';
		
		if($files) {
			foreach($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}
		}
	}
}
?>