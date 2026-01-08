<?php

namespace BlueSpice\Expiry\Process\SendNotification;

use BlueSpice\Expiry\Data\Record;
use BlueSpice\Expiry\Event\ExpiryToday;
use BlueSpice\Expiry\Process\SendNotification;
use DateInterval;
use DateInvalidTimeZoneException;
use DateTime;
use DateTimeZone;
use Exception;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\Events\Notifier;

class Daily extends SendNotification {

	/**
	 * @param Title $title
	 * @param Record $record
	 * @param Notifier $notifier
	 *
	 * @throws Exception
	 */
	protected function sendNotifications( Title $title, Record $record, Notifier $notifier ): void {
		$comment = $record->get( Record::COMMENT, '' );
		$event = new ExpiryToday( $title, $comment );
		$notifier->emit( $event );
	}

	/**
	 * @return Title[]
	 * @throws DateInvalidTimeZoneException
	 * @throws Exception
	 */
	protected function getExpiredTitles(): array {
		$from = new DateTime( 'now', new DateTimeZone( $this->localTimeZone ) );
		$from->setTime( 0, 0, 0 );
		$to = clone $from;

		$from->add( DateInterval::createFromDateString( '-1 day' ) );
		$to->add( DateInterval::createFromDateString( '1 day' ) );

		return $this->factory->getExpiredTitles( $from, $to );
	}
}
