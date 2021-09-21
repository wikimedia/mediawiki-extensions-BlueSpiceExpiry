<?php

namespace BlueSpice\Expiry\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Expiry\GlobalActionsTool;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements
	MWStakeCommonUIRegisterSkinSlotComponents,
	BlueSpiceDiscoveryTemplateDataProviderAfterInit
{

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-bluespice-expiry' => [
					'factory' => static function () {
						return new GlobalActionsTool();
					}
				]
			]
		);
	}

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->unregister( 'toolbox', 'ca-expiryCreate' );
		$registry->register( 'actions_secondary', 'ca-expiryCreate' );
	}
}
