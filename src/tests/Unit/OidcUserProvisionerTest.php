<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OidcUserProvisionerTest extends TestCase
{
    private MockObject         $userRepo;
    private MockObject         $identityRepo;
    private \OidcUserProvisioner $provisioner;

    protected function setUp(): void
    {
        $this->userRepo     = $this->createMock(\UserRepositoryInterface::class);
        $this->identityRepo = $this->createMock(\OidcIdentityRepositoryInterface::class);

        $this->provisioner = new \OidcUserProvisioner(
            $this->userRepo,
            $this->identityRepo,
        );
    }

    // -------------------------------------------------------------------------
    // Known OIDC identity (findByProviderSub hit)
    // -------------------------------------------------------------------------

    public function test_returns_user_when_known_by_sub_and_active(): void
    {
        $user = $this->makeUser(userId: 5, userIsActive: true);

        $this->identityRepo->method('findByProviderSub')->willReturn($this->makeIdentity(userId: 5));
        $this->userRepo->method('findById')->with(5)->willReturn($user);

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'user@example.com', 'Test');

        $this->assertSame($user, $result);
    }

    public function test_returns_null_when_known_by_sub_but_user_inactive(): void
    {
        $user = $this->makeUser(userId: 5, userIsActive: false, userIsNew: false);

        $this->identityRepo->method('findByProviderSub')->willReturn($this->makeIdentity(userId: 5));
        $this->userRepo->method('findById')->with(5)->willReturn($user);

        $this->userRepo->expects($this->never())->method('activate');

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'user@example.com', 'Test');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Email match (findByProviderSub miss, findByEmail hit)
    // -------------------------------------------------------------------------

    public function test_links_identity_and_returns_active_user_on_email_match(): void
    {
        $existingUser = $this->makeUser(userId: 7, userIsActive: true, userIsNew: false);
        $reloadedUser = $this->makeUser(userId: 7, userIsActive: true, userIsNew: false);

        $this->identityRepo->method('findByProviderSub')->willReturn(null);
        $this->userRepo->method('findByEmail')->willReturn($existingUser);
        $this->userRepo->method('findById')->with(7)->willReturn($reloadedUser);

        $this->identityRepo->expects($this->once())->method('create')->with(7, 1, 'sub123');
        $this->userRepo->expects($this->once())->method('removePassword')->with(7);
        $this->userRepo->expects($this->never())->method('activate');

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'user@example.com', 'Test');

        $this->assertSame($reloadedUser, $result);
    }

    public function test_auto_activates_new_inactive_user_on_email_match(): void
    {
        $existingUser = $this->makeUser(userId: 8, userIsActive: false, userIsNew: true);
        $reloadedUser = $this->makeUser(userId: 8, userIsActive: true,  userIsNew: true);

        $this->identityRepo->method('findByProviderSub')->willReturn(null);
        $this->userRepo->method('findByEmail')->willReturn($existingUser);
        $this->userRepo->method('findById')->with(8)->willReturn($reloadedUser);

        $this->identityRepo->expects($this->once())->method('create')->with(8, 1, 'sub123');
        $this->userRepo->expects($this->once())->method('removePassword')->with(8);
        $this->userRepo->expects($this->once())->method('activate')->with(8);

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'user@example.com', 'Test');

        $this->assertSame($reloadedUser, $result);
    }

    public function test_returns_null_when_email_matched_user_is_inactive_and_not_new(): void
    {
        $existingUser = $this->makeUser(userId: 9, userIsActive: false, userIsNew: false);
        $reloadedUser = $this->makeUser(userId: 9, userIsActive: false, userIsNew: false);

        $this->identityRepo->method('findByProviderSub')->willReturn(null);
        $this->userRepo->method('findByEmail')->willReturn($existingUser);
        $this->userRepo->method('findById')->with(9)->willReturn($reloadedUser);

        $this->userRepo->expects($this->never())->method('activate');

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'user@example.com', 'Test');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // No match — new user creation
    // -------------------------------------------------------------------------

    public function test_creates_new_user_when_no_match_found(): void
    {
        $newUser = $this->makeUser(userId: 10, userIsActive: true);

        $this->identityRepo->method('findByProviderSub')->willReturn(null);
        $this->userRepo->method('findByEmail')->willReturn(null);
        $this->userRepo->method('createOidc')->with('Test User', 'test@example.com')->willReturn(10);
        $this->userRepo->method('findById')->with(10)->willReturn($newUser);

        $this->identityRepo->expects($this->once())->method('create')->with(10, 1, 'sub123');
        $this->userRepo->expects($this->once())->method('activate')->with(10);

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'test@example.com', 'Test User');

        $this->assertSame($newUser, $result);
    }

    public function test_uses_email_prefix_as_display_name_when_name_is_empty(): void
    {
        $newUser = $this->makeUser(userId: 11, userIsActive: true);

        $this->identityRepo->method('findByProviderSub')->willReturn(null);
        $this->userRepo->method('findByEmail')->willReturn(null);
        $this->userRepo->method('findById')->with(11)->willReturn($newUser);
        $this->userRepo->method('createOidc')->willReturn(11);

        $this->userRepo->expects($this->once())
            ->method('createOidc')
            ->with('jdoe', 'jdoe@example.com')
            ->willReturn(11);

        $result = $this->provisioner->findOrProvision(1, 'sub123', 'jdoe@example.com', '');

        $this->assertSame($newUser, $result);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(
        int    $userId     = 1,
        bool   $userIsActive = true,
        bool   $userIsNew  = false,
        ?string $userPasswd = null,
    ): \UserDto {
        return new \UserDto(
            userId:       $userId,
            userGuid:     'guid-' . $userId,
            userEmail:    'user@example.com',
            userIsNew:    $userIsNew,
            userIsActive: $userIsActive,
            userRole:     0,
            userName:     'Test User',
            userPasswd:   $userPasswd,
            userLastLogin: null,
        );
    }

    private function makeIdentity(int $userId = 1): \OidcIdentityDto
    {
        return new \OidcIdentityDto(
            identityId:  1,
            userId:      $userId,
            providerId:  1,
            providerKey: 'google',
            providerSub: 'sub123',
            linkedAt:    new \DateTimeImmutable(),
        );
    }
}
