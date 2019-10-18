<?php

class ExpiryHooks {
	public static function onSkinTemplateNavigation( $oSkinTemplate, &$links ) {
		$oUser = RequestContext::getMain()->getUser();
		$oTitle = RequestContext::getMain()->getTitle();

		if ( !$oUser->isLoggedIn() ) {
			return true;
		}
		if ( $oTitle->exists() === false || $oTitle->isSpecialPage() ) {
			return true;
		}
		if ( !$oUser->isAllowed( 'expirearticle' ) ) {
			return true;
		}
		if ( !$oTitle->userCan( 'read' ) ) {
			return true;
		}

		$links['actions']['expiryCreate'] = array(
			"class" => '',
			"text" => wfMessage( 'bs-expiry-menu-entry-create' )->plain(),
			"href" => "#",
			"bs-group" => "hidden"
		);

		return true;
	}

	public static function onQueryPages( &$wgQueryPages ) {
		$wgQueryPages[] = array( 'SpecialExpiry', 'Expired_Articles' );
		return true;
	}

	/**
	* Hook handler for UnitTestList
	*
	* @param array $paths
	* @return boolean
	*/
	public static function onUnitTestsList( &$paths ) {
		$paths[] = __DIR__ . '/../tests/phpunit/';
		return true;
	}
}