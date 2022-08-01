<?php

namespace app\controllers;

use app\models\Admins;
use yii\web\Controller;

class AdminController extends Controller
{
    public function actionIndex()
    {

        $redis = \Yii::$app->get("redis");
        $redis->set("name","孙乔雨");

        $admins = Admins::find()->asArray()->all();

        echo json_encode($admins, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    }
}