<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\PDOStatement;
use Acme\Domain\Data\ImmutableUser;
use Acme\Domain\Data\MutableUser;

/**
 * Test for PDOStatement
 *
 * @author k.holy74@gmail.com
 */
class PDOStatementTest extends \PHPUnit_Framework_TestCase
{

	private function createRecord(\DateTime $now)
	{
		$pdo = new \PDO('sqlite::memory:', null, null, [
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

		$pdo->beginTransaction();
		$statement = new PDOStatement($pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)"));
		$statement->execute([':user_name' => 'test1', ':created_at' => $now->getTimestamp()]);
		$statement->execute([':user_name' => 'test2', ':created_at' => $now->getTimestamp()]);
		$pdo->commit();

		return $pdo;
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testExecuteRaiseExceptionWhenPDOExceptionIsThrown()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)"));
		$statement->execute([':user_id' => '1']);
	}

	public function testFetchAssoc()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :user_id ORDER BY user_id"));

		$statement->execute(['user_id' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_ASSOC);

		$this->assertEquals('1', $user['user_id']);
		$this->assertEquals('test1', $user['user_name']);
		$this->assertEquals($now->getTimestamp(), $user['created_at']);

		$statement->execute(['user_id' => '2']);
		$user = $statement->fetch(PDOStatement::FETCH_ASSOC);
		$this->assertEquals('2', $user['user_id']);
		$this->assertEquals('test2', $user['user_name']);
		$this->assertEquals($now->getTimestamp(), $user['created_at']);
	}

	public function testFetchNum()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :user_id ORDER BY user_id"));

		$statement->execute(['user_id' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_NUM);

		$this->assertEquals('1', $user[0]);
		$this->assertEquals('test1', $user[1]);
		$this->assertEquals($now->getTimestamp(), $user[2]);

		$statement->execute(['user_id' => '2']);
		$user = $statement->fetch(PDOStatement::FETCH_NUM);
		$this->assertEquals('2', $user[0]);
		$this->assertEquals('test2', $user[1]);
		$this->assertEquals($now->getTimestamp(), $user[2]);
	}

	public function testFetchInto()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		// PDO::FETCH_INTO でのオブジェクト生成は、フェッチモード指定時の引数でコンストラクタが呼ばれた後、列と同名のプロパティに値のセットを試みる。
		// プロパティの可視性は有効となり、定義されている場合は __set() が呼ばれる。
		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId ORDER BY user_id"));
		$statement->setFetchMode(PDOStatement::FETCH_INTO, new MutableUser(null, $timezone, 'Y-m-d H:i:s'));

		$statement->execute(['userId' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_INTO);
		$this->assertEquals('1', $user->userId);
		$this->assertEquals('test1', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);

		$statement->execute(['userId' => '2']);
		$user = $statement->fetch(PDOStatement::FETCH_INTO);
		$this->assertEquals('2', $user->userId);
		$this->assertEquals('test2', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testFetchIntoRaiseLogicExceptionWhenObjectIsImmutable()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		// PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId ORDER BY user_id"));
		$statement->setFetchMode(PDOStatement::FETCH_INTO, new ImmutableUser(null, $timezone, \DateTime::RFC3339));

		$statement->execute(['userId' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_INTO);
	}

	public function testFetchClass()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		// PDO::FETCH_CLASS でのオブジェクト生成は、列と同名のプロパティに可視性に関わらず値をセットし、その後でフェッチモード指定時の引数でコンストラクタが呼ばれる。
		// コンストラクタが呼ばれた時点ではすでにプロパティに値がセットされた状態となり、__set() が呼ばれることはない。
		// そのため、__set() での値のバリデーションや変換は機能しない。
		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId ORDER BY user_id"));
		$statement->setFetchMode(PDOStatement::FETCH_CLASS, '\Acme\Domain\Data\ImmutableUser', [null, $timezone, 'Y-m-d H:i:s']);

		$statement->execute(['userId' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_CLASS);
		$this->assertEquals('1', $user->userId);
		$this->assertEquals('test1', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);

		$statement->execute(['userId' => '2']);
		$user = $statement->fetch(PDOStatement::FETCH_CLASS);
		$this->assertEquals('2', $user->userId);
		$this->assertEquals('test2', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);
	}

	public function testFetchClosure()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);
		$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

		// オリジナルのモード FETCH_CLOSURE では、元の PDOStatement に設定されたモードでフェッチした値をクロージャの引数として、クロージャの戻り値を返す。
		// この例では ATTR_DEFAULT_FETCH_MODE として FETCH_ASSOC が設定済みのため、クロージャの引数には連想配列が渡される。
		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :userId ORDER BY user_id"));
		$statement->setFetchMode(PDOStatement::FETCH_CLOSURE, function($item) use ($timezone) {
			$user = new ImmutableUser(
				[
					'userId'    => $item['user_id'],
					'userName'  => $item['user_name'],
					'createdAt' => $item['created_at'],
				],
				$timezone,
				'Y-m-d H:i:s'
			);
			return $user;
		});

		$statement->execute(['userId' => '1']);
		$user = $statement->fetch(PDOStatement::FETCH_CLOSURE);
		$this->assertEquals('1', $user->userId);
		$this->assertEquals('test1', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);

		$statement->execute(['userId' => '2']);
		$user = $statement->fetch(PDOStatement::FETCH_CLOSURE);
		$this->assertEquals('2', $user->userId);
		$this->assertEquals('test2', $user->userName);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAt);
	}

	public function testJsonSerializeByFetchAssoc()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_ASSOC);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]['user_id']);
		$this->assertEquals('test1', $records[0]['user_name']);
		$this->assertEquals($now->getTimestamp(), $records[0]['created_at']);

		$this->assertEquals('2', $records[1]['user_id']);
		$this->assertEquals('test2', $records[1]['user_name']);
		$this->assertEquals($now->getTimestamp(), $records[1]['created_at']);
	}

	public function testJsonSerializeByFetchNum()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_NUM);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0][0]);
		$this->assertEquals('test1', $records[0][1]);
		$this->assertEquals($now->getTimestamp(), $records[0][2]);

		$this->assertEquals('2', $records[1][0]);
		$this->assertEquals('test2', $records[1][1]);
		$this->assertEquals($now->getTimestamp(), $records[1][2]);
	}

	public function testJsonSerializeByFetchClass()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_CLASS, '\Acme\Domain\Data\ImmutableUser', [null, $timezone, \DateTime::RFC3339]);
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

		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_INTO, new MutableUser(null, $timezone, \DateTime::RFC3339));
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

		// PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
		$statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_INTO, new ImmutableUser(null, $timezone, \DateTime::RFC3339));
		$records = $statement->jsonSerialize();
	}

	public function testJsonSerializeByFetchClosure()
	{
		$timezone = new \DateTimeZone('Asia/Tokyo');
		$now = new \DateTime('now', $timezone);

		$pdo = $this->createRecord($now);

		$statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
		$statement->execute();
		$statement->setFetchMode(PDOStatement::FETCH_CLOSURE, function($item) use ($timezone) {
			$user = new ImmutableUser(
				[
					'userId'    => $item['user_id'],
					'userName'  => $item['user_name'],
					'createdAt' => $item['created_at'],
				],
				$timezone,
				\DateTime::RFC3339
			);
			return $user;
		});
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->userId);
		$this->assertEquals('test1', $records[0]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

		$this->assertEquals('2', $records[1]->userId);
		$this->assertEquals('test2', $records[1]->userName);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
	}

}
