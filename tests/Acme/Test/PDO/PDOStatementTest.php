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
  user_id    INTEGER     NOT NULL PRIMARY KEY AUTOINCREMENT 
, user_name  TEXT        NOT NULL
, birthday   VARCHAR(10) NOT NULL
, created_at INTEGER     NOT NULL
);
SQL
        );

        $pdo->beginTransaction();
        $statement = new PDOStatement($pdo->prepare("INSERT INTO users (user_name, birthday, created_at) VALUES (:user_name, :birthday, :created_at)"));
        $statement->execute(['user_name' => 'test1', 'birthday' => '1980-12-20', 'created_at' => $now->getTimestamp()]);
        $statement->execute(['user_name' => 'test2', 'birthday' => '1996-01-01', 'created_at' => $now->getTimestamp()]);
        $pdo->commit();

        return $pdo;
    }

    public function testCallPdoStatementMethod()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT user_id, user_name, birthday, created_at FROM users ORDER BY user_id"));

        $statement->execute();

        $user = $statement->fetchObject();

        $this->assertEquals('1', $user->user_id);
        $this->assertEquals('test1', $user->user_name);
        $this->assertEquals('1980-12-20', $user->birthday);
        $this->assertEquals($now->getTimestamp(), $user->created_at);
    }

    public function testCallPdoStatementMethodWithArguments()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday
, created_at AS createdAt
FROM
  users
WHERE
  user_id = :user_id
SQL
        ));

        $statement->execute(['user_id' => 1]);
        $user = $statement->fetchObject(
            '\Acme\Test\PDO\PDOTestDataImmutable',
            [[
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]]
        );

        $this->assertNull($user->userId); // プロパティがセットされた後でコンストラクタが呼ばれるため、コンストラクタで指定したプロパティ以外はNULLになってしまう…
        $this->assertNull($user->userName);
        $this->assertNull($user->birthday);
        $this->assertNull($user->createdAt);
        $this->assertEquals($now, $user->now);
        $this->assertEquals('Y/m/d', $user->dateFormat);
        $this->assertEquals('Y-m-d H:i:s', $user->dateTimeFormat);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testRaiseExceptionWhenUndefinedMethodIsCalled()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday
, created_at AS createdAt
FROM
  users
ORDER BY
  user_id
SQL
        ));

        $statement->undefinedMethod();
    }

    public function testExecuteParamInt()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_id = :user_id
SQL
        ));

        $statement->execute(['user_id' => 1]);
        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('1', $user['user_id']);
        $this->assertEquals('test1', $user['user_name']);
        $this->assertEquals('1980-12-20', $user['birthday']);
        $this->assertEquals($now->getTimestamp(), $user['created_at']);
    }

    public function testExecuteParamStr()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_name = :user_name
SQL
        ));

        $statement->execute(['user_name' => 'test2']);
        $user = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('2', $user['user_id']);
        $this->assertEquals('test2', $user['user_name']);
        $this->assertEquals('1996-01-01', $user['birthday']);
        $this->assertEquals($now->getTimestamp(), $user['created_at']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRaiseExceptionWhenParameterIsInvalidIbject()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_id = :user_id
SQL
        ));
        $statement->execute($now);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExecuteRaiseExceptionWhenParameterIsInvalidType()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_id = :user_id
SQL
        ));
        $statement->execute(false);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExecuteRaiseExceptionWhenPDOExceptionIsThrown()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_name = :user_name
SQL
        ));
        $statement->execute(['user_id' => 1]);
    }

    public function testFetchIntoMutableObject()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO でのオブジェクト生成は、フェッチモード指定時の引数でコンストラクタが呼ばれた後、列と同名のプロパティに値のセットを試みる。
        // プロパティの可視性は有効となり、定義されている場合は __set() が呼ばれる。
        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
WHERE
  user_id = :userId
SQL
        ));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_INTO,
            new PDOTestDataMutable([
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ])
        );

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataMutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals(new \DateTimeImmutable('1980-12-20', $now->getTimezone()), $user->birthday);
        $this->assertEquals('1980/12/20', $user->birthdayAsString);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    /**
     * @expectedException \LogicException
     */
    public function testFetchIntoImmutableObjectRaiseLogicException()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
