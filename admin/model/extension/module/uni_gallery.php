<?php class ModelExtensionModuleUniGallery extends Model {
	public function addGallery($data) {
		$this->db->query("INSERT INTO ".DB_PREFIX."uni_gallery SET name = '".$this->db->escape($data['name'])."', status = '".(int)$data['status']."'");

		$gallery_id = $this->db->getLastId();

		if (isset($data['gallery_image'])) {
			foreach ($data['gallery_image'] as $gallery_image) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_gallery_image SET gallery_id = '".(int)$gallery_id."', link = '". $this->db->escape($gallery_image['link'])."', image = '". $this->db->escape($gallery_image['image'])."'");

				$gallery_image_id = $this->db->getLastId();

				foreach ($gallery_image['gallery_image_description'] as $language_id => $gallery_image_description) {				
					$this->db->query("INSERT INTO ".DB_PREFIX."uni_gallery_image_description SET gallery_image_id = '".(int)$gallery_image_id."', language_id = '".(int)$language_id."', gallery_id = '".(int)$gallery_id."', title = '". $this->db->escape($gallery_image_description['title'])."'");
				}
			}
		}		
	}

	public function editGallery($gallery_id, $data) {
		$this->db->query("UPDATE ".DB_PREFIX."uni_gallery SET name = '".$this->db->escape($data['name'])."', status = '".(int)$data['status']."' WHERE gallery_id = '".(int)$gallery_id."'");

		$this->db->query("DELETE FROM ".DB_PREFIX."uni_gallery_image WHERE gallery_id = '".(int)$gallery_id."'");
		$this->db->query("DELETE FROM ".DB_PREFIX."uni_gallery_image_description WHERE gallery_id = '".(int)$gallery_id."'");

		if (isset($data['gallery_image'])) {
			foreach ($data['gallery_image'] as $gallery_image) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_gallery_image SET gallery_id = '".(int)$gallery_id."', link = '". $this->db->escape($gallery_image['link'])."', image = '". $this->db->escape($gallery_image['image'])."'");

				$gallery_image_id = $this->db->getLastId();

				foreach ($gallery_image['gallery_image_description'] as $language_id => $gallery_image_description) {				
					$this->db->query("INSERT INTO ".DB_PREFIX."uni_gallery_image_description SET gallery_image_id = '".(int)$gallery_image_id."', language_id = '".(int)$language_id."', gallery_id = '".(int)$gallery_id."', title = '". $this->db->escape($gallery_image_description['title'])."'");
				}
			}
		}			
	}

	public function deleteGallery($gallery_id) {
		$this->db->query("DELETE FROM ".DB_PREFIX."uni_gallery WHERE gallery_id = '".(int)$gallery_id."'");
		$this->db->query("DELETE FROM ".DB_PREFIX."uni_gallery_image WHERE gallery_id = '".(int)$gallery_id."'");
		$this->db->query("DELETE FROM ".DB_PREFIX."uni_gallery_image_description WHERE gallery_id = '".(int)$gallery_id."'");
	}

	public function getGallery($gallery_id) {			
		$query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX."uni_gallery` WHERE gallery_id = '".(int)$gallery_id."'");

		return $query->row;
	}

	public function getGallerys($data = array()) {
		
		$sql = "SELECT * FROM ".DB_PREFIX."uni_gallery";

		$sort_data = array(
			'name',
			'status'
		);	

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY ".$data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	

			$sql .= " LIMIT ".(int)$data['start'].",".(int)$data['limit'];
		}		

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getGalleryImages($gallery_id) {
		$gallery_image_data = array();

		$gallery_image_query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_gallery_image` WHERE gallery_id = '".(int)$gallery_id."'");

		foreach ($gallery_image_query->rows as $gallery_image) {
			$gallery_image_description_data = array();

			$gallery_image_description_query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_gallery_image_description` WHERE gallery_image_id = '".(int)$gallery_image['gallery_image_id']."' AND gallery_id = '".(int)$gallery_id."'");

			foreach ($gallery_image_description_query->rows as $gallery_image_description) {			
				$gallery_image_description_data[$gallery_image_description['language_id']] = ['title' => $gallery_image_description['title']];
			}

			$gallery_image_data[] = array(
				'image_description' => $gallery_image_description_data,
				'link'              => $gallery_image['link'],
				'image'             => $gallery_image['image']	
			);
		}

		return $gallery_image_data;
	}

	public function getTotalGallerys() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."uni_gallery");
		return $query->row['total'];
	}

	public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_gallery` (`gallery_id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(64) NOT NULL, `status` tinyint(1) NOT NULL, PRIMARY KEY (`gallery_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_gallery_image` (`gallery_image_id` int(11) NOT NULL AUTO_INCREMENT, `gallery_id` int(11) NOT NULL, `name` varchar(64) NOT NULL, `link` varchar(255) NOT NULL, `image` varchar(255) NOT NULL, PRIMARY KEY (`gallery_image_id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_gallery_image_description` (`gallery_image_id` int(11) NOT NULL, `language_id` int(11) NOT NULL, `gallery_id` int(11) NOT NULL, `title` varchar(64) NOT NULL, PRIMARY KEY (`gallery_image_id`,`language_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		
		$query = $this->db->query("SELECT query FROM `".DB_PREFIX."seo_url` WHERE `keyword` LIKE 'gallery' LIMIT 1");
		if ($query->num_rows == 0) {
			foreach ($languages as $language) {
				$this->db->query("INSERT INTO `".DB_PREFIX . "seo_url` SET store_id = 0, language_id = '".(int)$language['language_id']."', query = 'extension/module/uni_gallery', keyword = 'gallery'");
			}
		}
	}
}
?>