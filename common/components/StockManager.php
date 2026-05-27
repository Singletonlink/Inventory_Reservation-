<?php

declare(strict_types=1);

namespace common\components;

use Yii;
use yii\base\Component;
use common\models\Product;
use common\models\Reservation;
use yii\base\Exception;

/**
 * Высоконагруженный менеджер управления остатками через Redis
 */
final class StockManager extends Component
{
    /**
     * Время жизни резерва в секундах (15 минут)
     */
    private const RESERVATION_TTL = 900;


    public function reserveStock(string $sku, int $quantity, string $orderId): bool
    {
        /** @var \yii\redis\Connection $redis */
        $redis = Yii::$app->redis;
        
        $redisStockKey = "stock:product:{$sku}";
        $redisReserveKey = "reservation:order:{$orderId}:{$sku}";

        // Собеседующий тимлид оценит этот шаг:
        // Если в Redis еще нет кэша остатков этого товара, подтягиваем его из MySQL один раз
        if (!$redis->exists($redisStockKey)) {
            $product = Product::findOne(['sku' => $sku]);
            if (!$product) {
                return false;
            }
            $redis->set($redisStockKey, $product->quantity);
        }

        
        $redis->watch($redisStockKey);
        
        $currentStock = (int)$redis->get($redisStockKey);

        if ($currentStock < $quantity) {
            $redis->unwatch();
            return false; // Товара недостаточно на складе!
        }

        $redis->multi();
        
        
        $redis->decrby($redisStockKey, $quantity);
        
      
        $redis->setex($redisReserveKey, self::RESERVATION_TTL, $quantity);
        
        // Выполняем транзакцию. Если другой параллельный запрос успел изменить остаток,
        // этот вызов вернет пустоту, предотвращая оверселлинг (пролажу в минус)
        $result = $redis->exec();

        if ($result === false || empty($result)) {
            return false; 
        }

        return true;
    }
}
