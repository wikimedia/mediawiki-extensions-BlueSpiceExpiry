<?php

namespace BlueSpice\Expiry\Tests;

use BlueSpice\Tests\BSApiTasksTestBase;
use MediaWiki\Title\Title;

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExpiry
 */
class ApiExpiryTasksTest extends BSApiTasksTestBase {

	protected function getModuleName() {
		return 'bs-expiry-tasks';
	}

	public function setUp(): void {
		parent::setUp();
		$this->insertPage( 'Dummy' );
	}

	/**
	 * @covers \ApiExpiryTasks::task_saveExpiry
	 */
	public function testSaveExpiry() {
		$title = Title::newFromText( 'Dummy' );
		$articleId = $title->getArticleID();
		$nextWeek = strtotime( '+7 days', strtotime( 'midnight', time() ) );

		// Create new expiry
		$response = $this->executeTask(
			'saveExpiry',
			[
				'articleId' => $articleId,
				'date' => $nextWeek,
				'comment' => 'Test expiry'
			]
		);

		$this->assertTrue( $response->success, 'SaveExpiry (create) task failed' );
		$this->assertSelect(
			'bs_expiry',
			[ 'exp_date', 'exp_comment' ],
			[ 'exp_page_id' => $articleId ],
			[
				[ date( 'Y-m-d H:i:s', $nextWeek ), 'Test expiry' ]
			]
		);

		// Update expiry
		$lastWeek = strtotime( 'midnight -7 days' );
		$expiryId = $this->getExpiryFromArticleID( $articleId );

		$this->assertGreaterThan( 0, $expiryId, 'Failed to retrieve expiry from DB' );

		$response = $this->executeTask(
			'saveExpiry',
			[
				'articleId' => $articleId,
				'date' => $lastWeek,
				'comment' => 'Updated expiry',
				'id' => $expiryId
			]
		);

		$this->assertTrue( $response->success, 'SaveExpiry (update) task failed' );
		$this->assertSelect(
			'bs_expiry',
			[ 'exp_date', 'exp_comment' ],
			[ 'exp_page_id' => $articleId ],
			[
				[ date( 'Y-m-d H:i:s', $lastWeek ), 'Updated expiry' ]
			]
		);
	}

	/**
	 * @covers \ApiExpiryTasks::task_getDetailsForExpiry
	 */
	public function testGetDetailsForExpiry() {
		$title = Title::newFromText( 'Dummy' );
		$lastWeek = strtotime( 'midnight -7 days' );
		$this->insertExpiryIntoDb( $title, 'Expiry to delete', $lastWeek );

		$response = $this->executeTask(
			'getDetailsForExpiry',
			[ 'articleId' => $title->getArticleID() ]
		);

		$this->assertTrue( $response->success, 'GetDetailsForExpiry task failed' );
		$payload = $response->payload;

		$this->assertEquals(
			date( 'Y-m-d H:i:s', $lastWeek ),
			$payload['date'],
			'Returned expiry has unexpected date'
		);
	}

	/**
	 * @covers \ApiExpiryTasks::task_deleteExpiry
	 */
	public function testDeleteExpiry() {
		$title = Title::newFromText( 'Dummy' );
		$articleId = $title->getArticleID();
		$lastWeek = strtotime( 'midnight -7 days' );
		$expiryId = $this->insertExpiryIntoDb( $title, 'Expiry to delete', $lastWeek );

		$response = $this->executeTask(
			'deleteExpiry',
			[
				'articleId' => $articleId,
				'expiryId' => $expiryId
			]
		);

		$this->assertTrue( $response->success, 'DeleteExpiry task failed' );
		$this->assertSame(
			0,
			$this->getExpiryFromArticleID( $articleId ),
			'Expiry still exists after deletion'
		);
	}

	/**
	 * Get the expiry ID for a given article.
	 *
	 * @param int $articleId
	 * @return int Expiry ID or 0 if none
	 */
	protected function getExpiryFromArticleID( int $articleId ): int {
		$res = $this->getDb()->select(
			'bs_expiry',
			'exp_id',
			[
				'exp_page_id' => $articleId
			],
			__METHOD__
		);

		if ( !$res || !$res->numRows() ) {
			return 0;
		}

		$row = $res->fetchRow();
		return (int)$row['exp_id'];
	}

	/**
	 * Inserts a row into the bs_expiry table for the given article.
	 *
	 * @param Title $title
	 * @param string $comment
	 * @param int $timestamp
	 * @return int The ID of the inserted row
	 */
	protected function insertExpiryIntoDb( Title $title, string $comment, int $timestamp ): int {
		$db = $this->getDb();

		$row = [
			'exp_page_id' => $title->getArticleID(),
			'exp_date' => $db->timestamp( $timestamp ),
			'exp_comment' => $comment
		];

		$db->insert( 'bs_expiry', $row, __METHOD__ );

		return (int)$db->insertId();
	}

}
