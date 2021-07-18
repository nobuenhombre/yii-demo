<?php

namespace common\components;

use yii\base\BaseObject;
use Exception;
use Yii;

/**
 * Class RedisNative
 *
 * @package commmon\components
 */
class RedisNative extends BaseObject
{
    public const DEFAULT_HOST = 'localhost';
    public const DEFAULT_PORT = 6379;
    public const DEFAULT_DB_INDEX = 0;

    // @var string
    public $host;

    // @var integer
    public $port;

    // @var integer
    public $dbIndex;

    // @var \Redis
    private $connect;

    /**
     * @return \Redis
     */
    public function getConnect():\Redis {
        return $this->connect;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        if ($this->host === null) {
            $this->host = static::DEFAULT_HOST;
        }

        if ($this->port === null) {
            $this->port = static::DEFAULT_PORT;
        }

        if ($this->dbIndex === null) {
            $this->port = static::DEFAULT_DB_INDEX;
        }

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
}