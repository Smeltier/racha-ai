<?php

namespace App\Models\User;

use LogicException;
use PDO;
use PDOException;
use RuntimeException;

final class UserDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(User $user) : int
    {
        if ($user->getId() !== null) {
            throw new LogicException("User already persisted");
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO users
             (name, email, password_hash)
             VALUES (:name, :email, :password_hash)"
        );

        try {
            $stmt->execute([
                ':name' => $user->getName(),
                ':email' => $user->getEmail()->getValue(),
                ':password_hash' => $user->getPasswordHash()
            ]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) == 1062) {
                throw new RuntimeException("Email already in use");
            }
            throw $e;
        }

        $id = (int) $this->pdo->lastInsertId();
        $user->assignId($id);
        return $id;
    }

    public function update(User $user) : void
    {
    }

    public function delete(int $id) : void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM users
             WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();
        } catch (PDOException $e) {

        }
    }

    public function findById(int $id) : ?User
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password_hash
             FROM users WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $tuple = $stmt->fetch();
        return $tuple ? $this->mapTuple($tuple) : null;
    }

    public function findByEmail(Email $email) : ?User
    {
        $emailValue = $email->getValue();

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password_hash
             FROM users WHERE email = :email"
        );
        $stmt->bindParam(':email', $emailValue);

        $stmt->execute();

        $tuple = $stmt->fetch();

        return $tuple ? $this->mapTuple($tuple) : null;
    }

    public function existsByEmail(Email $email) : bool
    {
        $emailValue = $email->getValue();

        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->bindParam(':email', $emailValue);

        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    private function mapTuple(array $tuple) : User
    {
        return User::reconstitute(
            (int) $tuple['id'],
            $tuple['name'],
            $tuple['email'],
            $tuple['password_hash']
        );
    }
}
