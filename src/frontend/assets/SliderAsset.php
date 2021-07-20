<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Class SliderAsset
 *
 * Ассет для создания кастомной страницы Слайдера
 * Базовый URL изменен на точку,
 * для того чтобы после скачивания - файл открывался и работал
 * в браузере даже с локального диска
 *
 * @package frontend\assets
 */
class SliderAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '.';
    public $css = [
        'assets/slider/css/slider.css',
    ];
    public $js = [
        'assets/slider/js/jquery.js',
        'assets/slider/js/slider.js'
    ];
    public $depends = [
    ];
}
