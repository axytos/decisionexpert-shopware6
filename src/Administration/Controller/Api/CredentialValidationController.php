<?php

namespace Axytos\DecisionExpert\Shopware\Administration\Controller\Api;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Axytos\ECommerce\Clients\CredentialValidation\CredentialValidationClientInterface;
use Axytos\Shopware\ErrorReporting\ErrorHandler;

/**
 * @RouteScope(scopes={"administration"})
 */
class CredentialValidationController
{
    private CredentialValidationClientInterface $CredentialValidationClient;
    private ErrorHandler $errorHandler;

    public function __construct(
        CredentialValidationClientInterface $CredentialValidationClient,
        ErrorHandler $errorHandler
    ) {
        $this->CredentialValidationClient = $CredentialValidationClient;
        $this->errorHandler = $errorHandler;
    }

    /**
     * @Route(path="/api/v1/AxytosDecisionExpert/Credentials/validate")
     */
    public function validateCredentials(): JsonResponse
    {
        try {
            $success = $this->CredentialValidationClient->validateApiKey();

            return new JsonResponse(['success' => $success]);
        } catch (\Throwable $th) {
            $this->errorHandler->handle($th);

            return new JsonResponse(['success' => false]);
        }
    }
}
