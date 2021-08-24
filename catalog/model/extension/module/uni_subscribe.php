<?php
class ModelExtensionModuleUniSubscribe extends Model {	
	public function editSubscribe($customer_id, $newsletter) {
		$this->db->query("UPDATE `".DB_PREFIX."customer` SET newsletter = '".(int)$newsletter."' WHERE customer_id = '".(int)$customer_id."'");
	}
	
	public function getRewards($customer_id, $description, $points) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."customer_reward` WHERE customer_id = '".(int)$customer_id."' AND description = '".$this->db->escape($description)."' AND points = '".(int)$points."'");
		
		return $query->row;
	}
	
	public function addReward($customer_id, $description, $points, $order_id = 0) {
		$this->db->query("INSERT INTO `".DB_PREFIX."customer_reward` SET customer_id = '".(int)$customer_id."', order_id = '".(int)$order_id."', points = '".(int)$points."', description = '".$this->db->escape($description)."', date_added = NOW()");
	}
	
	public function getAttempts($ip) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."customer_login` WHERE ip = '".$this->db->escape($ip)."'");

		return $query->row;
	}
	
	public function addAttempt($email, $ip) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."customer_login` WHERE ip = '".$this->db->escape($ip)."'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `".DB_PREFIX."customer_login` SET email = '".$this->db->escape(utf8_strtolower((string)$email))."', ip = '".$this->db->escape($ip)."', total = 1, date_added = '".$this->db->escape(date('Y-m-d H:i:s'))."', date_modified = '".$this->db->escape(date('Y-m-d H:i:s'))."'");
		} else {
			$this->db->query("UPDATE `".DB_PREFIX."customer_login` SET total = (total + 1), date_modified = '".$this->db->escape(date('Y-m-d H:i:s'))."' WHERE customer_login_id = '".(int)$query->row['customer_login_id']."'");
		}
	}
}
?>