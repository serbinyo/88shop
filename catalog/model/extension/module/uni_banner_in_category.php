<?php
class ModelExtensionModuleUniBannerInCategory extends Model {	
	public function getBanners($category_id) {
		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');
		$category_id = (int)$category_id;
			
		$sql = "SELECT * FROM `".DB_PREFIX."uni_banner_in_category` b LEFT JOIN `".DB_PREFIX."uni_banner_in_category_description` bd ON (b.banner_id = bd.banner_id) LEFT JOIN `".DB_PREFIX."uni_banner_in_category_to_category` b2c ON (b.banner_id = b2c.banner_id) LEFT JOIN `".DB_PREFIX."uni_banner_in_category_to_store` b2s ON (b.banner_id = b2s.banner_id)";
		$sql .= " WHERE bd.language_id = '".(int)$language_id."' AND b2c.category_id = '".(int)$category_id."' AND ((b.date_start = '0000-00-00' OR b.date_start < NOW()) AND (b.date_end = '0000-00-00' OR b.date_end > NOW())) AND b2s.store_id = '".(int)$store_id."' AND b.status = '1'";
			
		$query = $this->db->query($sql);
				
		return $query->rows;
	}
}
?>