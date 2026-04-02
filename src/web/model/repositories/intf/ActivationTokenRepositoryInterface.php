<?php
declare(strict_types=1);

interface ActivationTokenRepositoryInterface
{
    /**
     * Deletes any existing activation tokens for the user, creates a new one
     * that expires in X hours, and returns the raw (unhashed) token to be
     * sent by email.
     */
    public function createToken(int $userId, int $validityHours): string;

    /**
     * Hashes the raw token and looks up a record that is not yet expired
     * and not yet used. Returns ['token_id' => ..., 'user_id' => ...] or null.
     */
    public function findValidByToken(string $rawToken): ?array;

    public function markUsed(int $tokenId): void;

    public function deleteByUser(int $userId): void;
}
