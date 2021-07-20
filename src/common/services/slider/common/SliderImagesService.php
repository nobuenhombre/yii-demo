<?php

namespace common\services\slider\common;

use common\services\RedisNativeService;
use yii\helpers\Url;
use \Exception;

class SliderImagesService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    /**
     * SliderImagesService constructor.
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
     * Создаем основной ключ
     * -------------------------------------------
     *
     * @param   string  $id
     * @param   int     $imagesQty
     */
    public function set(string $id, int $imagesQty)
    {
        $this->redisConnect->set($this->keyService->key($this->keyService::DATA, $id), $imagesQty);
    }

    /**
     * Получим количество картинок в слайдере
     * --------------------------------------
     *
     * @param   string  $id
     *
     * @return bool|string
     */
    public function getCount(string $id)
    {
        return $this->redisConnect->get($this->keyService->key($this->keyService::DATA, $id));
    }


    /**
     * Для каждого слайдера будем создавать отдельные каталоги
     * что-бы не валить все картинки в одну кучу
     * Да и удалять их потом будет приятнее.
     * -------------------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function getDirectory(string $id):string
    {
        return __DIR__ . "/../web/images/{$id}/";
    }

    /**
     * @param   string  $id
     *
     * @return string
     * @throws \Exception
     */
    public function createDirectory(string $id):string
    {
        $dir = $this->getDirectory($id);

        if (!mkdir($dir, 0777, true)) {
            throw new Exception("cant mkdir [{$dir}]");
        }

        return $dir;
    }

    /**
     * Получим список картинок для REST API контроллера
     * ------------------------------------------------
     *
     * @param   string  $id
     *
     * @return array
     */
    public function get(string $id): array
    {
        $count  = $this->getCount($id);

        $list = [];
        for ($i = 0; $i < $count; $i++) {
            $list[] = Url::to("@web/images/{$id}/page-{$i}.jpg", true);
        }

        return $list;
    }

    /**
     * @param   string  $id
     *
     * @throws \Exception
     */
    public function delete(string $id)
    {
        $qty = $this->getCount($id);
        $dir = $this->getDirectory($id);

        for ($i = 0; $i < $qty; $i++) {
            $file = "{$dir}page-{$i}.jpg";
            if (!unlink($file)) {
                throw new Exception("cant unlink file [{$file}]");
            }
        }

        if (!rmdir("{$dir}")) {
            throw new Exception("cant rmdir [{$dir}]");
        }
    }
}