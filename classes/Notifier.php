<?php

// Load Twilio lib
require '/var/www/html/stockowl/vendor/autoload.php';

// Used to manage  subscriptions and send texts
class Notifier
{
	protected $db;
	protected $logger;

	public function __construct($db,$logger) 
	{
		$this->db = $db;
		$this->logger = $logger;
    }
	
	// Expire subscriptions from database
	public function expireSubscriptions()
	{
		$sql = 'UPDATE subscription 
				SET expired = 1 
				WHERE expired = 0 AND fulfilled IS NULL AND created_on + INTERVAL 30 DAY < NOW()';

		$stmt = $this->db->prepare($sql);
        $result = $stmt->execute([]);
		$this->logger->addInfo("Expiring subscriptions older than 30 days");
	}

	// List non-expired subscriptions
	public function listSubscriptions()
	{
		$sql = 'SELECT subscription.id,subscription.phone,retailer.name,api_url,product_number,store_code 
				FROM subscription
				JOIN retailer on retailer.id = subscription.retailer_id 
				JOIN location on location.id = subscription.location_id
				WHERE expired = 0 AND fulfilled IS NULL;';
	
		$stmt = $this->db->prepare($sql);
        $result = $stmt->execute([]);
		
		return $stmt->fetchAll();
	}

	// Update last_checked timestamp on subscription	
	public function updateSubscription($id)
	{
		$sql = 'UPDATE subscription 
				SET last_checked = NOW()
				WHERE id = :id';

		$stmt = $this->db->prepare($sql);
		$result = $stmt->execute([':id' => $id]);
		$this->logger->addInfo("Updated subscription $id");
	}

	// Close out the subscription
	public function closeSubscription($s)
	{
		$sid = $this->sendSMS($s);

		$sql = 'UPDATE subscription 
				SET fulfilled = NOW(), fulfilled_sid = :sid
				WHERE id = :id';

		$stmt = $this->db->prepare($sql);
		$stmt->execute([
                ':id' => $s['id'],
                ':sid' => $sid
        ]);
	
		$this->logger->addInfo("Closed subscription id " . $s['id']);
	}

	// Send text message
	private function sendSMS($s)
	{
		try
        {
			$account_sid = '';
			$auth_token = '';
			
			$client = new Services_Twilio($account_sid, $auth_token);
			$body = "Your product from " . $s['name'] . " is in stock!\nWe won't send you any further alerts about this product. Thanks for using myStockOwl!";
			$sms = $client->account->messages->create(array("From" => '412-555-0000',
                "To" => $s['phone'],
                "Body" => $body
        ));
        }
        catch (Services_Twilio_RestException $e)
        {
                echo $e->getMessage();
        }

		$this->logger->addInfo("Sent notify sms for subscription " . implode(',', $s));
		return $sms->sid;
	}
}


