<?php

namespace BetssonSports\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "betsson_event_market".
 *
 * @property integer $id
 * @property integer $MarketID
 * @property integer $LanguageCode
 * @property integer $EventID
 * @property integer $BetGroupID
 * @property integer $BetGroupTypeID
 * @property integer $BetGroupStyleID
 * @property string $BetGroupName
 * @property integer $BetGroupUnitID
 * @property string $BetGroupUnitName
 * @property string $MarketStartDate
 * @property string $MarketEndDate
 * @property string $MarketPublishDate
 * @property string $MarketDeadline
 * @property integer $MarketStatusID
 * @property string $MarketStatusName
 * @property string $MarketURL
 * @property integer $IsLive
 * @property string $SubParticipantName
 * @property string $CacheDate
 * @property string $CacheExpireDate
 * @property string $ErrorMessage
 * @property string $StartingPitchers
 * @property integer $created_at
 * @property integer $update_at
 *
 * @property BetssonLeagueEvent $event
 * @property BetssonMarketSelection[] $betssonMarketSelections
 */
class BetssonEventMarket extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'betsson_event_market';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MarketID', 'LanguageCode', 'EventID', 'BetGroupID', 'BetGroupTypeID', 'BetGroupStyleID', 'BetGroupName', 'BetGroupUnitID', 'MarketDeadline', 'MarketStatusID', 'MarketStatusName', 'IsLive'], 'required'],
            [['MarketID', 'EventID', 'BetGroupID', 'BetGroupTypeID', 'BetGroupStyleID', 'BetGroupUnitID', 'MarketStatusID'], 'integer'],
            [['MarketStartDate', 'MarketEndDate', 'MarketPublishDate', 'MarketDeadline', 'CacheDate', 'CacheExpireDate'], 'safe'],
            [['IsLive'], 'boolean'],
            [['MarketURL', 'ErrorMessage'], 'string'],
            [['BetGroupName', 'BetGroupUnitName', 'MarketStatusName', 'SubParticipantName', 'StartingPitchers'], 'string', 'max' => 255]
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
            'MarketID' => 'Market ID',
            'LanguageCode' => 'Language Code',
            'EventID' => 'Event ID',
            'BetGroupID' => 'Bet Group ID',
            'BetGroupTypeID' => 'Bet Group Type ID',
            'BetGroupStyleID' => 'Bet Group Style ID',
            'BetGroupName' => 'Bet Group Name',
            'BetGroupUnitID' => 'Bet Group Unit ID',
            'BetGroupUnitName' => 'Bet Group Unit Name',
            'MarketStartDate' => 'Market Start Date',
            'MarketEndDate' => 'Market End Date',
            'MarketPublishDate' => 'Market Publish Date',
            'MarketDeadline' => 'Market Deadline',
            'MarketStatusID' => 'Market Status ID',
            'MarketStatusName' => 'Market Status Name',
            'MarketURL' => 'Market Url',
            'IsLive' => 'Is Live',
            'SubParticipantName' => 'Sub Participant Name',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire Date',
            'ErrorMessage' => 'Error Message',
            'StartingPitchers' => 'Starting Pitchers',
            'created_at' => 'Created At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(BetssonLeagueEvent::className(), ['id' => 'EventID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetssonMarketSelections()
    {
        return $this->hasMany(BetssonMarketSelection::className(), ['MarketID' => 'id']);
    }
}
