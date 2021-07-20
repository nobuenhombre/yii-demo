<?php

namespace common\services\slider\common;

use common\services\RedisNativeService;

class SliderTimerService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    // @var int
    private $timeout;

    /**
     * SliderTimerService constructor.
     *
     * @param   RedisNativeService  $redisService
     * @param   SliderKeyService    $keyService
     * @param   int                 $timeout
     */
    public function __construct(
        RedisNativeService $redisService,
        SliderKeyService $keyService,
        int $timeout
    ) {
        $this->redisConnect = $redisService->getConnect();
        $this->keyService   = $keyService;
        $this->timeout      = $timeout;
    }

    /**
     * Создаем Временный ключ с таймаутом для последующего удаления
     * файлов слайдера через консольный контроллер
     * -------------------------------------------
     *
     * @param   string  $id
     * @param   int     $imagesQty
     */
    public function set(string $id, int $imagesQty)
    {
        $this->redisConnect->set($this->keyService->key($this->keyService::TIME, $id), $imagesQty, $this->timeout);
    }

    /**
     * Получение списка ключиков в редисе по маске
     * -------------------------------------------
     *
     * @param   string  $mask
     *
     * @return array
     */
    private function getListIDs(string $mask)
    {
        $keys     = $this->redisConnect->keys($mask . "*");

        $list_ids = [];
        foreach ($keys as $k) {
            $list_ids[$k] = substr($k, strlen($mask));
        }

        return $list_ids;
    }

    /**
     * Вычисление списка id слайдеров - претендентов на удаление
     * Мы знаем набор постоянных ключей
     * Мы знаем оставшие ключики с таймаутом
     * Их дельта и есть претенденты на удаление
     * ----------------------------------------
     *
     * @return array
     */
    public function getListIDsToRemove()
    {
        $list_ids_data  = $this->getListIDs($this->keyService::DATA . ":");
        $list_ids_time  = $this->getListIDs($this->keyService::TIME . ":");

        $keys_to_delete = array_diff($list_ids_data, $list_ids_time);

        return $keys_to_delete;
    }
}