<?php

namespace App\Services;

use Predis\Client;

class RedisService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => '**',
            'port'   => '**',
        ]);
    }

    public function set($key, $value, $ttl = null)
    {
        $this->client->set($key, $value);
        if ($ttl) {
            $this->client->expire($key, $ttl);
        }
    }

    public function get($key)
    {
        return $this->client->get($key);
    }

    public function delete($key)
    {
        return $this->client->del($key);
    }

    public function exists($key)
    {
        return $this->client->exists($key);
    }
}