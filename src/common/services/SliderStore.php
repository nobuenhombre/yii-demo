<?php

namespace common\services;

use yii\helpers\Url;

/**
 * Class SliderStore
 *
 * Класс предназначен для управления ключами редиса
 * для хранения информации по процессу конвертации PDF
 * а также для хранения информации о готовых слайдерах
 *
 * @package common\services
 */
class SliderStore
{
    // @var \Redis
    private $redisConnect;

    /**
     * SliderStore constructor.
     *
     * @param   \common\services\RedisNative  $redisService
     */
    public function __construct(
        RedisNative $redisService
    ) {
        $this->redisConnect = $redisService->getConnect();
    }

    /**
     * Маски ключей редиса
     * Для разного рода данных о слайдерах
     */
    const
        DATA = 'pdf.data', // В этой маске будем хранить число картинок для слайдера - Бессрочно
        TIME = 'pdf.time', // В этой маске будем хранить число картинок для слайдера - 30 минут для таймера удаления
        PROGRESS = 'pdf.progress', // В этой маске будем хранить прогресс конвертации PDF в набор картинок
        STATUS = 'pdf.status', // В Этой маске будем хранить статус конвертации
        SOURCE = 'pdf.source', // В Этой маске сохраним оригинальное имя файла
        MESSAGE = 'pdf.message'; // В этой маске буду хранить список сообщений о ходе процесса конверсии

    /**
     * Таймаут для автоматического удаления ключей редиса
     * Раз в минуту по крону я буду звать консольный контроллер
     * php yii timer/delete
     * там вычисляются удаленные по таймауту ключи из редиса
     * и удаляются связанные с ними файлы слайдеров на диске.
     */
    const
        TIMEOUT = 1800;//1800 = 30 минут * 60 секунд

    /**
     * Статусы конвертации PDF
     * в процессе, успешно, ошибка
     */
    const
        STATUS_IN_PROGRESS = 'in_progress',
        STATUS_SUCCESS = 'success',
        STATUS_ERROR = 'error';

    /**
     * Формируем ключи в редисе для хранения информации о слайдере
     * -----------------------------------------------------------
     *
     * @param   string  $type
     * @param   string  $id
     *
     * @return string
     */
    public function key(string $type, string $id): string
    {
        return "{$type}:{$id}";
    }

    /**
     * Создаем основной ключ
     * -------------------------------------------
     *
     * @param   string  $id
     * @param   int     $imagesQty
     */
    public function create(string $id, int $imagesQty)
    {
        $this->redisConnect->set($this->key(static::DATA, $id), $imagesQty);
    }

    /**
     * Создаем Временный ключ с таймаутом для последующего удаления
     * файлов слайдера через консольный контроллер
     * -------------------------------------------
     *
     * @param   string  $id
     * @param   int     $imagesQty
     */
    public function setTimer(string $id, int $imagesQty)
    {
        $this->redisConnect->set(
            $this->key(static::TIME, $id),
            $imagesQty,
            static::TIMEOUT
        );
    }

    /**
     * Фнукция сохранения прогресса конвертации в ключики редиса
     * ---------------------------------------------------------
     *
     * @param   string  $id
     * @param   int     $imagesQty
     * @param   int     $pageNum
     *
     * @return int
     */
    public function setProgress(string $id, int $imagesQty, int $pageNum): int
    {
        $progress = round(($pageNum + 1) * 100 / $imagesQty);

        $this->redisConnect->set($this->key(static::PROGRESS, $id), $progress);

        return $progress;
    }

    /**
     * Получаем прогресс
     * -----------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function getProgress(string $id): string
    {
        return $this->redisConnect->get($this->key(static::PROGRESS, $id));
    }

    /**
     * Получим имя исходного файла из редиса
     * -------------------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function getSource(string $id): string
    {
        return $this->redisConnect->get($this->key(static::SOURCE, $id));
    }

    /**
     * Сохраним имя исходного файла в редис
     * ------------------------------------
     *
     * @param   string  $id
     * @param   string  $fileName
     */
    public function setSource(string $id, string $fileName)
    {
        $this->redisConnect->set($this->key(static::SOURCE, $id), $fileName);
    }

