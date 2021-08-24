<?php
class ModelExtensionModuleUniCategoryWall extends Model {	
	public function getCategories($categories) {	
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$category_data = [];
		$result = [];
		
		if($categories) {	
			foreach($categories as $category_id => $category_arr) {
			
				if (array_key_exists('p', $category_arr)) {
					$query = $this->db->query("SELECT c.category_id, cd.name, c.image FROM `".DB_PREFIX."category` c LEFT JOIN `".DB_PREFIX."category_description` cd ON (c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '".(int)$category_arr['p']."' AND cd.language_id = '".$lang_id."' AND c2s.store_id = '".$store_id."' AND c.status = '1'");
	
					$category_data = $query->row;
				}
	
				$children_data = [];
				
				if($category_arr && is_array($category_arr)) {
					
					$categories = implode(',', array_key_exists('p', $category_arr) ? array_slice($category_arr, 1) : $category_arr);
					
					if($categories) {
						$query = $this->db->query("SELECT c.category_id, cd.name, c.image FROM `".DB_PREFIX."category` c LEFT JOIN `".DB_PREFIX."category_description` cd ON (c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX ."category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id in (".$this->db->escape($categories).") AND cd.language_id = '".$lang_id."' AND c2s.store_id = '".$store_id."' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");
							
						$children_data = $query->rows;
					}
				}
					
				if ($category_data) {
					$result[] = [
						'category_id' => $category_data['category_id'],
						'name'		  => $category_data['name'],
						'image' 	  => $category_data['image'],
						'children'    => $children_data
					];
				} else {
					foreach($children_data as $children) {
						$result[] = [
							'category_id' => $children['category_id'],
							'name'		  => $children['name'],
							'image' 	  => $children['image'],
							'children'    => []
						];
					}
				}
			}
		}
		
		return $result;
	}
}
?>