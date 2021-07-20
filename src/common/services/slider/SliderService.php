<?php

namespace common\services\slider;

use common\services\RedisNativeService;
use common\services\slider\common\SliderImagesService;
use common\services\slider\common\SliderKeyService;
use \Exception;

class SliderService
{
    // @var \Redis
    private $redisConnect;

    // @var SliderKeyService
    private $keyService;

    // @var SliderImagesService
    private $imgService;

    // @var SourceService
    private $sourceService;

    /**
     * SliderService constructor.
     *
     * @param   RedisNativeService   $redisService
     * @param   SliderKeyService     $keyService
     * @param   SliderImagesService  $imgService
     */
    public function __construct(
        RedisNativeService $redisService,
        SliderKeyService $keyService,
        SliderImagesService $imgService,
        SourceService $sourceService
    ) {
        $this->redisConnect  = $redisService->getConnect();
        $this->keyService    = $keyService;
        $this->imgService    = $imgService;
        $this->sourceService = $sourceService;
    }

    /**
     * @param   string  $id
     * @param   int     $imagesQty
     */
    public function create(string $id, int $imagesQty)
    {
        $this->imgService->set($id, $imagesQty);
    }

    /**
     * Проверим существует ли слайдер?
     * или он не существовал, или он удалился по таймауту
     * --------------------------------------------------
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function isExists(string $id): bool
    {
        return $this->redisConnect->exists($this->keyService->key($this->keyService::DATA, $id));
    }

    /**
     * Удаление всех ключей редиса для конкретного слайдера
     * ----------------------------------------------------
     *
     * @param   string  $id
     */
    private function deleteKey(string $id)
    {
        $keys = $this->redisConnect->keys("{$this->keyService::ANY}:{$id}");
        $this->redisConnect->del($keys);
    }

    public function getZipFile(string $id)
    {
        return __DIR__ . "/../web/zips/{$id}.zip";
    }

    /**
     * Удаляем полность все ключи в редисе и все связанные файлы
     * картинки и исходный pdf, и каталог где эти картинки лежали
     * ----------------------------------------------------------
     *
     * @param   string  $id
     * @param   bool    $source
     *
     * @throws \Exception
     */
    public function delete(string $id, bool $source = true)
    {
        /**
         * Удаляем картинки, zip архив и исходный pdf
         */
        $this->imgService->delete($id);

        $zipFile = $this->getZipFile($id);
        if (!unlink($zipFile)) {
            throw new Exception("cant unlink file [{$zipFile}]");
        }

        if ($source) {
            $sourceFile = $this->sourceService->get($id);
            if (!unlink($sourceFile)) {
                throw new Exception("cant unlink file [{$sourceFile}]");
            }
        }

        $this->deleteKey($id);
    }
}