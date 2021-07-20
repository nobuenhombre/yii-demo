<?php

/**
 * @var $this yii\web\View
 * @var $model frontend\models\UploadForm
 * @var $list array
 */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \frontend\messages\I18nApp;

$this->title = 'Please Upload File :: ' . \Yii::t('app', I18nApp::APP);

?>
<div class="site-index">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
    <?= $form->field($model, 'pdfFile')->fileInput() ?>
    <div id="Restrict50M" class="my-icon my-icon-info">Выберите PDF файл для конверсии.</div>
    <?= Html::submitButton('Конвертировать', ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end() ?>
    <?php
    if (count($list)>0) {
        ?>
        <h2>Список готовых слайдеров</h2>
        <ol>
            <?php
            foreach ($list as $id) {
                ?>
                <li><a href="<?= Url::toRoute(['site/slider', 'id' => $id]); ?>">Slider - <?= $id ?></a></li>
                <?php
            }
            ?>
        </ol>
        <?php
    }
    ?>
</div>
