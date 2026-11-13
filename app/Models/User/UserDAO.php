<?php

namespace App\Models\User;

use Throwable;
use App\Exceptions\PersistenceException;
use App\Exceptions\StateException;
use PDO;
use PDOException;
use App\Exceptions\EmailAlreadyInUseException;

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
            throw new StateException("O usuário já foi persistido");
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
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
                    throw new EmailAlreadyInUseException("O e-mail já está em uso", 0, $e);
                }
                throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
            }

            $id = (int) $this->pdo->lastInsertId();
            $user->assignId($id);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return $id;
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }
    }

    public function update(User $user) : void
    {
        if ($user->getId() === null) {
            throw new StateException("O usuário ainda não foi persistido");
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $stmt = $this->pdo->prepare(
                "UPDATE users
                SET name = :name,
                    email = :email,
                    password_hash = :password_hash,
                    deleted_at = :deleted_at
                WHERE id = :id"
            );

            try {
                $stmt->execute([
                    ':name' => $user->getName(),
                    ':email' => $user->getEmail()->getValue(),
                    ':password_hash' => $user->getPasswordHash(),
                    ':deleted_at' => $user->getDeletedAt()?->format('Y-m-d H:i:s'),
                    ':id' => $user->getId()
                ]);
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? null) == 1062) {
                    throw new EmailAlreadyInUseException("O e-mail já está em uso", 0, $e);
                }

                throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
            }

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }
    }

    public function findById(int $id) : ?User
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password_hash
             FROM users WHERE id = :id AND deleted_at IS NULL"
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
             FROM users WHERE email = :email AND deleted_at IS NULL"
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
            "SELECT 1 FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1"
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
            $tuple['password_hash'],
        );
    }
}
