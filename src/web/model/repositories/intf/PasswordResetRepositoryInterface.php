<?php
declare(strict_types=1);

interface PasswordResetRepositoryInterface
{
    /**
     * Deletes any existing tokens for the user, creates a new one that
     * expires in one hour, and returns the raw (unhashed) token to be
     * sent by email.
     */
    public function createToken(int $userId): string;

    /**
     * Hashes the raw token and looks up a record that is not yet expired
     * and not yet used. Returns ['reset_id' => ..., 'user_id' => ...] or null.
     */
    public function findValidByToken(string $rawToken): ?array;

    public function markUsed(int $resetId): void;

    public function deleteByUser(int $userId): void;
}
