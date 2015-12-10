<?php

namespace BetssonSports\models;

use Yii;
use yii\helpers\ArrayHelper;
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
    const STATUSNAME_OPEN = 1;
    const STATUSNAME_WON = 2;
    const STATUSNAME_LOST = 3;
    const STATUSNAME_VOID = 4;
    const STATUSNAME_HALF_WON = 5;
    const STATUSNAME_HALF_LOST = 6;
    const STATUSNAME_EXPIRED = 10;

    static $statuses = [
        self::STATUSNAME_OPEN => 'Open',
        self::STATUSNAME_WON => 'Won',
        self::STATUSNAME_LOST => 'Lost',
        self::STATUSNAME_VOID => 'Void',
        self::STATUSNAME_HALF_WON => 'Half won',
        self::STATUSNAME_HALF_LOST => 'Half lost',
        self::STATUSNAME_EXPIRED => 'Expired',
    ];

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
            'BetGroupNameParsed' => 'Bet Group',
            'SelectionName' => 'Selection',
            'Odds' => 'Odds',
            'SelectionLimitValue' => 'Limit Value',
            'SelectionStatus' => 'Status ID',
            'SelectionStatusName' => 'Status',
            'SelectionSortOrder' => 'Sort Order',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public static function getForDropdown($MarketID = null)
    {
        $models = static::find()
                        ->andFilterWhere(['MarketID' => $MarketID])
                        ->orderBy('SelectionName')
                        ->groupBy('SelectionName')
                        ->all();

        return ArrayHelper::map($models, 'SelectionID', 'SelectionName');
    }

    /**
     * @return array
     */
    public static function getSelectionStatusList()
    {
        return self::$statuses;
    }

    /**
     * Returns the status name in nice format.
     *
     * @param  null|integer $status_id Status integer value if sent to method.
     * @return string               Nicely formatted type.
     */
    public function getSelectionStatusName($status_id = null)
    {
        if (is_null($status_id)) {
            $status_id = $this->SelectionStatus;
        }
        if (array_key_exists($status_id, self::$statuses)) {
            return self::$statuses[$status_id];
        }
        else {
            return $status_id;
        }
    }

    public function getBetGroupNameParsed()
    {
        return str_replace(
            ['#player#', '#limit#', '#unit#'],
            [
                $this->market->SubParticipantName,
                $this->SelectionLimitValue,
                $this->market->BetGroupUnitName
            ],
            $this->market->BetGroupName
        );
    }

    public function getSelectionNameParsed()
    {
        return str_replace(
            ['#player#', '#limit#', '#unit#'],
            [
                $this->market->SubParticipantName,
                $this->SelectionLimitValue,
                $this->market->BetGroupUnitName
            ],
            $this->SelectionName
        );
    }

    /**
     * Expire open selections for which Market has expired
     * @return int
     */
    static public function expireOpen() {
        return \Yii::$app->db
            ->createCommand('update '.self::tableName().' AS selection LEFT JOIN '.BetssonEventMarket::tableName(). ' AS market ON '.
                'selection.MarketID = market.MarketID '.
                'SET selection.SelectionStatus = '.self::STATUSNAME_EXPIRED.', selection.SelectionStatusName = "Expired" '.
                'WHERE SelectionStatus = '.self::STATUSNAME_OPEN.' AND market.MarketStatusID = ' . BetssonEventMarket::STATUSNAME_EPIRED)
            ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarket()
    {
        return $this->hasOne(BetssonEventMarket::className(), ['MarketID' => 'MarketID'])
            ->inverseOf('selections');
    }
}
