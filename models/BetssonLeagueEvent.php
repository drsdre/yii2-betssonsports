<?php

namespace BetssonSports\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "betsson_league_event".
 *
 * @property integer $id
 * @property integer $EventID
 * @property string $LanguageCode
 * @property integer $LeagueID
 * @property string $EventName
 * @property string $EventDeadline
 * @property string $EventURL
 * @property string $CacheDate
 * @property string $CacheExpireDate
 * @property string $ErrorMessage
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BetssonEventMarket[] $betssonEventMarkets
 * @property BetssonCategory $subCategory
 */
class BetssonLeagueEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'betsson_league_event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['EventID', 'LanguageCode', 'LeagueID', 'EventName', 'EventDeadline'], 'required'],
            [['EventID', 'LeagueID'], 'integer'],
            [['EventDeadline', 'CacheDate', 'CacheExpireDate'], 'safe'],
            [['EventURL', 'ErrorMessage'], 'string'],
            [['LanguageCode'], 'string', 'max' => 2],
            [['EventName'], 'string', 'max' => 255]
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
            'EventID' => 'Event ID',
            'LanguageCode' => 'Language Code',
            'LeagueID' => 'League ID',
            'EventName' => 'Event Name',
            'EventDeadline' => 'Event Deadline',
            'EventURL' => 'Event Url',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire Date',
            'ErrorMessage' => 'Error Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetssonEventMarkets()
    {
        return $this->hasMany(BetssonEventMarket::className(), ['EventID' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory()
    {
        return $this->hasOne(BetssonCategory::className(), ['id' => 'SubCategoryID']);
    }
}
