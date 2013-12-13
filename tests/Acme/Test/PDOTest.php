<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\PDO;
use Acme\Test\PDOTestDataMutable;

/**
 * Test for PDO
 *
 * @author k.holy74@gmail.com
 */
class PDOTest extends \PHPUnit_Framework_TestCase
{

	private function createTable()
	{
		$pdo = new PDO('sqlite::memory:', null, null, [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
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

		return $pdo;
	}

	public function testPrepare()
	{
		$pdo = $this->createTable();
		$statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
		$this->assertInstanceOf('\Acme\PDOStatement', $statement);
	}

	public function testPrepareWithOption()
	{
		$pdo = $this->createTable();
		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id", [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
		$this->assertInstanceOf('\Acme\PDOStatement', $statement);
	}

	public function testQuery()
	{
		$pdo = $this->createTable();
		$statement = $pdo->query("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$this->assertInstanceOf('\Acme\PDOStatement', $statement);
	}

	public function testQueryWithSetFetchModeToFetchInto()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createTable();

		$pdo->beginTransaction();
		$statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
		$statement->execute([':user_name' => 'test1', ':created_at' => $now->getTimestamp()]);
		$pdo->commit();

		$statement = $pdo->query(
			"SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id",
			\PDO::FETCH_INTO,
			new PDOTestDataMutable(null, $timezone, 'Y-m-d H:i:s')
		);
		$user = $statement->fetch();

		$this->assertInstanceOf('\Acme\Test\PDOTestDataMutable', $user);
	}

	public function testQueryWithSetFetchModeToFetchClass()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createTable();

		$pdo->beginTransaction();
		$statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
		$statement->execute([':user_name' => 'test1', ':created_at' => $now->getTimestamp()]);
		$pdo->commit();

		$statement = $pdo->query(
			"SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id",
			\PDO::FETCH_CLASS,
			'\Acme\Test\PDOTestDataImmutable',
			[null, $timezone, 'Y-m-d H:i:s']
		);
		$user = $statement->fetch();

		$this->assertInstanceOf('\Acme\Test\PDOTestDataImmutable', $user);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testQueryRaiseExceptionWhenInvalidArgumentCount()
	{
		$pdo = $this->createTable();

		$statement = $pdo->query(
			"SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id",
			\PDO::FETCH_CLASS,
			'\Acme\Test\PDOTestDataImmutable',
			[],
			false
		);
	}

}
