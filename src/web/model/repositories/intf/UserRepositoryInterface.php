<?php
declare(strict_types=1);

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?UserDto;

    public function findById(int $id): ?UserDto;

    public function findByGuid(string $guid): ?UserDto;

    public function create(string $name, string $email, string $hashedPwd): int;

    public function createOidc(string $name, string $email): int;

    public function activate(int $userId): void;

    public function updateLastLogin(int $userId): void;

    public function updateName(int $userId, string $name): void;

    public function updatePassword(int $userId, string $hashedPwd): void;

    public function removePassword(int $userId): void;

    public function delete(int $userId): void;

    public function setActive(int $userId, bool $active): void;

    public function setRole(int $userId, int $role): void;

    public function countAdmins(): int;

    public function countAll(): int;

    /** @return UserDto[] */
    public function findAll(): array;
}
