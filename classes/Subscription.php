<?php

class Subscription extends Mapper
{
    /**
     * Create new subscription
     */
    public function createSubscription($data) {

		$sql = 'INSERT INTO subscription
				(created_on,phone,retailer_id,location_id,product_number,confirmation_sid)
      			VALUES (NOW(),:phone,:retailer_id,:location_id,:product_number,:confirmation_sid)';
	
		$stmt = $this->db->prepare($sql);
    	$result = $stmt->execute([
	  	':phone' => $data['phone'],
	  	':retailer_id' => $data['retailer_id'],
	 	':location_id' => $data['location_id'],
	  	':product_number' => $data['product_number'],
	  	':confirmation_sid' => $data['confirmation_sid']
    	]);

		$id = $this->db->lastInsertId();
	
		$this->logger->addInfo("Subscription $id created for " . implode(',',$data));
	
		$response['code'] = 0;
		$response['message'] = "Subscription has been created successfully.";
		$response['subscription'] = $data;
		
		return $response;

	}
	
	public function countSubscriptions($data) {
		$sql = 'SELECT count(id)
				FROM subscription 
				WHERE phone = :phone AND retailer_id = :retailer_id 
				AND product_number = :product_number AND location_id = :location_id';
	
		$stmt = $this->db->prepare($sql);
    	$result = $stmt->execute([
	  	':phone' => $data['phone'],
	  	':retailer_id' => $data['retailer_id'],
	 	':location_id' => $data['location_id'],
	  	':product_number' => $data['product_number']
    	]);

		// Return number of matching rows
		return $stmt->fetchColumn();
	}


}
