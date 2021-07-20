<?php

namespace frontend\controllers;

use common\components\SliderStore;
use common\services\RedisNativeService;
use frontend\models\UploadForm;
use Yii;
use yii\helpers\BaseArrayHelper;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Class SiteController
 *
 * @package app\controllers
 */
class SiteController extends Controller
{
    // @var \Redis
    private $redisConnect;

    public function __construct(
        $id,
        $module,
        RedisNativeService $redisService,
        $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->redisConnect = $redisService->getConnect();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'slider'],
                        'allow' => true,
                        'roles' => ['?','@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Эта конструкция нужна для того чтобы в функциях экшенов
     * входящие параметры также передавались из POST запросов
     * а не только через GET
     * ------------------------------------------------------
     * @param string $id
     * @param array $params
     * @return mixed
     */
    public function runAction($id, $params = [])
    {
        // Extract the params from the request and bind them to params
        $params = BaseArrayHelper::merge(Yii::$app->getRequest()->getBodyParams(), $params);
        return parent::runAction($id, $params);
    }

    /**
     * Главная страница с формой для передачи PDF файла
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new UploadForm();
        if (Yii::$app->request->isPost) {
            $model->pdfFile = UploadedFile::getInstance($model, 'pdfFile');
            if ($model->upload()) {
                return $this->render('converter', ['model' => $model]);
            }
        }
        $list_sliders = SliderStore::get_list_ids(SliderStore::DATA.":");
        return $this->render('index', ['model' => $model, 'list'=>$list_sliders]);
    }

    /**
     * Страница слайдера
     *
     * @param string $id
     * @return string
     */
    public function actionSlider(string $id)
    {
        if (SliderStore::slider_exists($id)) {
            $this->layout = 'slider';
            $qty = SliderStore::get_qty_images($id);
            return $this->render('slider', ['id' => $id, 'qty' => $qty]);
        } else {
            return $this->render('error', [
                'name'=>'Слайдера не существует!',
                'message'=>'Слайдера не существует! Либо его никогда не было, либо он был удален через 30 минут после создания!'
            ]);
        }
    }

}
