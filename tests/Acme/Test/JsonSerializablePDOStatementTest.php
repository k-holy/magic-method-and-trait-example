<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\Domain\Data\ImmutableUser;
use Acme\Domain\Data\MutableUser;

/**
 * Test for BaseTrait with JsonSerializableTrait
 *
 * @author k.holy74@gmail.com
 */
class JsonSerializablePDOStatementTest extends \PHPUnit_Framework_TestCase
{

	private function createRecord(\DateTime $now)
	{
		$pdo = new \PDO('sqlite::memory:', null, null, [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_STATEMENT_CLASS => ['\Acme\JsonSerializablePDOStatement'],
		]);

		$pdo->exec('DROP TABLE IF EXISTS users;');
		$pdo->exec(<<<'SQL'
CREATE TABLE users(
  user_id    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT 
, user_name  TEXT    NOT NULL
, created_at INTEGER NOT NULL
);
SQL
		);

		$pdo->beginTransaction();
		$statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
		$statement->execute([':user_name' => 'test1', ':created_at' => $now->getTimestamp()]);
		$statement->execute([':user_name' => 'test2', ':created_at' => $now->getTimestamp()]);
		$pdo->commit();

		return $pdo;
	}

	public function testJsonSerializeByFetchAssoc()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]['user_id']);
		$this->assertEquals('test1', $records[0]['user_name']);
		$this->assertEquals($now->getTimestamp(), $records[0]['created_at']);

		$this->assertEquals('2', $records[1]['user_id']);
		$this->assertEquals('test2', $records[1]['user_name']);
		$this->assertEquals($now->getTimestamp(), $records[1]['created_at']);
	}

	public function testJsonSerializeByFetchObject()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchMode(\PDO::FETCH_OBJ);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->userId);
		$this->assertEquals('test1', $records[0]->userName);
		$this->assertEquals($now->getTimestamp(), $records[0]->createdAt);

		$this->assertEquals('2', $records[1]->userId);
		$this->assertEquals('test2', $records[1]->userName);
		$this->assertEquals($now->getTimestamp(), $records[1]->createdAt);
	}

	public function testJsonSerializeByFetchClass()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchMode(\PDO::FETCH_CLASS, '\Acme\Domain\Data\ImmutableUser', [null, $timezone, \DateTime::RFC3339]);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->userId);
		$this->assertEquals('test1', $records[0]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

		$this->assertEquals('2', $records[1]->userId);
		$this->assertEquals('test2', $records[1]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
	}

	public function testJsonSerializeByFetchInto()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchMode(\PDO::FETCH_INTO, new MutableUser(null, $timezone, \DateTime::RFC3339));
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->userId);
		$this->assertEquals('test1', $records[0]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

		$this->assertEquals('2', $records[1]->userId);
		$this->assertEquals('test2', $records[1]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testJsonSerializeByFetchIntoRaiseLogicExceptionWhenObjectIsImmutable()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchMode(\PDO::FETCH_INTO, new ImmutableUser(null, $timezone, \DateTime::RFC3339));
		$records = $statement->jsonSerialize();
	}

}
