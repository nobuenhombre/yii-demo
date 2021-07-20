<?php

namespace common\services;

use \yii\helpers\Url;
use Imagick;
use ZipArchive;

/**
 * Class PdfConverter
 *
 * Класс собственно выполняет конверсию PDF в Html Слайдер.
 *
 * @package common\services
 */
class PdfConverter {

    public
        $id,
        $file_name,
        $slider_folder,
        $pages_count;

    private
        $imagick, $zip_archive;

    /**
     * Данный класс будет вызываться в AJAX
     * соответсвенно файл PDF постом туда передавать глупо
     * тем более что мы его уже получили от клиента
     * В модели models/UploadForm мы через редис при помощи класса SliderStore
     * сохраним ссылочки на данный файл.
     * Получим их и обработаем.
     * ------------------------
     * PdfConverter constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->file_name = SliderStore::getSource($this->id);
    }


    /**
     * Конверсия и создание ZIP архива.
     *
     * Для отображения статуса конверсии через AJAX
     * По мере исполнения в редис пишутся
     *
     * 1) Процент исполнения
     * этот процент я привязал к сохранению картинок
     * (Saved/Total)*100
     *
     * 2) Статус - (В процессе, Ошибка или Успех)
     *
     * 3) Сообщения - что конкретно сейчас выполняется
     *
     * По ходу работы функции могут случиться исключения
     * 1) ограничение - Портрет
     * 2) ограничение - число страниц
     * 3) ошибка создания архива
     */
    public function convert()
    {
        try {
            SliderStore::setStatus($this->id, SliderStore::STATUS_IN_PROGRESS);

            SliderStore::addMessage($this->id, "Clear old data for {$this->id}");
            SliderStore::deleteKeyWithFiles($this->id, false);

            SliderStore::addMessage($this->id, "Create new ZipArchive()");
            $this->zip_archive = new ZipArchive();

            if ($this->zip_archive->open(__DIR__ . "/../web/zips/{$this->id}.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

                SliderStore::addMessage($this->id, "Create new Imagick()");
                $this->imagick = new Imagick($this->file_name);

                SliderStore::addMessage($this->id, "identifyImage()");
                $identify = $this->imagick->identifyImage();
                if ($identify['geometry']['width'] > $identify['geometry']['height']) {
                    throw new \Exception("Сработало ограничение - PDF должен иметь портретную ориентацию!", 2);
                }

                SliderStore::addMessage($this->id, "Read PDF ...");
                $this->pages_count = $this->imagick->getNumberImages();
                if ($this->pages_count > 20) {
                    throw new \Exception("Сработало ограничение - В Файле более 20 страниц!", 2);
                }
                SliderStore::create($this->id, $this->pages_count);

                SliderStore::addMessage($this->id, "Setup background color");
                $this->imagick->setImageBackgroundColor('white');

                SliderStore::addMessage($this->id, "Setup resolution");
                $this->imagick->setResolution(300, 300);

                SliderStore::addMessage($this->id, "Setup JPG compression");
                $this->imagick->setImageCompressionQuality(100);

                for ($page_num = 0; $page_num < $this->pages_count; $page_num++) {

                    SliderStore::addMessage($this->id, "Write image page-{$page_num}.jpg");
                    $this->imagick->setIteratorIndex($page_num);
                    $dir = static::get_directory($this->id);
                    $this->imagick->writeImage("{$dir}page-{$page_num}.jpg");

                    SliderStore::addMessage($this->id, "Add image page-{$page_num}.jpg to ZIP archive");
                    $this->zip_archive->addFile("{$dir}page-{$page_num}.jpg","/images/{$this->id}/page-{$page_num}.jpg");

                    SliderStore::setProgress($this->id, $this->pages_count, $page_num);
                }
                $this->imagick->destroy();

                SliderStore::addMessage($this->id, "Add assets/slider/css/slider.css to ZIP archive");
                $this->zip_archive->addFile(__DIR__ . "/../web/assets/slider/css/slider.css", "/assets/slider/css/slider.css");

                SliderStore::addMessage($this->id, "Add assets/slider/js/slider.js to ZIP archive");
                $this->zip_archive->addFile(__DIR__ . "/../web/assets/slider/js/slider.js", "/assets/slider/js/slider.js");

                SliderStore::addMessage($this->id, "Add assets/slider/js/jquery.js to ZIP archive");
                $this->zip_archive->addFile(__DIR__ . "/../web/assets/slider/js/jquery.js", "/assets/slider/js/jquery.js");

                SliderStore::addMessage($this->id, "Add assets/slider/js/jquery.js to ZIP archive");
                $this->zip_archive->setArchiveComment("PDF to HTML Slider converter demo - {$this->id}");

                SliderStore::addMessage($this->id, "Add index.html to ZIP archive");
                $this->zip_archive->addFromString('index.html', file_get_contents(Url::toRoute(['site/slider', 'id' => $this->id], true)));

            } else {
                throw new \Exception("Can't create Zip!", 1);
            }
            $this->zip_archive->close();
            SliderStore::addMessage($this->id, "All complete!");
            SliderStore::setTimer($this->id, $this->pages_count);
            SliderStore::setStatus($this->id, SliderStore::STATUS_SUCCESS);
        } catch(\Exception $exception) {
            SliderStore::addMessage($this->id, "Error: ".$exception->getMessage());
            SliderStore::setStatus($this->id, SliderStore::STATUS_ERROR);
        }
    }
}