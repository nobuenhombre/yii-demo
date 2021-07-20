<?php

namespace common\services;

use Exception;
use Yii;

/**
 * Class RedisNative
 *
 * @package commmon\services
 */
class RedisNative
{
    // @var string
    private $host;

    // @var integer
    private $port;

    // @var int
    private $dbIndex;

    // @var \Redis
    private $connect;

    /**
     * RedisNative constructor.
     *
     * @param   string  $host
     * @param   int     $port
     * @param   int     $dbIndex
     *
     * @throws \Exception
     */
    public function __construct(
        string $host,
        int $port,
        int $dbIndex
    ) {
        $this->connect = null;

        if (!extension_loaded('redis')) {
            throw new Exception(Yii::t('app', 'redis extension not loaded'));
        }

        $this->connect = new \Redis();

        $okConnect = $this->connect->connect($this->host, $this->port);
        if (!$okConnect) {
            throw new Exception(
                Yii::t(
                    'app',
                    'cant connect to redis server by {host}:{port}',
                    [
                        'host'=>$this->host,
                        'port'=>$this->port
                    ]
                )
            );
        }

        $okSelect = $this->connect->select($this->dbIndex);
        if (!$okSelect) {
            throw new Exception(
                Yii::t(
                    'app',
                    'cant select redis db by {dbIndex}',
                    [
                        'dbIndex'=>$this->dbIndex,
                    ]
                )
            );
        }
    }

    /**
     * @return \Redis
     */
    public function getConnect():\Redis {
        return $this->connect;
    }
}