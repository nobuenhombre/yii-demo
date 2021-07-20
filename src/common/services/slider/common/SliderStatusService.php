<?php

namespace common\services\slider\common;

use common\services\RedisNativeService;

class SliderStatusService
{
    /**
     * Статусы конвертации PDF
     * в процессе, успешно, ошибка
     */
    const
        STATUS_IN_PROGRESS = 'in_progress',
        STATUS_SUCCESS = 'success',
        STATUS_ERROR = 'error';

    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    /**
     * SliderStatusService constructor.
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
     * Получим Статус из редиса
     * ------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function get(string $id): string
    {
        return $this->redisConnect->get($this->keyService->key($this->keyService::STATUS, $id));
    }

    /**
     * Сохраним Статус в редис
     * -----------------------
     *
     * @param   string  $id
     * @param   string  $status
     */
    public function set(string $id, string $status)
    {
        $this->redisConnect->set($this->keyService->key($this->keyService::STATUS, $id), $status);
    }
}