<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test\PDO;

use Acme\PDO\PDO;

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
        $pdo->setEscapeCharacter('\\');

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
        $this->assertInstanceOf('\Acme\PDO\PDOStatement', $statement);
    }

    public function testPrepareWithOption()
    {
        $pdo = $this->createTable();
        $statement = $pdo->prepare("SELECT user_id, user_name, created_at FROM users ORDER BY user_id", [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
        $this->assertInstanceOf('\Acme\PDO\PDOStatement', $statement);
    }

    public function testQuery()
    {
        $pdo = $this->createTable();
        $statement = $pdo->query("SELECT user_id, user_name, created_at FROM users ORDER BY user_id");
        $this->assertInstanceOf('\Acme\PDO\PDOStatement', $statement);
    }

    public function testQueryWithSetFetchModeToFetchInto()
    {
        $now = new \DateTimeImmutable('now');

        $pdo = $this->createTable();

        $pdo->beginTransaction();
        $statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
        $statement->execute([
            ':user_name' => 'test1',
            ':created_at' => $now->getTimestamp(),
        ]);
        $pdo->commit();

        $statement = $pdo->query(
            "SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id",
            \PDO::FETCH_INTO,
            new PDOTestDataMutable([
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ])
        );
        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataMutable', $user);
    }

    public function testQueryWithSetFetchModeToFetchClass()
    {
        $now = new \DateTimeImmutable('now');

        $pdo = $this->createTable();

        $pdo->beginTransaction();
        $statement = $pdo->prepare("INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)");
        $statement->execute([
            ':user_name' => 'test1',
            ':created_at' => $now->getTimestamp(),
        ]);
        $pdo->commit();

        $statement = $pdo->query(
            "SELECT user_id AS userId, user_name AS userName, created_at AS createdAt FROM users ORDER BY user_id",
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Acme\Test\PDO\PDOTestDataImmutable',
            [[
                'now' => $now,
                'dateFormat' => 'Y/m/d',
                'dateTimeFormat' => 'Y-m-d H:i:s',
            ]]
        );
        $user = $statement->fetch();

        $this->assertInstanceOf('\Acme\Test\PDO\PDOTestDataImmutable', $user);
    }

    public function testEscapeCharacter()
    {
        $pdo = $this->createTable();
        $pdo->setEscapeCharacter('!');
        $this->assertEquals('!%Foo!%', $pdo->escapeLikePattern('%Foo%'));
        $this->assertEquals('!_Foo!_', $pdo->escapeLikePattern('_Foo_'));
    }

    public function testPrepareEscapeLikePattern()
    {
        $now = new \DateTimeImmutable('now');

        $pdo = $this->createTable();

        $pdo->beginTransaction();
        $statement = $pdo->prepare(
            "INSERT INTO users (user_name, created_at) VALUES (:user_name, :created_at)"
        );
        $statement->execute([
            'user_name' => 'Foo-%RIK%-1',
            'created_at' => $now->getTimestamp(),
        ]);
        $statement->execute([
            'user_name' => 'Bar-%RIK%-2',
            'created_at' => $now->getTimestamp(),
        ]);
        $statement->execute([
            'user_name' => 'Baz-%RIK%-3',
            'created_at' => $now->getTimestamp(),
        ]);
        $pdo->commit();

        $statement = $pdo->prepare(
            "SELECT user_id, user_name, created_at FROM users WHERE user_name LIKE :user_name ESCAPE '\\'"
        );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $statement->execute(['user_name' => '%' . $pdo->escapeLikePattern('%RIK%') . '%']);

        $user = $statement->fetch();
        $this->assertEquals('Foo-%RIK%-1', $user['user_name']);

        $user = $statement->fetch();
        $this->assertEquals('Bar-%RIK%-2', $user['user_name']);

        $user = $statement->fetch();
        $this->assertEquals('Baz-%RIK%-3', $user['user_name']);

        $statement->execute(['user_name' => $pdo->escapeLikePattern('Foo-%') . '%']);
        $user = $statement->fetch();
        $this->assertEquals('Foo-%RIK%-1', $user['user_name']);

        $statement->execute(['user_name' => $pdo->escapeLikePattern('Bar-%') . '%']);
        $user = $statement->fetch();
        $this->assertEquals('Bar-%RIK%-2', $user['user_name']);

        $statement->execute(['user_name' => $pdo->escapeLikePattern('Baz-%') . '%']);
        $user = $statement->fetch();
        $this->assertEquals('Baz-%RIK%-3', $user['user_name']);

    }

}
