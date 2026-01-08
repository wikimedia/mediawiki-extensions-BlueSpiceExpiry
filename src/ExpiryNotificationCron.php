<?php

namespace BlueSpice\Expiry;

use BlueSpice\Expiry\Process\LogExpirations;
use BlueSpice\Expiry\Process\SendNotification\Daily;
use BlueSpice\Expiry\Process\SendNotification\Weekly;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\WikiCron\WikiCronManager;

class ExpiryNotificationCron {

	/**
	 * @return void
	 */
	public static function register(): void {
		if ( defined( 'MW_PHPUNIT_TEST' ) || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}

		/** @var WikiCronManager $cronManager */
		$cronManager = MediaWikiServices::getInstance()->getService( 'MWStake.WikiCronManager' );

		// Interval: Daily at 01:00
		$cronManager->registerCron( 'bs-expiry-send-daily', '0 1 * * *', new ManagedProcess( [
			'send-daily' => [
				'class' => Daily::class,
				'services' => [
					'MWStake.Notifier',
					'BSExpiryFactory',
					'MainConfig',
				],
			]
		] ) );

		// Interval: Weekly on Sunday at 01:00
		$cronManager->registerCron( 'bs-expiry-send-weekly', '0 1 * * 0', new ManagedProcess( [
			'send-weekly' => [
				'class' => Weekly::class,
				'services' => [
					'MWStake.Notifier',
					'BSExpiryFactory',
					'MainConfig',
				],
			]
		] ) );

		// Interval: Daily at 01:00
		$cronManager->registerCron( 'bs-expiry-log-expirations', '0 1 * * *', new ManagedProcess( [
			'log-expirations' => [
				'class' => LogExpirations::class,
				'services' => [
					'BSExpirySpecialLogLogger',
					'TitleFactory',
					'BSUtilityFactory',
					'WikiPageFactory',
					'DBLoadBalancer',
				],
			]
		] ) );
	}
}
