<?php

namespace BlueSpice\Expiry\Hook\BeforePageDisplay;

class AddPageWatermark extends \BlueSpice\Hook\BeforePageDisplay {

	protected function skipProcessing() {
		$title = $this->out->getTitle();
		if ( !$title ) {
			return true;
		}
		if ( $title->getArticleID() < 1 ) {
			return true;
		}
		$action = $this->out->getRequest()->getVal( 'action', 'view' );
		if ( $action !== 'view' && $action !== 'submit' ) {
			return true;
		}
		if ( $this->out->getRequest()->getVal( 'printable', false ) === 'yes' ) {
			return true;
		}
		if ( !$this->getConfig()->get( 'ExpiryEnablePageWatermark' ) ) {
			return true;
		}
		$expiry = \BlueSpice\Expiry\Extension::getExpiryForPage(
			$title->getArticleID()
		);
		if ( !$expiry ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.expiry.watermark.styles' );
	}

}
