<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\Expiry\Panel\Flyout;
use BlueSpice\Expiry\Extension;

class AddExpiredInfo extends SkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		$title = $this->skin->getTitle();
		if ( $title instanceof \Title == false || $title->exists() === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->mergeSkinDataArray(
			SkinData::PAGE_DOCUMENTS_PANEL,
			[
				'expiry' => [
					'position' => 30,
					'callback' => function( $sktemplate ) {
						$currentPageId = $sktemplate->getSkin()->getTitle()->getArticleId();
						//TODO: Caching? But when to invalidate?
						$dbr = wfGetDB( DB_REPLICA );
						$row = $dbr->selectRow(
							'bs_expiry',
							'exp_date',
							[ 'exp_page_id' => $currentPageId ],
							__METHOD__,
							[ 'ORDER BY' => 'exp_date DESC' ]
						);

						$expiryTS = '';
						if( $row !== false ) {
							$expiryTS = wfTimestamp( TS_MW, strtotime( $row-> exp_date ) );
						}

						return new Flyout( $sktemplate, $expiryTS );
					}
				]
			]
		);

		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'expiryCreate' );
	}
}
