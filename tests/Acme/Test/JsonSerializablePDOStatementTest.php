<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\User;

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
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		]);

		$pdo->exec('DROP TABLE IF EXISTS users;');
		$pdo->exec(<<<'SQL'
CREATE TABLE users(
  user_id    INTEGER     NOT NULL PRIMARY KEY AUTOINCREMENT 
, user_name  TEXT        NOT NULL
, created_at DATETIME    NOT NULL
);
SQL
		);

		$pdo->beginTransaction();
		$statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, datetime(:created_at))");
		$statement->execute([':user_name' => 'test1', ':created_at' => $now->format('Y-m-d H:i:s')]);
		$statement->execute([':user_name' => 'test2', ':created_at' => $now->format('Y-m-d H:i:s')]);
		$pdo->commit();

		return $pdo;
	}

	public function testJsonSerialize()
	{

		$now = new \DateTime();

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]['user_id']);
		$this->assertEquals('test1', $records[0]['user_name']);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $records[0]['created_at']);

		$this->assertEquals('2', $records[1]['user_id']);
		$this->assertEquals('test2', $records[1]['user_name']);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $records[1]['created_at']);
	}

	public function testJsonSerializeWithFetchStyleForJsonIsFetchObject()
	{
		$now = new \DateTime();

		$pdo = $this->createRecord($now);

		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchStyleForJson([
			\PDO::FETCH_OBJ,
		]);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->user_id);
		$this->assertEquals('test1', $records[0]->user_name);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $records[0]->created_at);

		$this->assertEquals('2', $records[1]->user_id);
		$this->assertEquals('test2', $records[1]->user_name);
		$this->assertEquals($now->format('Y-m-d H:i:s'), $records[1]->created_at);
	}

	public function testJsonSerializeWithFetchStyleForJsonIsFetchIntoUser()
	{
		$now = new \DateTime();

		$pdo = $this->createRecord($now);

		// \PDO::FETCH_INTO および \PDO::FETCH_CLASS でのオブジェクト生成は、プロパティのセット後にコンストラクタが呼ばれる。
		// また __set() などは一切呼ばれず、プロパティの可視性も無視される。
		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchStyleForJson([
			\PDO::FETCH_INTO,
			new User(null, \DateTime::RFC3339),
		]);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->user_id);
		$this->assertEquals('test1', $records[0]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->created_at);

		$this->assertEquals('2', $records[1]->user_id);
		$this->assertEquals('test2', $records[1]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->created_at);
	}

	public function testJsonSerializeWithFetchStyleForJsonIsFetchClassUser()
	{
		$now = new \DateTime();

		$pdo = $this->createRecord($now);

		// \PDO::FETCH_INTO および \PDO::FETCH_CLASS でのオブジェクト生成は、プロパティのセット後にコンストラクタが呼ばれる。
		// また __set() などは一切呼ばれず、プロパティの可視性も無視される。
		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchStyleForJson([
			\PDO::FETCH_CLASS,
			'\Acme\User',
			[null, \DateTime::RFC3339],
		]);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->user_id);
		$this->assertEquals('test1', $records[0]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->created_at);

		$this->assertEquals('2', $records[1]->user_id);
		$this->assertEquals('test2', $records[1]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->created_at);
	}

	public function testJsonSerializeWithFetchStyleForJsonIsFetchFuncUser()
	{
		$now = new \DateTime();

		$pdo = $this->createRecord($now);

		// \PDO::FETCH_FUNC でのオブジェクト生成は書いた通り動作する。
		$statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
		$statement->execute();
		$statement->setFetchStyleForJson([
			\PDO::FETCH_FUNC,
			function ($user_id, $user_name, $created_at) {
				$user = new User(
					[
						'user_id'    => $user_id,
						'user_name'  => $user_name,
						'created_at' => $created_at,
					],
					\DateTime::RFC3339
				);
				return $user;
			},
		]);
		$records = $statement->jsonSerialize();

		$this->assertEquals('1', $records[0]->user_id);
		$this->assertEquals('test1', $records[0]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->created_at);

		$this->assertEquals('2', $records[1]->user_id);
		$this->assertEquals('test2', $records[1]->user_name);
		$this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->created_at);
	}

}
