<?php
declare(strict_types=1);

class OidcUserProvisioner
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private OidcIdentityRepositoryInterface $identityRepo,
    ) {}

    /**
     * Finds an existing user for the given OIDC identity, or creates one.
     *
     * Resolution order:
     *  1. Known OIDC identity (providerId + sub) → load linked user.
     *  2. Email matches an existing account → link identity to that user.
     *  3. No match at all → create a new OIDC-only account.
     *
     * Returns null when the resolved user is not active (and cannot be auto-activated).
     */
    public function findOrProvision(
        int    $providerId,
        string $sub,
        string $email,
        string $name
    ): ?UserDto {
        $existing  = null;
        $reloadDto = false;

        $identity = $this->identityRepo->findByProviderSub($providerId, $sub);
        if ($identity !== null) {
            $existing = $this->userRepo->findById($identity->userId);
        } else {
            $existing = $this->userRepo->findByEmail($email);
            if ($existing !== null) {
                $this->identityRepo->create($existing->userId, $providerId, $sub);
                $this->userRepo->removePassword($existing->userId);
                $reloadDto = true;
            }
        }

        if ($existing !== null) {
            if ($existing->userIsNew && !$existing->userIsActive) {
                $this->userRepo->activate($existing->userId);
                $reloadDto = true;
            }
            if ($reloadDto) {
                $existing = $this->userRepo->findById($existing->userId);
            }

            return $existing->userIsActive ? $existing : null;
        }

        $displayName = $name !== '' ? $name : explode('@', $email)[0];
        $newUserId   = $this->userRepo->createOidc($displayName, $email);
        $this->identityRepo->create($newUserId, $providerId, $sub);
        $this->userRepo->activate($newUserId);

        return $this->userRepo->findById($newUserId);
    }
}
