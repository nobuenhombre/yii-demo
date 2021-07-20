<?php

use frontend\messages\I18nApp;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $id string
 * @var $qty int
 */

$this->title = 'Slider :: ' . Yii::t('app', I18nApp::APP);

?>
<div class="site-index">
    <a href="<?=Url::to("@web/zips/{$id}.zip", true);?>">Download ZIP File</a>
    <ul class="rslides" id="slider">
        <?php
            for($i=0; $i<$qty; $i++) {
                ?>
                <li><a href="#"><img src=".<?=Url::to("@web/images/{$id}/page-{$i}.jpg");?>" alt=""></a></li>
                <?php
            }
        ?>
    </ul>
</div>
