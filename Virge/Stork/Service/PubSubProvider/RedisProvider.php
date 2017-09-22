<?php
namespace Virge\Stork\Service\PubSubProvider;

use Redis;
use Thruway\ClientSession;
use Virge\Core\Config;
use Virge\Stork\Component\PubSubMessage;

class RedisProvider implements \Virge\Stork\Service\PubSubProviderInterface
{
    protected $redis;

    protected $callback;

    public function __construct()
    {
        $this->redis = null;
    }

    public function onSessionStart(ClientSession $session, $loop, callable $callback)
    {
        $this->callback = $callback;

        $loop->addPeriodicTimer(1, function() use($session) {
            try {
                $client = $this->getClient();

                if(!$client->isConnected()) {
                    $this->redis = null;
                    $client = $this->getClient();
                }

                //pop oldest message first
                while(false !== ($rawMessage = $client->lPop('virge:stork'))) {
                    $this->onRedisMessage($rawMessage);
                }

            } catch(\Exception $ex) {
                if($ex->getMessage() === "read error on connection") {
                    $this->redis = null; //force reconnect
                } else {
                    throw $ex;
                }
            }
        });
    }

    public function onSessionEnd()
    {
        
    }

    public function getClient() : Redis
    {
        if($this->redis) {
            return $this->redis;
        }

        $redis = new Redis();
        $success = $redis->pconnect(Config::get('app', 'redis_host'), Config::get('app', 'redis_port'));
        $redis->setOption(Redis::OPT_READ_TIMEOUT, 6000);
        if(!$success) {
            throw new \RuntimeException("Failed to connect to Redis");
        }

        $redis->select(1);

        return $this->redis = $redis;
    }

    public function getPushClient() : Redis
    {
        if($this->pushClient && $this->pushClient->isConnected()) {
            return $this->pushClient;
        }

        $redis = new Redis();
        $success = $redis->connect(Config::get('app', 'redis_host'), Config::get('app', 'redis_port'));
        $redis->setOption(Redis::OPT_READ_TIMEOUT, 100);
        if(!$success) {
            throw new \RuntimeException("Failed to connect to Redis");
        }

        $redis->select(1);

        return $this->pushClient = $redis;
    }

    public function push(PubSubMessage $message)
    {
        return $this->getClient()->publish("virge:stork", serialize($message));
    }

    public function startPublishingServer()
    {
        //don't need to start a pub server
        try {
            $this->getClient()->subscribe(['virge:stork'], [$this, 'publishToQueue']);
        } catch(\Exception $ex) {
            if($ex->getMessage() === "read error on connection") {
                $this->redis = null;
                $this->startPublishingServer();
            } else {
                throw $ex; //re-throw any other exception
            }
        }
    }

    public function publishToQueue($redis, $channel, $message)
    {
        if(!$this->getPushClient()->rPush('virge:stork', $message)){
            LogService::error($this->getPushClient()->getLastError());
        }
    }

    public function onRedisMessage($rawMessage)
    {
        call_user_func_array($this->callback, [$rawMessage]);
    }

    public function onReceiveMessage($message)
    {
        
    }
}