WHERE
  user_id = :userId
SQL
        ));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_INTO,
            new PDOTestDataImmutable([
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ])
        );

        $user = $statement->fetch();
    }

    public function testFetchClassMutableObject()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        // PDO::FETCH_CLASS でのオブジェクト生成は、列と同名のプロパティに可視性に関わらず値をセットし、その後でフェッチモード指定時の引数でコンストラクタが呼ばれる。
        // コンストラクタが呼ばれた時点ではすでにプロパティに値がセットされた状態となり、__set() が呼ばれることはない。
        // PDO::FETCH_PROPS_LATE を合わせて指定すると動作が変更され、コンストラクタが呼ばれた後でプロパティに値がセットされるようになるが、やはり __set() が呼ばれることはない。
        // そのため、__set() での値のバリデーションや変換は機能しない。
        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
WHERE
  user_id = :userId
SQL
        ));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE,
            '\Acme\Test\PDO\PDOTestDataMutable',
            [[
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]]
        );

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataMutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals(new \DateTimeImmutable('1980-12-20', $now->getTimezone()), $user->birthday);
        $this->assertEquals('1980/12/20', $user->birthdayAsString);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchClassImmutableObject()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        // PDO::FETCH_CLASS + PDO::FETCH_PROPS_LATE の場合、ImmutableTrait::__set() は呼ばれないため LogicException はスローされない。
        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
WHERE
  user_id = :userId
