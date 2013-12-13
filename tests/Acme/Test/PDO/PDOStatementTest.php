<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test\PDO;

use Acme\PDO\PDOStatement;
use Acme\Domain\Data\User;
use Acme\JsonSerializer;

/**
 * Test for PDOStatement
 *
 * @author k.holy74@gmail.com
 */
class PDOStatementTest extends \PHPUnit_Framework_TestCase
{

    private function createRecord(\DateTimeImmutable $now)
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
        $statement->execute(['user_name' => 'test1', 'created_at' => $now->getTimestamp()]);
        $statement->execute(['user_name' => 'test2', 'created_at' => $now->getTimestamp()]);
        $pdo->commit();

        return $pdo;
    }

    public function testCallPdoStatementMethod()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));

        $statement->execute();

        $user = $statement->fetchObject();

        $this->assertEquals('1', $user->user_id);
        $this->assertEquals('test1', $user->user_name);
        $this->assertEquals($now->getTimestamp(), $user->created_at);
    }

    public function testCallPdoStatementMethodWithArguments()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :user_id"));

        $statement->execute(['user_id' => 1]);
        $user = $statement->fetchObject('\Acme\Test\PDO\PDOTestDataImmutable', [['timezone' => $timezone, 'dateFormat' => 'Y-m-d H:i:s']]);

        $this->assertNull($user->userId); // プロパティがセットされた後でコンストラクタが呼ばれるため、コンストラクタで指定したプロパティ以外はNULLになってしまう…
        $this->assertNull($user->userName);
        $this->assertEquals($timezone, $user->timezone);
        $this->assertEquals('Y-m-d H:i:s', $user->dateFormat);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testRaiseExceptionWhenUndefinedMethodIsCalled()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));

        $statement->undefinedMethod();
    }

    public function testExecuteParamInt()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :user_id"));

        $statement->execute(['user_id' => 1]);
        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('1', $user['user_id']);
        $this->assertEquals('test1', $user['user_name']);
        $this->assertEquals($now->getTimestamp(), $user['created_at']);
    }

    public function testExecuteParamStr()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_name = :user_name"));

        $statement->execute(['user_name' => 'test2']);
        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('2', $user['user_id']);
        $this->assertEquals('test2', $user['user_name']);
        $this->assertEquals($now->getTimestamp(), $user['created_at']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRaiseExceptionWhenParameterIsInvalidIbject()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :user_id"));
        $statement->execute($now);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRaiseExceptionWhenParameterIsInvalidType()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :user_id"));
        $statement->execute(false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteRaiseExceptionWhenPDOExceptionIsThrown()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_name = :user_name"));
        $statement->execute(['user_id' => 1]);
    }

    public function testFetchIntoMutableObject()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO でのオブジェクト生成は、フェッチモード指定時の引数でコンストラクタが呼ばれた後、列と同名のプロパティに値のセットを試みる。
        // プロパティの可視性は有効となり、定義されている場合は __set() が呼ばれる。
        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_INTO, new PDOTestDataMutable(['timezone' => $timezone, 'dateFormat' => 'Y-m-d H:i:s']));

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataMutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    /**
     * @expectedException \LogicException
     */
    public function testFetchIntoImmutableObjectRaiseLogicException()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_INTO, new PDOTestDataImmutable(['timezone' => $timezone, 'dateFormat' => 'Y-m-d H:i:s']));

        $user = $statement->fetch();
    }

    public function testFetchClassMutableObject()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        // PDO::FETCH_CLASS でのオブジェクト生成は、列と同名のプロパティに可視性に関わらず値をセットし、その後でフェッチモード指定時の引数でコンストラクタが呼ばれる。
        // コンストラクタが呼ばれた時点ではすでにプロパティに値がセットされた状態となり、__set() が呼ばれることはない。
        // PDO::FETCH_PROPS_LATE を合わせて指定すると動作が変更され、コンストラクタが呼ばれた後でプロパティに値がセットされるようになるが、やはり __set() が呼ばれることはない。
        // そのため、__set() での値のバリデーションや変換は機能しない。
        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Acme\Test\PDO\PDOTestDataMutable', [['timezone' => $timezone, 'dateFormat' => 'Y-m-d H:i:s']]);

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataMutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchClassImmutableObject()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        // PDO::FETCH_CLASS + PDO::FETCH_PROPS_LATE の場合、ImmutableTrait::__set() は呼ばれないため LogicException はスローされない。
        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Acme\Test\PDO\PDOTestDataImmutable', [['timezone' => $timezone, 'dateFormat' => 'Y-m-d H:i:s']]);

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataImmutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchCallback()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($timezone) {
            return new User(
                [
                    'userId'     => (int)$cols['user_id'],
                    'userName'   => $cols['user_name'],
                    'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                    'timezone'   => $timezone,
                    'dateFormat' => 'Y-m-d H:i:s',
                ]
            );
        });

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Domain\Data\User', $user);
        $this->assertEquals(1, $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchCallbackInIteration()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($timezone) {
            return new User(
                [
                    'userId'     => (int)$cols['user_id'],
                    'userName'   => $cols['user_name'],
                    'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                    'timezone'   => $timezone,
                    'dateFormat' => 'Y-m-d H:i:s',
                ]
            );
        });
        $statement->execute();

        foreach ($statement as $user) {
            $this->assertInstanceOf('\Acme\Domain\Data\User', $user);
            $this->assertEquals($now, $user->createdAt);
            $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
            switch ($user->userId) {
            case 1:
                $this->assertEquals('test1', $user->userName);
                break;
            case 2:
                $this->assertEquals('test2', $user->userName);
                break;
            }
        }

    }

    public function testFetchCallbackReturnedFalseWhenFetchReturnedFalse()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users WHERE user_id = :userId"));
        $statement->setFetchCallback(function($cols) use ($timezone) {
            return true;
        });

        $statement->execute(['userId' => 1000]);

        $this->assertFalse($statement->fetch());
    }

    public function testJsonSerializeByFetchAssoc()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

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
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_NUM);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertEquals('1', $records[0][0]);
        $this->assertEquals('test1', $records[0][1]);
        $this->assertEquals($now->getTimestamp(), $records[0][2]);

        $this->assertEquals('2', $records[1][0]);
        $this->assertEquals('test2', $records[1][1]);
        $this->assertEquals($now->getTimestamp(), $records[1][2]);
    }

    public function testJsonSerializeByFetchObject()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_OBJ);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

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
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Acme\Test\PDO\PDOTestDataImmutable', [['timezone' => $timezone, 'dateFormat' => \DateTime::RFC3339]]);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals('1', $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
        $this->assertEquals('2', $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
    }

    public function testJsonSerializeByFetchInto()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_INTO, new PDOTestDataMutable(['timezone' => $timezone, 'dateFormat' => \DateTime::RFC3339]));

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals('1', $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
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
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
        $statement = new PDOStatement($pdo->prepare("SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_INTO, new PDOTestDataImmutable(null, $timezone, \DateTime::RFC3339));

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();
    }

    public function testJsonSerializeByFetchCallback()
    {
        $timezone = new \DateTimeZone('Asia/Tokyo');
        $now = new \DateTimeImmutable('now', $timezone);

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($timezone) {
            return new User(
                [
                    'userId'     => (int)$cols['user_id'],
                    'userName'   => $cols['user_name'],
                    'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                    'timezone'   => $timezone,
                    'dateFormat' => \DateTime::RFC3339,
                ]
            );
        });

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals(1, $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
        $this->assertEquals(2, $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
    }

}
