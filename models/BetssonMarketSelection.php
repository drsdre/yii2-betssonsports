<?php

namespace BetssonSports\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "betsson_market_selection".
 *
 * @property integer $id
 * @property integer $SelectionID
 * @property string $LanguageCode
 * @property integer $MarketID
 * @property double $Odds
 * @property double $SelectionLimitValue
 * @property integer $SelectionStatus
 * @property string $SelectionStatusName
 * @property string $SelectionName
 * @property integer $SelectionSortOrder
 * @property string $CacheDate
 * @property string $CacheExpireDate
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BetssonEventMarket $market
 */
class BetssonMarketSelection extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'betsson_market_selection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SelectionID', 'LanguageCode', 'MarketID', 'Odds', 'SelectionLimitValue', 'SelectionStatus', 'SelectionStatusName', 'SelectionName', 'SelectionSortOrder'], 'required'],
            [['SelectionID', 'MarketID', 'SelectionStatus', 'SelectionSortOrder'], 'integer'],
            [['Odds', 'SelectionLimitValue'], 'number'],
            [['CacheDate', 'CacheExpireDate'], 'safe'],
            [['LanguageCode'], 'string', 'max' => 2],
            [['SelectionStatusName', 'SelectionName'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'SelectionID' => 'Selection ID',
            'LanguageCode' => 'Language Code',
            'MarketID' => 'Market ID',
            'Odds' => 'Odds',
            'SelectionLimitValue' => 'Selection Limit Value',
            'SelectionStatus' => 'Selection Status',
            'SelectionStatusName' => 'Selection Status Name',
            'SelectionName' => 'Selection Name',
            'SelectionSortOrder' => 'Selection Sort Order',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarket()
    {
        return $this->hasOne(BetssonEventMarket::className(), ['id' => 'MarketID']);
    }
}
