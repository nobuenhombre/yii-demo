<?php

namespace common\services\slider\common;

use common\services\RedisNativeService;

class SliderMessagesService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    /**
     * SliderMessagesService constructor.
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
     * Добавляем сообщения - лог исполнения в редис
     * Эти сообщения будут отражаться на странице в процессе конвертации
     * -----------------------------------------------------------------
     *
     * @param   string  $id
     * @param   string  $message
     */
    public function add(string $id, string $message)
    {
        $this->redisConnect->append($this->keyService->key($this->keyService::MESSAGE, $id), $message);
    }

    /**
     * Получение сообщений и очистка
     * Чтобы после получения сообщения через ajax скрипт
     * ключик содержащий их очистился
     * ------------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function pop(string $id): string
    {
        return $this->redisConnect->getSet($this->keyService->key($this->keyService::MESSAGE, $id), '');
    }
}