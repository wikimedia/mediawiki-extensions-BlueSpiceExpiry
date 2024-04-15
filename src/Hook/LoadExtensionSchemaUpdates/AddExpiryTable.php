<?php

namespace BlueSpice\Expiry\Hook\LoadExtensionSchemaUpdates;

class AddExpiryTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dbType = $this->updater->getDB()->getType();
		$dir = dirname( __DIR__, 3 );

		$this->updater->addExtensionTable(
			'bs_expiry',
			"$dir/maintenance/db/sql/$dbType/bs_expiry-generated.sql"
		);
	}
}
