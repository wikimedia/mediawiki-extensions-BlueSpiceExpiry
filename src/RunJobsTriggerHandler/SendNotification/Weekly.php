<?php

namespace BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Event\ExpiryInOneWeek;
use BlueSpice\Expiry\RunJobsTriggerHandler\SendNotification;
use BlueSpice\RunJobsTriggerHandler\Interval\OnceAWeek;
use DateInterval;
use DateTime;
use DateTimeZone;
use MWStake\MediaWiki\Component\Events\Notifier;
use Title;

class Weekly extends SendNotification {

	/**
	 *
	 * @return OnceAWeek
	 */
	public function getInterval() {
		return new OnceAWeek();
	}

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param Notifier $notifier
	 * @throws \Exception
	 */
	protected function sendNotifications( Title $title, Record $record, Notifier $notifier ) {
		$comment = $record->get( Record::COMMENT, '' );
		$event = new ExpiryInOneWeek( $title, $comment );
		$notifier->emit( $event );
	}

	/**
	 * @return Title[]
	 */
	protected function getExpiredTitles() {
		$from = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$to = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$from->add( DateInterval::createFromDateString( '1 week' ) );
		$to->add( DateInterval::createFromDateString( '-1 day' ) );

		return $this->factory->getExpiredTitles( $from, $to );
	}
}
