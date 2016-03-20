# Virge::Stork

Virge::Stork is a collection of services/components on top of 
https://github.com/ratchetphp/Ratchet that provides a horizontally scalable
websocket architecture, and per topic subscription authentication for WAMP v1.

## Architecture
Virge::Stork provides a scalable architecture that allows you to scale your
websocket servers, and webservers independently, as well as support
for load balancing of websocket servers.

## Publish Server
Virge::Stork provides a ZMQ Publish server. This server subscribes to all 
available Websocket Servers, and will broadcast every push notification to 
all websocket servers. 

## Websocket Server
The websocket server subscribes to the ZMQ Publish server, and
also handles the Websocket connections of clients, and their topic subscriptions.

When receiving messages from the ZMQ Publish Server it determines if it has 
any connected clients on the chosen topic, and broadcasts to all subscribed.

## Topics
Topics are a URI that follows the format:
```
version.feedName.feedId
```

They are declared in Stork, and optionally attached to verifiers.
```
use Virge\Stork;
Stork::topic('v1', 'test')
    ->verify(function($session, $topic, $feedId, &$reason) {
        return true;
    })
;
```

The declared topics do not include the feedId, this is passed in by the client
upon connection. It will be passed to the verifier and can be used in determining
if the user can subscribe.

## Session
Virge::Stork reads the sessionId from the connecting client's cookies, and can
be setup to use your existing SessionHandler.
```
use Virge\Stork;
Stork::sessionHandler(\SessionHandlerInterface $myHandler);
```

## Authenticator
Virge::Stork provides the ability to have a custom authenticator callback
for initial websocket connection.
```
Stork::authenticator(function($conn) {
    return $conn->Session->getSecret() == '123';
});
```

## Running Examples

From within examples/simple you'll need to start 2 processes:
```
php -f publish_server.php
```
```
php -f server.php
```
```
php -S localhost:8000
```

Editing services.php will allow you to configure ports, and hostnames.

You should be able to visit http://localhost:8000 and it should attempt to 
connect to the websocket. Opening up the console will show debug information,
and any connection errors.

You can then send push messages using:
```
php -f push.php
```
