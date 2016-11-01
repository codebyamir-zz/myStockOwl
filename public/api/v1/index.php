<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../../../vendor/autoload.php';

spl_autoload_register(function ($classname) {
    require ("../../../classes/" . $classname . ".php");
});

$config['displayErrorDetails'] = true;
$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = 'DMCYCmlFXD';
$config['db']['dbname'] = 'stockowl';

$app = new \Slim\App(["settings" => $config]);

# Dependency Injection
$container = $app->getContainer();

$container['twilio'] = function($c) {
	$account_sid = 'ACaa040cd2180a7a0fff9a362533150f78';
	$auth_token = '459f48c42e54851521afb62cb822044b';

	$client = new Services_Twilio($account_sid, $auth_token);

    return $client;
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('stockowl_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../../../logs/app-web.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['Retailer'] = function ($c) {
    return new Retailer($c['db'],$c['logger']);
};

$container['Subscription'] = function ($c) {
    return new Subscription($c['db'],$c['logger']);
};


# Routes

$app->get('/retailers', function (Request $request, Response $response) {
	$data = $this->Retailer->getRetailers();
	
	$response = $response->withJson($data);
	return $response;
});

$app->get('/retailers/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
	$data = $this->Retailer->getRetailerById($id);
	
	$response = $response->withJson($data);
	return $response;
});

$app->post('/subscriptions', function (Request $request, Response $response) {
	$body = $request->getParsedBody();
	
	// Sanitize inputs
	$subscribe_data = [];
	$subscribe_data['retailer_id'] = filter_var($body['retailer_id'], FILTER_SANITIZE_STRING);
	$subscribe_data['location_id'] = filter_var($body['location_id'], FILTER_SANITIZE_STRING);
	$subscribe_data['product_number'] = filter_var($body['product_number'], FILTER_SANITIZE_STRING);
	$subscribe_data['phone'] = filter_var($body['phone'], FILTER_SANITIZE_STRING);

	// Remove all non-numeric characters from phone
	$subscribe_data['phone'] = preg_replace('/[^0-9]/', '', $subscribe_data['phone']);
	
	// E164 format includes plus sign
	if (strlen($subscribe_data['phone']) == 10)
	{
		$subscribe_data['phone'] = '+1' . $subscribe_data['phone'];
	}

	if (strlen($subscribe_data['phone']) == 11)
	{
		$subscribe_data['phone'] = '+' . $subscribe_data['phone'];
	}
		
	// Length should be 12 for E164 US format (+15550001234)
	$phone_valid = (strlen($subscribe_data['phone']) === 12);

	if (! $phone_valid) 
	{
		$result['code'] = 1;
		$result['message'] = 'Phone number must be US-based and include country code and area code' . $subscribe_data['phone'];

		$this->logger->addInfo("Invalid subscription attempt for " . implode(',',$subscribe_data));

		$response = $response->withJson($result);
		return $response;
	}

    // Check for duplicate subscription 	
	$exists = ($this->Subscription->countSubscriptions($subscribe_data) > 0);

	if ($exists)
	{
		$this->logger->addInfo("Duplicate subscription attempt for " . implode(',',$subscribe_data));
		
		$result['code'] = 1;
		$result['message'] = "Subscription already exists";

		$response = $response->withJson($result);
		return $response;
	}

	try
	{
		$sms = $this->twilio->account->messages->create(array(
                "From" => '412-201-0710',
                "To" => $subscribe_data['phone'],
                "Body" => "Thank you for choosing myStockOwl!  We'll notify you once your product is in stock."
        ));
	}
	catch (Services_Twilio_RestException $e)
	{
		echo $e->getMessage();
	}
	
	$subscribe_data['confirmation_sid'] = $sms->sid;

	$result = $this->Subscription->createSubscription($subscribe_data);
	$response = $response->withJson($result);
	return $response;
});


$app->run();