    /**
     * Добавляем сообщения - лог исполнения в редис
     * Эти сообщения будут отражаться на странице в процессе конвертации
     * -----------------------------------------------------------------
     *
     * @param   string  $id
     * @param   string  $message
     */
    public function addMessage(string $id, string $message)
    {
        $this->redisConnect->append($this->key(static::MESSAGE, $id), $message . '<br />');
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
    public function popMessage(string $id): string
    {
        return $this->redisConnect->getSet($this->key(static::MESSAGE, $id), '');
    }

    /**
     * Получим Статус из редиса
     * ------------------------
     *
     * @param   string  $id
     *
     * @return string
     */
    public function getStatus(string $id): string
    {
        return $this->redisConnect->get($this->key(static::STATUS, $id));
    }

    /**
     * Сохраним Статус в редис
     * -----------------------
     *
     * @param   string  $id
     * @param   string  $status
     */
    public function setStatus(string $id, string $status)
    {
        $this->redisConnect->set($this->key(static::STATUS, $id), $status);
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
    public function isSliderExists(string $id): bool
    {
        return $this->redisConnect->exists($this->key(static::DATA, $id));
    }

    /**
     * Получим количество картинок в слайдере
     * --------------------------------------
     *
     * @param   string  $id
     *
     * @return bool|string
     */
    public function getCountImages(string $id)
    {
        return $this->redisConnect->get($this->key(static::DATA, $id));
    }

    /**
     * Получим список картинок для REST API контроллера
     * ------------------------------------------------
     *
     * @param   string  $id
     *
     * @return array
     */
    public function getImages(string $id): array
    {
        $qty  = $this->getCountImages($id);
        $list = [];
        for ($i = 0; $i < $qty; $i++) {
            $list[] = Url::to("@web/images/{$id}/page-{$i}.jpg", true);
        }

        return $list;
    }

    /**
     * Получение списка ключиков в редисе по маске
     * -------------------------------------------
     *
     * @param   string  $mask
     *
     * @return array
     */
    public function getListIDs(string $mask)
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
        $list_ids_data  = $this->getListIDs(static::DATA . ":");
        $list_ids_time  = $this->getListIDs(static::TIME . ":");
        $keys_to_delete = array_diff($list_ids_data, $list_ids_time);

        return $keys_to_delete;
    }

    /**
     * Удаление всех ключей редиса для конкретного слайдера
     * ----------------------------------------------------
     *
     * @param   string  $id
     */
    public function deleteKey(string $id)
    {
        $keys = $this->redisConnect->keys("*:{$id}");
        $this->redisConnect->del($keys);
    }


    /**
     * Для каждого слайдера будем создавать отдельные каталоги
     * что-бы не валить все картинки в одну кучу
     * Да и удалять их потом будет приятнее.
     * -------------------------------------
     * @param string $id
     * @return string
     */
    public function getDirectory(string $id)
    {
        $dir = __DIR__ . "/../web/images/{$id}/";
        @mkdir($dir, 0777, true);

        return $dir;
    }

    /**
     * Удаляем полность все ключи в редисе и все связанные файлы
     * картинки и исходный pdf, и каталог где эти картинки лежали
     * ----------------------------------------------------------
     *
     * @param   string  $id
     * @param   bool    $source
     */
    public function deleteKeyWithFiles(string $id, bool $source = true)
    {
        /**
         * Удаляем картинки, zip архив и исходный pdf
         */
        $qty = $this->getCountImages($id);
        $dir = $this->getDirectory($id);

        for ($i = 0; $i < $qty; $i++) {
            @unlink("{$dir}page-{$i}.jpg");
        }

        @rmdir("{$dir}");
        @unlink(__DIR__ . "/../web/zips/{$id}.zip");

        if ($source) {
            $source_file = static::getSource($id);
            @unlink($source_file);
        }

        static::deleteKey($id);
    }
}