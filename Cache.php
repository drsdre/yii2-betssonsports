<?php
/**
 * Nordicbet.com API Yii2 Client Component
 *
 * @author Andre Schuurman <andre.schuurman@gmail.com>
 * @license MIT License
 */

namespace BetssonSports;

use BetssonSports\models\BetssonCategory;
use BetssonSports\models\BetssonCategoryLeague;
use BetssonSports\models\BetssonEventMarket;
use BetssonSports\models\BetssonLeagueEvent;
use BetssonSports\models\BetssonMarketSelection;

use BetssonSports\Exception;

class Cache {

	protected $_client;

	public function __construct($client) {
		$this->_client = $client;
	}

	/**
	 * Initiate the data in the databases
	 *
	 * @return bool
	 */
	public function initData() {
		return $this->GetActiveSubCategories();
	}


	public function updateData() {
		return $this->GetLatestUpdates();
	}

	/**
	 * @return bool
	 * @throws \BetssonSports\Exception
	 */
	protected function GetLatestUpdates() {

		// Mark time when update starts (make sure server time is set to UTC)
		$update_start_time = time();

		// Get data from API
		$result = $this->_client->GetLatestUpdates();


		// Check if data is available
		if ( ! isset($result->GetLatestUpdatesResult->SubCategory ) ) {
			return;
		}

		$subCategories = $result->GetLatestUpdatesResult->SubCategory;

		if (!is_array($subCategories)) {
			$subCategories = [$subCategories];
		}

		foreach($subCategories as $key => $sub_category ) {
			// Store the data
			$BetssonCategoryLeague = $this->StoreCategoryLeague( $sub_category );

			// Check if data is available
			if ( ! isset( $sub_category->SubCategoryEvents ) ) {
				continue;
			}

			$events = $sub_category->SubCategoryEvents;

			if ( ! is_array( $events ) ) {
				$events = [$events];
			}

			foreach ( $events as $key => $event ) {

				// Store the data
				$BetssonLeagueEvent = $this->storeEvent( $event, $BetssonCategoryLeague->LeagueID );

				// Check if data is available
				if ( ! isset( $event->EventMarkets ) ) {
					continue;
				}

				$markets = $event->EventMarkets;

				if ( ! is_array( $markets ) ) {
					$markets = [$markets];
				}

				foreach ( $markets as $key => $market ) {
					// Store the data
					$BetssonEventMarket = $this->storeMarket( $market, $BetssonLeagueEvent->EventID );

					// Check if data is available
					if ( ! isset( $market->MarketSelections->MarketSelection ) ) {
						continue;
					}

					$selections = $market->MarketSelections->MarketSelection;

					if ( ! is_array( $selections ) ) {
						$selections = [$selections];
					}

					foreach ( $selections as $key => $selection ) {
						// Store the data
						$BetssonEventMarket = $this->storeSelection( $selection, $BetssonEventMarket->MarketID );
					}
				}
			}
		}
		$this->_client->SetLastUpdate(['utcTimeStamp' => date(DATE_ATOM, $update_start_time)]);

		return true;
	}

	/**
	 * @return bool
	 */
	protected function GetActiveSubCategories() {

		// Mark time when update starts (make sure server time is set to UTC)
		$update_start_time = time();

		// Get data from API
		$result = $this->_client->GetActiveSubCategories();

		// Check if data is available
		if ( !isset($result->GetActiveSubCategoriesResult->SubCategory) ) {
			return false;
		}

		// Parse the data
		foreach( $result->GetActiveSubCategoriesResult->SubCategory as $key => $sub_category ) {
			// Store the data
			if (is_object( $sub_category )) {
				$BetssonCategoryLeague = $this->StoreCategoryLeague( $sub_category );

				// Get the events for league
				$this->GetActiveEventsForSubCategory($BetssonCategoryLeague->LeagueID);
			}
		}

		$this->_client->SetLastUpdate(['utcTimeStamp' => date(DATE_ATOM, $update_start_time)]);

		return true;
	}

