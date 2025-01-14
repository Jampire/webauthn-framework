<?php

declare(strict_types=1);

namespace Webauthn\Tests\Bundle\Functional;

use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Uid\Ulid;
use Webauthn\Bundle\Repository\CanGenerateUserEntity;
use Webauthn\Bundle\Repository\CanRegisterUserEntity;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class PublicKeyCredentialUserEntityRepository implements PublicKeyCredentialUserEntityRepositoryInterface, CanRegisterUserEntity, CanGenerateUserEntity
{
    public function __construct(
        private CacheItemPoolInterface $cacheItemPool
    ) {
        $this->saveUserEntity(User::create('admin', 'foo', 'Foo BAR (-_-)', null, ['ROLE_ADMIN', 'ROLE_USER']));
        $this->saveUserEntity(User::create(
            'XY5nn3p_6olTLjoB2Jbb',
            '929fba2f-2361-4bc6-a917-bb76aa14c7f9',
            'Bennie Moneypenny',
            null,
            ['ROLE_ADMIN', 'ROLE_USER']
        ));
    }

    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $item = $this->cacheItemPool->getItem('user-name' . Base64UrlSafe::encodeUnpadded($username));
        if (! $item->isHit()) {
            return null;
        }

        return $item->get();
    }

    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $item = $this->cacheItemPool->getItem('user-id' . Base64UrlSafe::encodeUnpadded($userHandle));
        if (! $item->isHit()) {
            return null;
        }

        return $item->get();
    }

    public function generateNextUserEntityId(): string
    {
        return Ulid::generate();
    }

    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        if (! $userEntity instanceof User) {
            $userEntity = User::create($userEntity->name, $userEntity->id, $userEntity->displayName);
        }

        $item = $this->cacheItemPool->getItem('user-id' . Base64UrlSafe::encodeUnpadded($userEntity->id));
        $item->set($userEntity);
        $this->cacheItemPool->save($item);

        $item = $this->cacheItemPool->getItem('user-name' . Base64UrlSafe::encodeUnpadded($userEntity->name));
        $item->set($userEntity);
        $this->cacheItemPool->save($item);
    }

    public function generateUserEntity(?string $username, ?string $displayName): PublicKeyCredentialUserEntity
    {
        $username ??= Base64UrlSafe::encodeUnpadded(random_bytes(32));
        $displayName ??= $username;
        $id = Ulid::generate();

        return User::create($username, $id, $displayName);
    }
}
