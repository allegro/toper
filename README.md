Toper
=====

Toper is a PHP Rest client based on popular Guzzle Rest Client. It base responsibility is to perform tasks connected with a load balancing and a fault tolerance.

Many modern web applications to keep up with a huge traffic needs to connect to not one backend machine, but often to several identical machines. Toper allows you to implement this very fast and simply. The main goal of Toper is to watch service instances and if any of them failed Toper will switch to other one.

Features
--------

* Round robin
* Fault tolerance

Installing via Composer 
-----------------------

The recomended way to intall toper is using Composer
```sh
#install composer
curl -sS https://getcomposer.org/installer | php
```
Edit your composer.json file and add Toper to require section:
```json
{
    "require": {
        "allegro/toper": "dev-master"
    }
}
```

Quick start
-----------
Here is example how create a request by Toper. In this case Toper is configured to use multiple destinations to protect against a breakdown if any of them fail.
```php

use Toper\GuzzleClientFactory;
use Toper\StaticHostPoolProvider;

$hostPollProvider = new StaticHostPoolProvider(
    array("http://service1.com", "http://service2.com")
); 
$guzzleClientFactory = new GuzzleClientFactory();

$toper = new \Toper\Client($hostPollProvider, $guzzleClientFactory);
$response = $toper->get('/')
    ->send();

if($response->getStatusCode() == 200) {
    echo $response->getBody();
}
```


