<?php 
require 'vendor/autoload.php';

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\EventRequestAsync;
use FacebookAds\Object\ServerSide\UserData;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;

$pixel_id = ''; //available on events manager
$access_token = ''; //available on events manager (pixel -> settings -> generate new access token)

if (empty($pixel_id) || empty($access_token)) {
  throw new Exception('Missing required test config. Got pixel_id: "' . $pixel_id . '", access_token: "' . $access_token . '"');
}

Api::init(null, null, $access_token, false);

function create_events($num) {
  $user_data = (new UserData())
    ->setEmail('') //lead email
    ->setClientUserAgent(''); //lead client agent

  $event = (new Event())
    ->setEventName('Event name') //event name (not pixel name) showed on events manager, available inside pixel tab
    ->setEventTime(time())
    ->setEventSourceUrl('https://your-event-source/') //url where the conversion happend
    ->setUserData($user_data);

  return array($event);
}

function create_async_request($pixel_id, $num) {
  $async_request = (new EventRequestAsync($pixel_id))
    ->setEvents(create_events($num));
  return $async_request->execute()
    ->then(
      null,
      function (RequestException $e) {
        print(
          "Error!!!\n" .
          $e->getMessage() . "\n" .
          $e->getRequest()->getMethod() . "\n"
        );
      }
    );
}

$promise = create_async_request($pixel_id, 3);

$response2 = $promise->wait();
print("Request 2: " . $response2->getBody() . "\n");
print("Async request with wait - OK.\n");

?>