<?php

// Used to query store inventory API

class Inventory
{
	protected $url;
	protected $logger;

	public function __construct($url,$logger)
	{
		$this->url = $url;
		$this->logger = $logger;
	}

	public function countStockIKEA($store_code,$product_number)
	{
		// Strip periods from product number
		$product_number = preg_replace('/[.]/', '', $product_number);
		
		$url = $this->url . '/' . $product_number;

		$agent= 'mystockowl.com (support@mystockowl.com)';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_URL,$url);

		$result=curl_exec($ch);
		curl_close($ch);

		$valid = simplexml_load_string($result);

		if (!$valid)
		{
			$this->logger->addInfo("IKEA stock check exception for $url");
        	return -1;
		}

		$xml = new SimpleXMLElement($result);

		foreach ($xml->availability[0]->localStore as $store)
		{
			if ($store['buCode'] == $store_code)
			{
				$stock = $store->stock->availableStock;
				$this->logger->addInfo("IKEA stock check success for $url");
				return $stock;
			}
		}
	}
}
