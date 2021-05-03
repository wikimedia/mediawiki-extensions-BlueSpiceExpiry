<?php

use MediaWiki\MediaWikiServices;

return [

	'BSExpiryFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Expiry\Factory;
	},

];
