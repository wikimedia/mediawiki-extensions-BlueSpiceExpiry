<?php

namespace BlueSpice\Expiry\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.Expiry' );
		$this->out->addModules( 'ext.bluespice.expiry.pageinfo.flyout' );
		$this->out->addModuleStyles( 'ext.bluespice.Expiry.Highlight' );

		$title = $this->getContext()->getTitle();
		$canExpire = false;
		$canDeleteExpiration = false;
		if ( $title && $title->exists() && $title->userCan( 'expirearticle' ) ) {
			$canExpire = true;
		}
		if ( $title && $title->exists() && $title->userCan( 'expiry-delete' ) ) {
			$canDeleteExpiration = true;
		}
		$this->out->addJsConfigVars( 'bsgExpiryCanExpire', $canExpire );
		$this->out->addJsConfigVars( 'bsgExpiryCanDeleteExpiration', $canDeleteExpiration );
	}

}
