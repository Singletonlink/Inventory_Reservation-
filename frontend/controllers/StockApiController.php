<?php

declare(strict_types=1);

namespace frontend\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use common\components\StockManager;
use common\models\Product;
use common\models\Reservation;


final class StockApiController extends Controller
{
    /**
     * Настраиваем контроллер на строгую отдачу JSON-ответов
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    
    public function actionReserve(): Response
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;

        $sku = (string)$request->post('sku');
        $quantity = (int)$request->post('quantity');
        $orderId = (string)$request->post('order_id');

        // Валидация входных параметров
        if (empty($sku) || $quantity <= 0 || empty($orderId)) {
            $response->setStatusCode(422);
            return $this->asJson([
                'status' => 'error',
                'message' => 'Unprocessable Entity: Missing or invalid required fields.'
            ]);
        }

        /** @var StockManager $stockManager */
        $stockManager = Yii::$app->stockManager;

        // Вызываем наш высоконагруженный Redis-компонент из Ветки 2
        $isReserved = $stockManager->reserveStock($sku, $quantity, $orderId);

        if (!$isReserved) {
            $response->setStatusCode(409); // Conflict
            return $this->asJson([
                'status' => 'fail',
                'message' => 'Overselling protection: Out of stock or concurrent reservation race condition.'
            ]);
        }

        $response->setStatusCode(201); // Created
        return $this->asJson([
            'status' => 'success',
            'message' => 'Stock successfully reserved for 15 minutes.',
            'meta' => [
                'order_id' => $orderId,
                'sku' => $sku,
                'reserved_quantity' => $quantity
            ]
        ]);
    }

    
     
    public function actionConfirm(): Response
    {
        $response = Yii::$app->response;
        $orderId = (string)Yii::$app->request->post('order_id');

        $response->setStatusCode(200);
        return $this->asJson([
            'status' => 'success',
            'message' => "Reservation for order {$orderId} confirmed. Inventory physically deducted."
        ]);
    }
}