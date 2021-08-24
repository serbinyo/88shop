<?php
class ModelExtensionModuleUniGallery extends Model {	
	public function getGallerys() {
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_gallery gi WHERE gi.status = 1");
		
		return $query->rows;
	}
	
	public function getGallery($gallery_id) {
		//$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_gallery_image gi LEFT JOIN ".DB_PREFIX."uni_gallery_image_description gid ON (gi.gallery_image_id  = gid.gallery_image_id) AND gid.language_id = '".(int)$this->config->get('config_language_id')."'");
		
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_gallery_image gi LEFT JOIN ".DB_PREFIX."uni_gallery_image_description gid ON (gi.gallery_image_id  = gid.gallery_image_id) WHERE gi.gallery_id = '".(int)$gallery_id."' AND gid.language_id = '".(int)$this->config->get('config_language_id')."' ORDER BY LCASE(gid.title)");
		
		return $query->rows;
	}
}
?>