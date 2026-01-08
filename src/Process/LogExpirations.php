<?php

namespace BlueSpice\Expiry\Process;

use BlueSpice\Expiry\SpecialLogLogger;
use BlueSpice\UtilityFactory;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWException;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Wikimedia\Rdbms\ILoadBalancer;

class LogExpirations implements IProcessStep {

	/**
	 * @param SpecialLogLogger $logger
	 * @param TitleFactory $titleFactory
	 * @param UtilityFactory $utilityFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param ILoadBalancer $loadBalancer
	 */
	public function __construct(
		private readonly SpecialLogLogger $logger,
		private readonly TitleFactory $titleFactory,
		private readonly UtilityFactory $utilityFactory,
		private readonly WikiPageFactory $wikiPageFactory,
		private readonly ILoadBalancer $loadBalancer
	) {
	}

	/**
	 * @inheritDoc
	 *
	 * @throws MWException
	 */
	public function execute( $data = [] ): array {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$expirations = $db->select(
			[
				'p' => 'page',
				'exp' => 'bs_expiry'
			],
			[
				'p.page_title',
				'p.page_namespace',
				'exp.exp_comment as comment'
			],
			[ 'exp_date = CURDATE()' ],
			__METHOD__,
			[], [
				'p' => [
					'INNER JOIN',
					'page_id = exp_page_id'
				]
			]
		);

		foreach ( $expirations as $expiration ) {
			$title = $this->titleFactory->newFromRow( $expiration );
			$user = $this->utilityFactory->getMaintenanceUser()->getUser();
			$this->updateCache( $title, $user );
			$this->logger->log(
				$user,
				$title,
				SpecialLogLogger::LOG_ACTION_EXPIRED,
				$expiration->comment || ''
			);
		}

		return [];
	}

	/**
	 * @param Title $title
	 * @param User $user
	 *
	 * @return void
	 */
	private function updateCache( Title $title, UserIdentity $user ): void {
		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$wikiPage->doSecondaryDataUpdates( [
			'triggeringUser' => $user,
			'defer' => DeferredUpdates::POSTSEND
		] );
		$title->invalidateCache();
	}
}
