<?php

namespace BlueSpice\Expiry\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class Expiry extends SpecialPage {

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct( 'Expiry', 'edit' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$out->addModules( [ 'ext.bluespice.expiry.specialExpiry' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-expiry-special-expiry-container' ] ) );
	}
}
