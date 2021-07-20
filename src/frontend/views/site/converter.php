<?php

/**
 * @var $this yii\web\View
 * @var $model frontend\models\UploadForm
 */

use frontend\messages\I18nApp;
use yii\bootstrap\Progress;

$this->title = 'Convert File :: ' . Yii::t('app', I18nApp::APP);
?>
<div class="site-index">
    <?= Progress::widget([
        'percent' => 0,
        'options' => ['id'=>'ProgressBar','data-id' => $model->id]
    ]);
    ?>
    <div id="ProgressLabel" class="my-icon my-icon-loading"></div>
    <div id="MessagesConsole"></div>
</div>
