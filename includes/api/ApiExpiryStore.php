<?php

class ApiExpiryStore extends BSApiExtJSStoreBase {

	public function __construct($mainModule, $moduleName, $modulePrefix = '') {
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}

	protected function makeData( $sQuery = '' ) {

		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return array();
		}

		$iOffset = 0;
		$iLimit = 25;
		$sSortField = 'exp_date';
		$sSortDirection = 'DESC';
		$iDate = 0;
		
		$aData = array (
			'results' => array (),
			'total' => 0
		);
		if ( BsCore::checkAccessAdmission( 'read' ) === false || $oUser->isAnon() ) {
			return $aData;
		}
		$dbr = wfGetDB( DB_REPLICA );
		$sTblPrfx = $dbr->tablePrefix();

		switch ( $sSortField ) {
			case 'rem_date':
				$sSortField = "{$sTblPrfx}bs_expiry.exp_date";
				break;
			case 'page_title':
				$sSortField = "{$sTblPrfx}page.page_title";
				break;
		}

		$aTables = array(
			'bs_expiry', 'page'
		);
		$aFields = array(
			"{$sTblPrfx}bs_expiry.exp_id",
			"{$sTblPrfx}bs_expiry.exp_page_id",
			"{$sTblPrfx}bs_expiry.exp_date",
			"{$sTblPrfx}page.page_title",
			"{$sTblPrfx}bs_expiry.exp_comment",
		);
		$aConditions = array();
		$aOptions = array(
			'ORDER BY' => "{$sSortField} {$sSortDirection}",
			'GROUP BY' => "{$sTblPrfx}bs_expiry.exp_id"
		);

		if ( is_null( $iOffset ) ) {
			$aOptions['OFFSET'] = $iOffset;
		}

		if ( is_null( $iLimit ) ) {
			$aOptions['LIMIT'] = $iLimit;
		}

		$aJoinConditions = array(
			"page" => array( 'JOIN', "{$sTblPrfx}bs_expiry.exp_page_id = {$sTblPrfx}page.page_id" )
		);

		// give other extensions the opportunity to modify the query
		Hooks::run(
			'BsExpiryBeforeBuildOverviewQuery',
			array(
				$this,
				&$aTables,
				&$aFields,
				&$aConditions,
				&$aOptions,
				&$aJoinConditions,
				&$sSortField,
				&$sSortDirection
			)
		);

/*
		$iUserId = $oUser->getId();
		if ( $iUserId && !$oUser->getOption( "MW::Reminder::ShowAllReminders" ) ) {
			$aConditions["{$sTblPrfx}bs_reminder.rem_user_id"] = $iUserId;
		}
*/
		if ( $iDate !== 0 ) {
			$aConditions[] = "{$sTblPrfx}bs_expiry.exp_date <= '" . $iDate . "'";
		}

		$res = $dbr->select(
			$aTables, $aFields, $aConditions, __METHOD__, $aOptions, $aJoinConditions
		);

		if ( $res ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->exp_page_id );
				$aResultSet = array(
					'id' => $row->exp_id,
					'page_title' => $oTitle->getPrefixedText(),
					'page_link' => $oTitle->getLocalURL(),
					'expiry_date' => $row->exp_date,
					'article_id' => $row->exp_page_id,
					'exp_comment' => $row->exp_comment
				);
				Hooks::run( 'BsExpiryBuildOverviewResultSet', array( $this, &$aResultSet, $row ) );
				$aData['results'][] = $aResultSet;
			}
		}

		unset( $aOptions['LIMIT'], $aOptions['OFFSET'] );
		$res = $dbr->select(
			$aTables,
			"COUNT({$sTblPrfx}bs_expiry.exp_id) AS total",
			$aConditions,
			__METHOD__,
			array (),
			$aJoinConditions
		);
		if ( $res ) {
			$row = $res->fetchRow();
			$aData['total'] = $row['total'];
		}

		$aReminders = $aData;

		$aOutput = array();

		foreach ( $aReminders['results'] as $aReminder ) {
			$oReminder = (object) $aReminder;
			$aOutput[] = $oReminder;
		}

		return $aOutput;
	}

	public function isReadMode() {
		return true;
	}

	protected function makeMetaData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return array();
		}

		$aMetadata = array(
			'idProperty' => 'id',
			'root' => 'results',
			'totalProperty' => 'total',
			'successProperty' => 'success',
			'fields' => array(
				array( 'name' => 'page_title' ),
				array( 'name' => 'page_link' ),
				array ( 'name' => 'exp_date' ),
				array ( 'name' => 'article_id' ),
				array ( 'name' => 'exp_comment' )
			),
			'sortInfo' => array(
				'field' => 'exp_date',
				'direction' => 'DESC'
			)
		);

		$aMetadata['columns'][] = array (
			'header' => wfMessage( 'bs-expiry-header-pagename' )->plain(),
			'dataIndex' => 'page_title',
			'render' => 'page',
			'sortable' => true
		);
		$aMetadata['columns'][] = array (
			'header' => wfMessage( 'bs-expiry-header-date' )->plain(),
			'dataIndex' => 'exp_date',
			'render' => 'date',
			'sortable' => true
		);

		\Hooks::run( 'BsExpiryBuildOverviewMetadata', [ &$aMetadata ] );

		return $aMetadata;
	}

	protected function getDescription() {
		return 'ExtJS store backend for Expiry';
	}

	public function getParamDescription() {
		$aDesc = parent::getParamDescription();
		//TODO: Add 'user' field to allow fechting for different users
		return $aDesc;
	}

}