SQL
        ));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE,
            '\Acme\Test\PDO\PDOTestDataImmutable',
            [[
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]]
        );

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataImmutable', $user);
        $this->assertEquals('1', $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchCallback()
    {
        $now = new \DateTimeImmutable('2013-12-20 00:00:00', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $statement = new PDOStatement($pdo->prepare("SELECT * FROM users WHERE user_id = :userId"));
        $statement->execute(['userId' => 1]);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($now) {
            return new User([
                'userId'     => (int)$cols['user_id'],
                'userName'   => $cols['user_name'],
                'birthday'   => new \DateTimeImmutable($cols['birthday']),
                'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                'now'        => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]);
        });

        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Domain\Data\User', $user);
        $this->assertEquals(1, $user->userId);
        $this->assertEquals('test1', $user->userName);
        $this->assertEquals(new \DateTimeImmutable('1980-12-20', $now->getTimezone()), $user->birthday);
        $this->assertEquals(33, $user->age);
        $this->assertEquals($now, $user->createdAt);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
    }

    public function testFetchCallbackInIteration()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare("SELECT * FROM users"));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($now) {
            return new User([
                'userId'     => (int)$cols['user_id'],
                'userName'   => $cols['user_name'],
                'birthday'   => new \DateTimeImmutable($cols['birthday']),
                'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                'now'        => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]);
        });
        $statement->execute();

        foreach ($statement as $user) {
            $this->assertInstanceOf('\Acme\Domain\Data\User', $user);
            $this->assertEquals($now, $user->createdAt);
            $this->assertEquals($now->format('Y-m-d H:i:s'), $user->createdAtAsString);
            switch ($user->userId) {
            case 1:
                $this->assertEquals('test1', $user->userName);
                $this->assertEquals(new \DateTimeImmutable('1980-12-20', $now->getTimezone()), $user->birthday);
                $this->assertEquals(32, $user->age);
                break;
            case 2:
                $this->assertEquals('test2', $user->userName);
                $this->assertEquals(new \DateTimeImmutable('1996-01-01', $now->getTimezone()), $user->birthday);
                $this->assertEquals(17, $user->age);
                break;
            }
        }

    }

    public function testFetchCallbackReturnedFalseWhenFetchReturnedFalse()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
WHERE
  user_id = :userId
SQL
        ));
        $statement->setFetchCallback(function($cols) {
            return true;
        });

        $statement->execute(['userId' => 1000]);

        $this->assertFalse($statement->fetch());
    }

    public function testJsonSerializeByFetchAssoc()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertEquals('1', $records[0]['user_id']);
        $this->assertEquals('test1', $records[0]['user_name']);
        $this->assertEquals('1980-12-20', $records[0]['birthday']);
        $this->assertEquals($now->getTimestamp(), $records[0]['created_at']);

        $this->assertEquals('2', $records[1]['user_id']);
        $this->assertEquals('test2', $records[1]['user_name']);
        $this->assertEquals('1996-01-01', $records[1]['birthday']);
        $this->assertEquals($now->getTimestamp(), $records[1]['created_at']);
    }

    public function testJsonSerializeByFetchNum()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_NUM);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();


        $this->assertEquals('1', $records[0][0]);
        $this->assertEquals('test1', $records[0][1]);
        $this->assertEquals('1980-12-20', $records[0][2]);
        $this->assertEquals($now->getTimestamp(), $records[0][3]);

        $this->assertEquals('2', $records[1][0]);
        $this->assertEquals('test2', $records[1][1]);
        $this->assertEquals('1996-01-01', $records[1][2]);
        $this->assertEquals($now->getTimestamp(), $records[1][3]);
    }

    public function testJsonSerializeByFetchObject()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_OBJ);

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertEquals('1', $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals('1980-12-20', $records[0]->birthday);
        $this->assertEquals($now->getTimestamp(), $records[0]->createdAt);

        $this->assertEquals('2', $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals('1996-01-01', $records[1]->birthday);
        $this->assertEquals($now->getTimestamp(), $records[1]->createdAt);
    }

    public function testJsonSerializeByFetchClass()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE,
            '\Acme\Test\PDO\PDOTestDataImmutable',
            [[
                'now' => $now,
                'dateFormat' => 'Y-m-d',
                'dateTimeFormat' => \DateTime::RFC3339,
            ]]
        );

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals('1', $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals('1980-12-20', $records[0]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
        $this->assertEquals('2', $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals('1996-01-01', $records[1]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
    }

    public function testJsonSerializeByFetchInto()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_INTO,
            new PDOTestDataMutable([
                'now' => $now,
                'dateFormat' => 'Y-m-d',
                'dateTimeFormat' => \DateTime::RFC3339,
            ])
        );

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals('1', $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals('1980-12-20', $records[0]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
        $this->assertEquals('2', $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals('1996-01-01', $records[1]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
    }

    /**
     * @expectedException \LogicException
     */
    public function testJsonSerializeByFetchIntoRaiseLogicExceptionWhenObjectIsImmutable()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        // PDO::FETCH_INTO の場合、ImmutableTrait::__set() から LogicException がスローされる。
        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id AS userId
, user_name AS userName
, birthday AS birthday
, created_at AS createdAt
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_INTO,
            new PDOTestDataImmutable(null, $now->getTimezone(), \DateTime::RFC3339)
        );

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();
    }

    public function testJsonSerializeByFetchCallback()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tokyo'));

        $pdo = $this->createRecord($now);

        $statement = new PDOStatement($pdo->prepare(<<<'SQL'
SELECT
  user_id
, user_name
, birthday
, created_at
FROM
  users
ORDER BY
  user_id
SQL
        ));
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->setFetchCallback(function($cols) use ($now) {
            return new User([
                'userId'     => (int)$cols['user_id'],
                'userName'   => $cols['user_name'],
                'birthday'   => new \DateTimeImmutable($cols['birthday']),
                'createdAt'  => new \DateTimeImmutable(sprintf('@%d', $cols['created_at'])),
                'now'        => $now,
                'dateFormat' => 'Y-m-d',
                'dateTimeFormat' => \DateTime::RFC3339,
            ]);
        });

        $serializer = new JsonSerializer($statement);
        $records = $serializer->jsonSerialize();

        $this->assertInstanceOf('\stdClass', $records[0]);
        $this->assertEquals(1, $records[0]->userId);
        $this->assertEquals('test1', $records[0]->userName);
        $this->assertEquals('1980-12-20', $records[0]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[0]->createdAt);

        $this->assertInstanceOf('\stdClass', $records[1]);
        $this->assertEquals(2, $records[1]->userId);
        $this->assertEquals('test2', $records[1]->userName);
        $this->assertEquals('1996-01-01', $records[1]->birthday);
        $this->assertEquals($now->format(\DateTime::RFC3339), $records[1]->createdAt);
    }

}
