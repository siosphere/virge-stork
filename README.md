# Virge::Stork

Virge::Stork is a collection of services/components that provide a nice wrapper
around Thruway (https://github.com/voryx/Thruway) that provides a horizontally 
scalable websocket architecture, and per topic subscription authentication 
for WAMP v2.

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
also handles the authentication of clients, and their topic subscriptions.

When receiving messages from the ZMQ Publish Server it broadcasts to all subscribed.

## Crossbar.io or other WAMP Router
Usage requires using crossbar.io or another compliant WAMP Router. Stork
depends on a role that allows it to register two Procedures

io.virge.stork.auth
Used to provide basic ticket authentication to clients

io.virge.stork.topic_auth
Used to determine if the client can subscribe to a given topic

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

## Authenticator
Virge::Stork provides the ability to have a custom authenticator callback
for initial websocket connection. This authenticator should set the 
authId to a valid string (usually the UserId of the user connection)

This authId will be used in the topic authentication
```
Stork::authenticator(function($session, &$returnData) {
    
    if($session->ticket === 'testtest') {
        $returnData['authid'] = "1";
        return true;
    }

    return false;
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

You will also need to start a crossbar.io server, an example configuration
is provided within examples/simple:

```
docker run --name crossbar -p 8000:80 -e "CROSSBAR_BACKEND_SECRET=testtest" -v /abs/path/to/examples/simple/.crossbar:/app/.crossbar --entrypoint "crossbar start --cbdir /app/.crossbar" crossbario/crossbar
```

We can also start our webserver within that directory:

```
php -S localhost:9000
```

Editing services.php will allow you to configure ports, and hostnames.

You should be able to visit http://localhost:9000 and it should attempt to 
connect to the websocket. Opening up the console will show debug information,
and any connection errors.

You can then send push messages using:
```
php -f push.php
```
