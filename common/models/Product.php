<?php

declare(strict_types=1);

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Product Model
 *
 * @property int $id
 * @property string $sku
 * @property string $title
 * @property int $quantity
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Reservation[] $reservations
 */
final class Product extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%products}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class, // Автоматическое заполнение created_at и updated_at
        ];
    }

    public function rules(): array
    {
        return [
            [['sku', 'title'], 'required'],
            [['sku'], 'string', 'max' => 64],
            [['sku'], 'unique'],
            [['title'], 'string', 'max' => 255],
            [['quantity'], 'integer', 'min' => 0], // Защита на уровне валидации: остаток не может быть отрицательным
        ];
    }

    /**
     * Связь с таблицей резервов (Один ко Многим)
     */
    public function getReservations(): ActiveQuery
    {
        return $this->hasMany(Reservation::class, ['product_id' => 'id']);
    }
}