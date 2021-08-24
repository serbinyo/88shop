<?php
class ModelExtensionModuleUniDownload extends Model {	
	public function getDownloads($product_id, $start, $limit) {
		$uniset = $this->config->get('config_unishop2');
		$status = $uniset['product']['download_tab']['status'];
		
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 50;
		}
		
		$result = [];
		
		if($status) {
			
			$implode = [];

			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '".(int)$order_status_id."'";
			}
			
			if(!$implode && $status > 2) {
				return $result;
			}
			
			$sql = "SELECT d.download_id, d.date_added, dd.name, d.filename, d.mask FROM";
			
			if($status > 2) {
				$sql .= " `".DB_PREFIX."order` o LEFT JOIN `".DB_PREFIX."order_product` op ON (o.order_id = op.order_id) LEFT JOIN `".DB_PREFIX."product_to_download` p2d ON (op.product_id = p2d.product_id)";
			} else {
				$sql .= " `".DB_PREFIX."product_to_download` p2d";
			}
			
			$sql .= " LEFT JOIN `".DB_PREFIX."download` d ON (p2d.download_id = d.download_id) LEFT JOIN `".DB_PREFIX."download_description` dd ON (d.download_id = dd.download_id) WHERE";
			
			if($status > 2) {
				$sql .= " o.customer_id = '".(int)$this->customer->getId()."' AND (".implode(" OR ", $implode).") AND";
			}
			
			$sql .= " p2d.product_id = '".(int)$product_id."' AND dd.language_id = '".(int)$this->config->get('config_language_id') . "'ORDER BY d.date_added DESC LIMIT ".(int)$start.", ".(int)$limit;
			
			$query = $this->db->query($sql);
			
			if($query->rows) {
				$result = $query->rows;
			}
		}
		
		return $result;
	}
	
	public function getDownload($product_id, $download_id) {
		$uniset = $this->config->get('config_unishop2');
		$status = $uniset['product']['download_tab']['status'];
		
		$result = [];
		
		if($status) {

			$implode = [];

			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '".(int)$order_status_id."'";
			}
			
			if(!$implode && $status > 2) {
				return $result;
			}
		
			$sql = "SELECT d.filename, d.mask FROM";
		
			if($status > 2) {
				$sql .= " `".DB_PREFIX."order` o LEFT JOIN `".DB_PREFIX."order_product` op ON (o.order_id = op.order_id) LEFT JOIN `".DB_PREFIX."product_to_download` p2d ON (op.product_id = p2d.product_id)";
			} else {
				$sql .= " `".DB_PREFIX."product_to_download` p2d";
			}
		
			$sql .= " LEFT JOIN `".DB_PREFIX."download` d ON (p2d.download_id = d.download_id) WHERE";
		
			if($status > 2) {
				$sql .= " o.customer_id = '".(int)$this->customer->getId()."' AND (".implode(" OR ", $implode).") AND";
			}
			
			$sql .= " p2d.product_id = '".(int)$product_id."' AND d.download_id = '".(int)$download_id."'";
		
			$query = $this->db->query($sql);
			
			if($query->row) {
				$result = $query->row;
			}
		}
		
		return $result;
	}
}
?>