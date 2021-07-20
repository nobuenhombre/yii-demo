<?php

namespace common\services\slider\common;

use common\services\RedisNativeService;

class SliderProgressService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    /**
     * SliderProgressService constructor.
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
     * Фнукция сохранения прогресса конвертации в ключики редиса
     *
     * @param   string  $id
     * @param   int     $imagesQty
     * @param   int     $pageNum
     */
    public function set(string $id, int $imagesQty, int $pageNum)
    {
        $progress = round(($pageNum + 1) * 100 / $imagesQty);

        $this->redisConnect->set($this->keyService->key($this->keyService::PROGRESS, $id), $progress);
    }

    /**
     * Получаем прогресс
     * -----------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function get(string $id): string
    {
        return $this->redisConnect->get($this->keyService->key($this->keyService::PROGRESS, $id));
    }
}