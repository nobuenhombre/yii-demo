<?php

namespace common\services\slider;

use common\services\RedisNativeService;
use common\services\slider\common\SliderKeyService;

class SourceService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    /**
     * SliderService constructor.
     *
     * @param   RedisNativeService  $redisService
     * @param   SliderKeyService    $keyService
     */
    public function __construct(
        RedisNativeService $redisService,
        SliderKeyService $keyService
    ) {
        $this->redisConnect = $redisService->getConnect();
        $this->keyService   = $keyService;
    }

    /**
     * Получим имя исходного файла из редиса
     * -------------------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function get(string $id): string
    {
        return $this->redisConnect->get($this->keyService->key($this->keyService::SOURCE, $id));
    }

    /**
     * Сохраним имя исходного файла в редис
     * ------------------------------------
     *
     * @param   string  $id
     * @param   string  $fileName
     */
    public function set(string $id, string $fileName)
    {
        $this->redisConnect->set($this->keyService->key($this->keyService::SOURCE, $id), $fileName);
    }
}