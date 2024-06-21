<?php

namespace BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Event\ExpiryToday;
use BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;
use DateInterval;
use DateTime;
use DateTimeZone;
use MWStake\MediaWiki\Component\Events\Notifier;
use Title;

class Daily extends SendNotification {

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param Notifier $notifier
	 * @throws \Exception
	 */
	protected function sendNotifications( Title $title, Record $record, Notifier $notifier ) {
		$comment = $record->get( Record::COMMENT, '' );
		$event = new ExpiryToday( $title, $comment );
		$notifier->emit( $event );
	}

	/**
	 * @return Title[]
	 */
	protected function getExpiredTitles() {
		$from = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$to = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$from->add( DateInterval::createFromDateString( '-1 day' ) );
		$to->add( DateInterval::createFromDateString( '1 day' ) );

		return $this->factory->getExpiredTitles( $from, $to );
	}
}
