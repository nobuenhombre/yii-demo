<?php

namespace common\services\slider\common;

/**
 * Class SliderKeyService
 */
class SliderKeyService
{
    /**
     * Маски ключей редиса
     * Для разного рода данных о слайдерах
     */
    public const
        ANY = '*', // любой из ниже перечисленных
        DATA = 'pdf.data', // число картинок для слайдера - Бессрочно
        TIME = 'pdf.time', // число картинок для слайдера - 30 минут для таймера удаления
        PROGRESS = 'pdf.progress', // прогресс конвертации PDF в набор картинок
        STATUS = 'pdf.status', // статус конвертации
        SOURCE = 'pdf.source', // оригинальное имя файла
        MESSAGE = 'pdf.message'; // список сообщений о ходе процесса конверсии

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
}