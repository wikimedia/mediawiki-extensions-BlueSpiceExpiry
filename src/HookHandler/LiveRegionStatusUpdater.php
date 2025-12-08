<?php

namespace BlueSpice\Expiry\Hookhandler;

use BlueSpice\Expiry\Extension;
use MediaWiki\Output\Hook\BeforePageDisplayHook;

class LiveRegionStatusUpdater implements BeforePageDisplayHook {

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$title = $out->getTitle();
		if ( !$title ) {
			return;
		}

		$expiry = Extension::getExpiryForPage( $title->getArticleID() );
		if ( !$expiry ) {
			return;
		}

		$out->addElement( 'div', [ 'id' => 'bs-expiry-status-cnt', 'role' => 'status' ] );
		$out->addModules( 'ext.bluespice.expiry.liveRegionStatusUpdater' );
	}
}
