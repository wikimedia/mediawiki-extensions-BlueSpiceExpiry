<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateNavigation;

use BlueSpice\Hook\SkinTemplateNavigation;

class AddExpiryCreateEntry extends SkinTemplateNavigation {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->sktemplate->getUser()->isLoggedIn() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->exists()
			|| $this->sktemplate->getTitle()->isSpecialPage() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'read' ), $this->sktemplate->getUser() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'expirearticle' ), $this->sktemplate->getUser() ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->links['actions']['expiryCreate'] = [
			"class" => '',
			"text" => $this->msg( 'bs-expiry-menu-entry-create' )->plain(),
			"href" => "#",
			"bs-group" => "hidden"
		];
		return true;
	}

}
