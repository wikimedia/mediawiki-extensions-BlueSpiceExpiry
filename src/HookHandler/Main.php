<?php

namespace BlueSpice\Expiry\HookHandler;

use BlueSpice\Expiry\GlobalActionsTool;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class Main implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-bluespice-expiry' => [
					'factory' => function () {
						return new GlobalActionsTool();
					}
				]
			]
		);
	}
}
