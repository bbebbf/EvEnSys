<?php
declare(strict_types=1);

interface OidcProviderRepositoryInterface
{
    /**
     * Returns active providers only, keyed by providerKey.
     *
     * @return OidcProviderInfoDto[] keyed by providerKey
     */
    public function findAllActiveInfos(): array;

    public function findByKey(string $providerKey): ?OidcProviderDto;
}
