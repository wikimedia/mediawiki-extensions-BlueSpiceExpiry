<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateNavigationUniversal;

use BlueSpice\Expiry\Extension;
use BlueSpice\Hook\SkinTemplateNavigationUniversal;

class AddExpiryCreateEntry extends SkinTemplateNavigationUniversal {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->sktemplate->getTitle();

		if ( !$this->sktemplate->getUser()->isRegistered() ) {
			return true;
		}
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		$isAllowed = $this->getServices()->getPermissionManager()->userCan(
			'expirearticle',
			$this->sktemplate->getUser(),
			$title
		);
		if ( !$isAllowed ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$label = $this->msg( 'bs-expiry-menu-entry-create' )->plain();
		$expiry = Extension::getExpiryForPage( $this->sktemplate->getTitle()->getArticleID(), false );

		if ( $expiry ) {
			$label = $this->msg( 'bs-expiry-menu-entry-manage' )->text();
		}
		$this->links['actions']['expiryCreate'] = [
			"class" => '',
			"text" => $label,
			"href" => "#",
			"bs-group" => "hidden"
		];
		return true;
	}

}
