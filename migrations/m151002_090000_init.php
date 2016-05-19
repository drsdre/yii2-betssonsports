<?php

use yii\db\Schema;
use yii\db\Migration;

class m151002_090000_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%betsson_category}}', [
            'id' => Schema::TYPE_PK,
            'CategoryID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LanguageCode' => Schema::TYPE_STRING . '(2) NOT NULL',

            'CategoryName' => Schema::TYPE_STRING . '(255) NOT NULL',

            'CacheDate' => Schema::TYPE_DATETIME . ' NULL',
            'CacheExpireDate' => Schema::TYPE_DATETIME . ' NULL',
            'ErrorMessage' => Schema::TYPE_TEXT . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        $this->createIndex('betsson_category_language_idlang', '{{%betsson_category}}', 'CategoryID, LanguageCode', true);
        $this->createIndex('betsson_category_name', '{{%betsson_category}}', 'CategoryName');

        $this->createTable('{{%betsson_category_league}}', [
            'id' => Schema::TYPE_PK,
            'LeagueID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LanguageCode' => Schema::TYPE_STRING . '(2) NOT NULL',

            'CategoryID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LeagueName' => Schema::TYPE_STRING . '(255) NOT NULL',
            'LeagueURL' => Schema::TYPE_TEXT . ' NULL',

            'CacheDate' => Schema::TYPE_DATETIME . ' NULL',
            'CacheExpireDate' => Schema::TYPE_DATETIME . ' NULL',
            'ErrorMessage' => Schema::TYPE_TEXT . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        // fk: betsson_category
        $this->addForeignKey('betsson_category_league_category_id', '{{%betsson_category_league}}', 'CategoryID', '{{%betsson_category}}', 'CategoryID', 'CASCADE','RESTRICT');
        $this->createIndex('betsson_category_league_idlang', '{{%betsson_category_league}}', 'LeagueID, LanguageCode', true);
        $this->createIndex('betsson_category_league_name', '{{%betsson_category_league}}', 'LeagueName');

        $this->createTable('{{%betsson_league_event}}', [
            'id' => Schema::TYPE_PK,
            'EventID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LanguageCode' => Schema::TYPE_STRING . '(2) NOT NULL',

            'LeagueID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'EventName' => Schema::TYPE_STRING . '(255) NOT NULL',
            'EventDeadline' => Schema::TYPE_DATETIME . ' NOT NULL',
            'EventURL' => Schema::TYPE_TEXT . ' NULL',

            'CacheDate' => Schema::TYPE_DATETIME . ' NULL',
            'CacheExpireDate' => Schema::TYPE_DATETIME . ' NULL',
            'ErrorMessage' => Schema::TYPE_TEXT . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        // fk: betsson_category
        $this->addForeignKey('betsson_league_event_league_id', '{{%betsson_league_event}}', 'LeagueID', '{{%betsson_category_league}}', 'LeagueID', 'CASCADE','RESTRICT');
        $this->createIndex('betsson_league_event_idlang', '{{%betsson_league_event}}', 'EventID, LanguageCode', true);
        $this->createIndex('betsson_league_event_name', '{{%betsson_league_event}}', 'EventName');

        $this->createTable('{{%betsson_event_market}}', [
            'id' => Schema::TYPE_PK,
            'MarketID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LanguageCode' => Schema::TYPE_STRING . '(2) NOT NULL',

            'EventID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'BetGroupID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'BetGroupTypeID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'BetGroupStyleID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'BetGroupName' => Schema::TYPE_STRING . '(255) NULL',
            'BetGroupUnitID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'BetGroupUnitName' => Schema::TYPE_STRING . '(255) NULL',

            'MarketStartDate' => Schema::TYPE_DATETIME . ' NULL',
            'MarketEndDate' => Schema::TYPE_DATETIME . ' NULL',
            'MarketPublishDate' => Schema::TYPE_DATETIME . ' NULL',
            'MarketDeadline' => Schema::TYPE_DATETIME . ' NOT NULL',
            'MarketStatusID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'MarketStatusName' => Schema::TYPE_STRING . '(255) NOT NULL',
            'MarketURL' => Schema::TYPE_TEXT . ' NULL',
            'IsLive' => Schema::TYPE_BOOLEAN . ' NOT NULL',
            'SubParticipantName' => Schema::TYPE_STRING . '(255) NULL',
            'StartingPitchers' => Schema::TYPE_STRING . '(255) NULL',

            'CacheDate' => Schema::TYPE_DATETIME . ' NULL',
            'CacheExpireDate' => Schema::TYPE_DATETIME . ' NULL',
            'ErrorMessage' => Schema::TYPE_TEXT . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        // fk: betsson_category
        $this->addForeignKey('betsson_event_market_event_id', '{{%betsson_event_market}}', 'EventID', '{{%betsson_league_event}}', 'EventID', 'CASCADE','RESTRICT');
        $this->createIndex('betsson_event_market_idlang', '{{%betsson_event_market}}', 'MarketID, LanguageCode', true);
        $this->createIndex('betsson_event_market_search', '{{%betsson_event_market}}', 'BetGroupName, MarketStatusID, MarketDeadline');

        $this->createTable('{{%betsson_market_selection}}', [
            'id' => Schema::TYPE_PK,
            'SelectionID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'LanguageCode' => Schema::TYPE_STRING . '(2) NOT NULL',

            'MarketID' => Schema::TYPE_INTEGER . ' NOT NULL',
            'Odds' => Schema::TYPE_DOUBLE . '(12,2) NOT NULL',
            'SelectionLimitValue' => Schema::TYPE_DOUBLE . '(12,2) NOT NULL',
            'SelectionStatus' => Schema::TYPE_INTEGER . ' NOT NULL',
            'SelectionStatusName' => Schema::TYPE_STRING . '(255) NULL',
            'SelectionName' => Schema::TYPE_STRING . '(255) NOT NULL',
            'SelectionSortOrder' => Schema::TYPE_INTEGER . ' NOT NULL',

            'CacheDate' => Schema::TYPE_DATETIME . ' NULL',
            'CacheExpireDate' => Schema::TYPE_DATETIME . ' NULL',
            'ErrorMessage' => Schema::TYPE_TEXT . ' NULL',

            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        // fk: betsson_category
        $this->addForeignKey('betsson_market_selection_market_id', '{{%betsson_market_selection}}', 'MarketID', '{{%betsson_event_market}}', 'MarketID', 'CASCADE','RESTRICT');
        $this->createIndex('betsson_market_selection_idlang', '{{%betsson_market_selection}}', 'SelectionID, LanguageCode', true);
        $this->createIndex('betsson_market_selection_search', '{{%betsson_market_selection}}', 'SelectionName, SelectionStatus, SelectionSortOrder');

    }

    public function down()
    {
        $this->dropForeignKey('betsson_market_selection_market_id', '{{%betsson_market_selection}}');
        $this->dropTable('{{%betsson_market_selection}}');
        $this->dropForeignKey('betsson_event_market_event_id', '{{%betsson_event_market}}');
        $this->dropTable('{{%betsson_event_market}}');
        $this->dropForeignKey('betsson_league_event_league_id', '{{%betsson_league_event}}');
        $this->dropTable('{{%betsson_league_event}}');
        $this->dropForeignKey('betsson_category_league_category_id', '{{%betsson_category_league}}');
        $this->dropTable('{{%betsson_category_league}}');
        $this->dropTable('{{%betsson_category}}');
    }
}
