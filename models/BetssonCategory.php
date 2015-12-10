<?php

namespace BetssonSports\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "betsson_category".
 *
 * @property integer $id
 * @property integer $CategoryID
 * @property string $LanguageCode
 * @property string $CategoryName
 * @property string $CacheDate
 * @property string $CacheExpireDate
 * @property string $ErrorMessage
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BetssonCategoryLeague $betssonCategoryLeague
 * @property BetssonLeagueEvent[] $betssonLeagueEvents
 */
class BetssonCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'betsson_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['CategoryID', 'LanguageCode', 'CategoryName'], 'required'],
            [['CategoryID'], 'integer'],
            [['CacheDate', 'CacheExpireDate'], 'safe'],
            [['ErrorMessage'], 'string'],
            [['LanguageCode'], 'string', 'max' => 2],
            [['CategoryName'], 'string', 'max' => 255]
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
            'CategoryID' => 'Category ID',
            'LanguageCode' => 'Language Code',
            'CategoryName' => 'Category Name',
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
    public static function getForDropdown()
    {
        $models = static::find()->orderBy('CategoryName')->all();

        return ArrayHelper::map($models, 'CategoryID', 'CategoryName');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeague()
    {
        return $this->hasOne(BetssonCategoryLeague::className(), ['CategoryID' => 'CategoryID'])
            ->inverseOf('category');
    }
}
