<?php
class Retailer extends Mapper
{

    public function getRetailers() {
        $sql = 'SELECT id, name, api_url
				FROM retailer 
				WHERE active = 1';
		$stmt = $this->db->query($sql);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
		
    }

    /**
     * Get one retailer by its ID
     *
     * @param int $id The ID of the retailer
     * @return RetailerEntity  The retailer
     */
    public function getRetailerById($retailer_id) {
        $sql = 'SELECT *
           		FROM retailer
				WHERE id = :retailer_id AND active = 1';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':retailer_id' => $retailer_id]);

		$info = $stmt->fetch();

		//Add locations
		$sql = 'SELECT * 
				FROM location 
				WHERE retailer_id = :retailer_id';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([':retailer_id' => $retailer_id]);
		 
		$locations = $stmt->fetchAll();

		$info['locations'] = $locations;

		return new RetailerEntity($info);
    }
}
