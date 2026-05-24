<?php

declare(strict_types=1);

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Reservation Model
 *
 * @property int $id
 * @property int $product_id
 * @property string $order_id
 * @property int $quantity
 * @property string $status
 * @property int $expire_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Product $product
 */
final class Reservation extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_EXPIRED = 'expired';

    public static function tableName(): string
    {
        return '{{%reservations}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['product_id', 'order_id', 'quantity', 'expire_at'], 'required'],
            [['product_id', 'quantity', 'expire_at'], 'integer'],
            [['quantity'], 'integer', 'min' => 1], // Резервировать меньше 1 единицы товара нельзя
            [['order_id'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 32],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_EXPIRED]],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * Связь с моделью товара (Обратная связь)
     */
    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Хелпер для проверки, истёк ли резерв
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && time() > $this->expire_at;
    }
}