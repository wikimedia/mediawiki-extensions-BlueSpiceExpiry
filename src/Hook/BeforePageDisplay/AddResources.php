<?php

namespace BlueSpice\Expiry\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.Expiry' );
		$this->out->addModules( 'ext.bluespice.expiry.pageinfo.flyout' );
		$this->out->addModuleStyles( 'ext.bluespice.Expiry.Highlight' );
	}

}
