<?php

use BlueSpice\Expiry\SpecialLogLogger;
use MediaWiki\MediaWikiServices;

return [

	'BSExpiryFactory' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Expiry\Factory;
	},

	'BSExpirySpecialLogLogger' => function ( MediaWikiServices $services ) {
		return new SpecialLogLogger();
	},
];
