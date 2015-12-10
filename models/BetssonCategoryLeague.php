<?php

namespace BetssonSports\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "betsson_category_league".
 *
 * @property integer $id
 * @property integer $LeagueID
 * @property string $LanguageCode
 * @property integer $CategoryID
 * @property string $LeagueName
 * @property string $LeagueURL
 * @property string $CacheDate
 * @property string $CacheExpireDate
 * @property string $ErrorMessage
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BetssonCategory $category
 */
class BetssonCategoryLeague extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'betsson_category_league';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['LeagueID', 'LanguageCode', 'CategoryID', 'LeagueName'], 'required'],
            [['LeagueID', 'CategoryID'], 'integer'],
            [['LeagueURL', 'ErrorMessage'], 'string'],
            [['CacheDate', 'CacheExpireDate'], 'safe'],
            [['LanguageCode'], 'string', 'max' => 2],
            [['LeagueName'], 'string', 'max' => 255]
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
            'LeagueID' => 'League ID',
            'LanguageCode' => 'Lang Code',
            'CategoryID' => 'Category ID',
            'LeagueName' => 'Name',
            'LeagueURL' => 'Url',
            'CacheDate' => 'Cache Date',
            'CacheExpireDate' => 'Cache Expire Date',
            'ErrorMessage' => 'Error Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public static function getForDropdown($CategoryID = null)
    {
        $models = static::find()
            ->andFilterWhere(['CategoryID' => $CategoryID])
            ->orderBy('LeagueName')
            ->groupBy('LeagueName')
            ->all();

        return ArrayHelper::map($models, 'LeagueID', 'LeagueName');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(BetssonCategory::className(), ['CategoryID' => 'CategoryID'])
                    ->inverseOf('league');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvents()
    {
        return $this->hasMany(BetssonLeagueEvent::className(), ['LeagueID' => 'LeagueID'])
                    ->inverseOf('league');
    }
}
