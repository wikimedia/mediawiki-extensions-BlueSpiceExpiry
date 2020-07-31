<?php

namespace BlueSpice\Expiry\Hook\SkinTemplateNavigation;

use BlueSpice\Hook\SkinTemplateNavigation;

class AddExpiryCreateEntry extends SkinTemplateNavigation {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$user = $this->sktemplate->getUser();
		$title = $this->sktemplate->getTitle();

		if ( !$user->isLoggedIn() ) {
			return true;
		}
		if ( !$title->exists() || $title->isSpecialPage() ) {
			return true;
		}
		$isAllowed = $this->getServices()->getPermissionManager()->userCan(
			'expirearticle',
			$user,
			$title
		);
		if ( !$isAllowed ) {
			return true;
		}
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $user, $title )
		) {
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
