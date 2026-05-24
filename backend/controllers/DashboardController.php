<?php

declare(strict_types=1);

namespace backend\controllers;

use yii\web\Controller;

final class DashboardController extends Controller
{
    /**
     * Рендерит главную страницу склада
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}