	/**
	 * @param $sub_category
	 *
	 * @return BetssonCategoryLeague|null|static
	 * @throws \BetssonSports\Exception
	 */
	protected function StoreCategoryLeague($sub_category) {

		// Load or store category
		if ( ! $BetssonCategory = BetssonCategory::findOne([
			'CategoryID' => intval($sub_category->CategoryID),
			'LanguageCode' => $sub_category->LanguageCode,
		])
		) {
			$BetssonCategory = new BetssonCategory();
			$BetssonCategory->CategoryID = intval($sub_category->CategoryID);
			$BetssonCategory->LanguageCode = $sub_category->LanguageCode;
		}
		$BetssonCategory->CategoryName = $sub_category->CategoryName;
		$BetssonCategory->CacheDate = $sub_category->CacheDate;
		$BetssonCategory->CacheExpireDate = $sub_category->CacheExpireDate;
		$BetssonCategory->ErrorMessage = $sub_category->ErrorMessage;

		if (!$BetssonCategory->save() ) {
			throw new Exception(print_r($BetssonCategory->errors, true));
		}


		// Load or store category league
		if ( ! $BetssonCategoryLeague = BetssonCategoryLeague::findOne([
			'LeagueID' => intval($sub_category->SubCategoryID),
			'LanguageCode' => $sub_category->LanguageCode,
		])
		) {
			$BetssonCategoryLeague = new BetssonCategoryLeague();
			$BetssonCategoryLeague->LeagueID = intval($sub_category->SubCategoryID);
			$BetssonCategoryLeague->LanguageCode = $sub_category->LanguageCode;
		}
		$BetssonCategoryLeague->CategoryID = intval($sub_category->CategoryID);
		$BetssonCategoryLeague->LeagueName = $sub_category->SubCategoryName;
		$BetssonCategoryLeague->LeagueURL = $sub_category->SubCategoryURL;
		$BetssonCategoryLeague->CacheDate = gmdate("Y-m-d H:i:s", strtotime($sub_category->CacheDate));
		$BetssonCategoryLeague->CacheExpireDate = gmdate("Y-m-d H:i:s", strtotime($sub_category->CacheExpireDate));
		$BetssonCategoryLeague->ErrorMessage = $sub_category->ErrorMessage;

		if (!$BetssonCategoryLeague->save() ) {
			throw new Exception(print_r($BetssonCategoryLeague->errors, true));
		}

		return $BetssonCategoryLeague;
	}

	/**
	 * @param $sub_category_id
	 * @param bool|false $reload
	 *
	 * @return bool
	 */
	protected function GetActiveEventsForSubCategory($sub_category_id, $reload = false) {

		// Get data from API
		$result = $this->_client->GetActiveEventsForSubCategory(['subCategoryId' => $sub_category_id]);

		// Check if data is available
		if (!isset($result->GetActiveEventsForSubCategoryResult->Event)) {
			return false;
		}

		$events = $result->GetActiveEventsForSubCategoryResult->Event;

		// Check if data is an array
		if (!is_array($events)) {
			$events = [$events];
		}

		// Parse the data
		foreach($events as $key => $event ) {

			// Store the data
			if ( is_object( $event ) ) {
				if ( ! isset( $event->EventID ) ) {
					continue;
				}
				$BetssonLeagueEvent = $this->storeEvent( $event, $sub_category_id );

				// Get the markets for event
				$this->GetActiveMarketsForEvent( $event->EventID );
			} else {
				continue;
			}
		}

		return true;
	}

	protected function storeEvent($event, $league_id) {
		// Load or store events
		if ( ! $BetssonLeagueEvent = BetssonLeagueEvent::findOne([
			'EventID' => intval($event->EventID),
			'LanguageCode' => $event->LanguageCode,
		])
		) {
			$BetssonLeagueEvent = new BetssonLeagueEvent();
			$BetssonLeagueEvent->EventID = intval($event->EventID);
			$BetssonLeagueEvent->LanguageCode = $event->LanguageCode;
		}
		$BetssonLeagueEvent->LeagueID = $league_id;
		$BetssonLeagueEvent->EventName = $event->EventName;
		$BetssonLeagueEvent->EventURL = $event->EventURL;
		$BetssonLeagueEvent->EventDeadline = gmdate("Y-m-d H:i:s", strtotime($event->EventDeadline));
		$BetssonLeagueEvent->CacheDate = gmdate("Y-m-d H:i:s", strtotime($event->CacheDate));
		$BetssonLeagueEvent->CacheExpireDate = gmdate("Y-m-d H:i:s", strtotime($event->CacheExpireDate));
		$BetssonLeagueEvent->ErrorMessage = $event->ErrorMessage;

		if (!$BetssonLeagueEvent->save() ) {
			throw new Exception(print_r($BetssonLeagueEvent->errors, true));
		}

		return $BetssonLeagueEvent;
	}

	protected function GetActiveMarketsForEvent($event_id) {

		// Get data from API
		$result = $this->_client->GetActiveMarketsForEvent(['eventId' => $event_id]);

		// Check if data is available
		if (!isset($result->GetActiveMarketsForEventResult->Market)) {
			return false;
		}

		$markets = $result->GetActiveMarketsForEventResult->Market;

		if (!is_array($markets)) {
			// Process single market
			$markets = [$markets];
		}

		// Process array of markets
		foreach($markets as $key => $market) {
			// Store the data
			if (is_object($market)) {
				if (!isset($market->MarketID)) {
					return false;
				}

				$BetssonEventMarket = $this->storeMarket($market, $event_id);
			} else {
				return false;
			}
		}

		return true;
	}


