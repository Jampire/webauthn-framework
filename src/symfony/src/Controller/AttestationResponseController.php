<?php

declare(strict_types=1);

namespace Webauthn\Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\Bundle\Exception\HttpNotImplementedException;
use Webauthn\Bundle\Exception\MissingFeatureException;
use Webauthn\Bundle\Repository\CanSaveCredentialSource;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\Bundle\Security\Handler\FailureHandler;
use Webauthn\Bundle\Security\Handler\SuccessHandler;
use Webauthn\Bundle\Security\Storage\OptionsStorage;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class AttestationResponseController
{
    public function __construct(
        private SerializerInterface $publicKeyCredentialLoader,
        private AuthenticatorAttestationResponseValidator $attestationResponseValidator,
        private PublicKeyCredentialSourceRepositoryInterface $credentialSourceRepository,
        private OptionsStorage $optionStorage,
        private SuccessHandler $successHandler,
        private FailureHandler|AuthenticationFailureHandlerInterface $failureHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            if (! $this->credentialSourceRepository instanceof CanSaveCredentialSource) {
                throw MissingFeatureException::create('Unable to register the credential.');
            }
            $format = $request->getContentTypeFormat();
            $format === 'json' || throw new BadRequestHttpException('Only JSON content type allowed');
            $content = $request->getContent();
            $publicKeyCredential = $this->publicKeyCredentialLoader->deserialize(
                $content,
                PublicKeyCredential::class,
                'json'
            );
            $response = $publicKeyCredential->response;
            $response instanceof AuthenticatorAttestationResponse || throw new BadRequestHttpException(
                'Invalid response'
            );
            $storedData = $this->optionStorage->get($response->clientDataJSON->challenge);
            $publicKeyCredentialCreationOptions = $storedData->getPublicKeyCredentialOptions();
            $publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions || throw new BadRequestHttpException(
                'Unable to find the public key credential creation options'
            );
            $userEntity = $storedData->getPublicKeyCredentialUserEntity();
            $userEntity instanceof PublicKeyCredentialUserEntity || throw new BadRequestHttpException(
                'Unable to find the public key credential user entity'
            );
            $credentialSource = $this->attestationResponseValidator->check(
                $response,
                $publicKeyCredentialCreationOptions,
                $request->getHost(),
            );
            if ($this->credentialSourceRepository->findOneByCredentialId(
                $credentialSource->publicKeyCredentialId
            ) !== null) {
                throw new BadRequestHttpException('The credentials already exists');
            }
            $this->credentialSourceRepository->saveCredentialSource($credentialSource);
            return $this->successHandler->onSuccess($request);
        } catch (Throwable $throwable) {
            if ($throwable instanceof MissingFeatureException) {
                throw new HttpNotImplementedException($throwable->getMessage(), $throwable);
            }
            $exception = new AuthenticationException($throwable->getMessage(), 401, $throwable);
            if ($this->failureHandler instanceof AuthenticationFailureHandlerInterface) {
                return $this->failureHandler->onAuthenticationFailure($request, $exception);
            }
            return $this->failureHandler->onFailure($request, $exception);
        }
    }
}
