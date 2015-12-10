<?php

namespace BetssonSports\models;

use Yii;
use yii\helpers\ArrayHelper;
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
    const STATUSNAME_OPEN = 10;
    const STATUSNAME_SUSPENDED = 30;
    const STATUSNAME_VOID = 70;
    const STATUSNAME_CLOSED = 80;
    const STATUSNAME_EPIRED = 100;

    static $statuses = [
        self::STATUSNAME_OPEN => 'Open',
        self::STATUSNAME_SUSPENDED => 'Suspended',
        self::STATUSNAME_VOID => 'Void',
        self::STATUSNAME_CLOSED => 'Closed',
        self::STATUSNAME_EPIRED => 'Expired',
    ];

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
            'LanguageCode' => 'Lang Code',
            'EventID' => 'Event ID',
            'BetGroupID' => 'Bet Group ID',
            'BetGroupTypeID' => 'Bet Group Type ID',
            'BetGroupStyleID' => 'Bet Group Style ID',
            'BetGroupName' => 'Bet Group',
            'BetGroupUnitID' => 'Bet Group Unit ID',
            'BetGroupUnitName' => 'Bet Group Unit Name',
            'MarketStartDate' => 'Start Date',
            'MarketEndDate' => 'End Date',
            'MarketPublishDate' => 'Publish Date',
            'MarketDeadline' => 'Market Deadline',
            'MarketStatusID' => 'Status ID',
            'MarketStatusName' => 'Status',
            'MarketURL' => 'Url',
            'IsLive' => 'Is Live',
            'SubParticipantName' => 'Sub Participant',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire',
            'ErrorMessage' => 'Error Message',
            'StartingPitchers' => 'Starting Pitchers',
            'created_at' => 'Created At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * Bet group name with variables parsed
     * @return string
     */
    public function getBetGroupNameParsed()
    {
        return str_replace(
            ['#player#', '#unit#'],
            [
                $this->SubParticipantName,
                $this->BetGroupUnitName
            ],
            $this->BetGroupName
        );
    }

    /**
     * @param null|integer $EventID
     * @param null|integer $LeagueID
     * @param null|integer $CategoryID
     *
     * @return array
     */
    public static function getForDropdown($EventID = null, $LeagueID = null, $CategoryID = null)
    {
        $query = static::find()
            ->andFilterWhere([self::tableName().'.EventID'=>$EventID])
            ->orderBy('BetGroupName')
            ->groupBy('BetGroupName');
        if (!is_null($LeagueID) && $LeagueID <> '') {
            $query
                ->innerJoinWith('event')
                ->andWhere([BetssonLeagueEvent::tableName().'.LeagueID' => $LeagueID]);
        }

        if (!is_null($CategoryID) && $CategoryID <> '') {
            $query
                ->innerJoinWith('event.league')
                ->andWhere([BetssonCategoryLeague::tableName().'.CategoryID' => $CategoryID]);
        }

        $models = $query->all();

        return ArrayHelper::map($models, 'BetGroupID', 'BetGroupName');
    }

    /**
     * Expire open market for which deadline has passed
     * @return int
     */
    static public function expireOpen() {
        return \Yii::$app->db
            ->createCommand()
            ->update(self::tableName(),
                [
                    'MarketStatusID' => self::STATUSNAME_EPIRED,
                    'MarketStatusName' => 'Expired',
                ], 'MarketStatusID = '.self::STATUSNAME_OPEN.' AND MarketEndDate < NOW() AND MarketDeadline < NOW()')
            ->execute();
    }

    /**
     * Remove expired markets older than deadline date
     * @param string $deadline_date
     * @return int
     */
    static public function deleteExpiredDeadline($deadline_date) {
        return \Yii::$app->db
            ->createCommand()
            ->delete(self::tableName(),
                [
                    'and',
                    ['MarketStatusID' => self::STATUSNAME_EPIRED],
                    ['<', 'MarketDeadline', $deadline_date]
                ])
            ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(BetssonLeagueEvent::className(), ['EventID' => 'EventID'])
            ->inverseOf('markets');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSelections()
    {
        return $this->hasMany(BetssonMarketSelection::className(), ['MarketID' => 'MarketID'])
            ->orderby('SelectionSortOrder')
            ->inverseOf('market');
    }
}