	protected function storeMarket($market, $event_id) {

		if (!isset($market->MarketID)) {
			return false;
		}

		// Load or store events
		if ( ! $BetssonEventMarket = BetssonEventMarket::findOne([
			'MarketID' => intval($market->MarketID),
			'LanguageCode' => $market->LanguageCode,
		])
		) {
			$BetssonEventMarket = new BetssonEventMarket();
			$BetssonEventMarket->MarketID = intval($market->MarketID);
			$BetssonEventMarket->LanguageCode = $market->LanguageCode;
		}
		$BetssonEventMarket->EventID = $event_id;
		$BetssonEventMarket->BetGroupUnitID = intval($market->BetGroupUnitID);
		$BetssonEventMarket->BetGroupUnitName = $market->BetGroupUnitName;
		$BetssonEventMarket->BetGroupID = intval($market->BetgroupID);
		$BetssonEventMarket->BetGroupName = $market->BetgroupName;
		$BetssonEventMarket->BetGroupStyleID = intval($market->BetgroupStyleID);
		$BetssonEventMarket->BetGroupTypeID = intval($market->BetgroupTypeID);
		$BetssonEventMarket->IsLive = boolval($market->IsLive);
		$BetssonEventMarket->MarketDeadline = gmdate("Y-m-d H:i:s", strtotime($market->MarketDeadline));
		$BetssonEventMarket->MarketEndDate = gmdate("Y-m-d H:i:s", strtotime($market->MarketEndDate));
		$BetssonEventMarket->MarketPublishDate = gmdate("Y-m-d H:i:s", strtotime($market->MarketPublishDate));
		$BetssonEventMarket->MarketStartDate = gmdate("Y-m-d H:i:s", strtotime($market->MarketStartDate));
		$BetssonEventMarket->MarketStatusID = intval($market->MarketStatusID);
		$BetssonEventMarket->MarketStatusName = $market->MarketStatusName;
		$BetssonEventMarket->MarketURL = $market->MarketURL;
		$BetssonEventMarket->StartingPitchers = $market->StartingPitchers;
		$BetssonEventMarket->SubParticipantName = $market->SubParticipantName;

		$BetssonEventMarket->CacheDate = gmdate("Y-m-d H:i:s", strtotime($market->CacheDate));
		$BetssonEventMarket->CacheExpireDate = gmdate("Y-m-d H:i:s", strtotime($market->CacheExpireDate));
		$BetssonEventMarket->ErrorMessage = $market->ErrorMessage;

		if (!$BetssonEventMarket->save() ) {
			throw new Exception(print_r($BetssonEventMarket->errors, true));
		}

		// Check if data is available
		if ( !isset($market->MarketSelections->MarketSelection)) {
			return;
		}

		$selections = $market->MarketSelections->MarketSelection;

		if (!is_array($selections)) {
			$selections = [$selections];
		}

		// Process the selections for market
		foreach($selections as $key => $selection) {
			// Store the data
			if ( is_object( $selection ) ) {
				$BetssonEventMarket = $this->storeSelection( $selection, $market->MarketID );
			} else {
				continue;
			}
		}

		return $BetssonEventMarket;
	}

	protected function storeSelection($selection, $market_id) {
		// Load or store selection
		if ( ! $BetssonMarketSelection = BetssonMarketSelection::findOne([
			'SelectionID' => intval($selection->SelectionID),
			'LanguageCode' => $selection->LanguageCode,
		])
		) {
			$BetssonMarketSelection = new BetssonMarketSelection();
			$BetssonMarketSelection->SelectionID = intval($selection->SelectionID);
			$BetssonMarketSelection->LanguageCode = $selection->LanguageCode;
		}
		$BetssonMarketSelection->MarketID = $market_id;
		$BetssonMarketSelection->Odds = doubleval($selection->Odds);
		$BetssonMarketSelection->SelectionLimitValue = doubleval($selection->SelectionLimitValue);
		$BetssonMarketSelection->SelectionName = $selection->SelectionName;
		$BetssonMarketSelection->SelectionStatus = intval($selection->SelectionStatus);
		$BetssonMarketSelection->SelectionStatusName = $selection->SelectionStatusName;
		$BetssonMarketSelection->SelectionSortOrder = intval($selection->SortOrder);


		$BetssonMarketSelection->CacheDate = gmdate("Y-m-d H:i:s", strtotime($selection->CacheDate));
		$BetssonMarketSelection->CacheExpireDate = gmdate("Y-m-d H:i:s", strtotime($selection->CacheExpireDate));
		//$BetssonMarketSelection->ErrorMessage = $selection->ErrorMessage;

		if (!$BetssonMarketSelection->save() ) {
			throw new Exception(print_r($BetssonMarketSelection->errors, true));
		}

		return $BetssonMarketSelection;
	}
}