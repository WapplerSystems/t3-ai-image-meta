<?php
declare(strict_types = 1);

namespace WapplerSystems\AiImageMeta\Controller;


use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Controller\AbstractFormEngineAjaxController;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
#[Controller]
class ImageMetaAjaxController extends AbstractFormEngineAjaxController
{

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory
    ) {
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \RuntimeException
     */
    public function descriptionAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->checkRequest($request);

        $results = [];

        $queryParameters = $request->getParsedBody() ?? [];
        $fileUid = $queryParameters['fileUid'];



        return new JsonResponse($results);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function checkRequest(ServerRequestInterface $request): bool
    {
        $queryParameters = $request->getParsedBody() ?? [];
        $expectedHash = GeneralUtility::hmac(
            $queryParameters['collections'] ?? '',
            __CLASS__
        );
        if (!hash_equals($expectedHash, $queryParameters['signature'] ?? '')) {
            throw new \InvalidArgumentException(
                'HMAC could not be verified',
                1535137045
            );
        }
        return true;
    }
}
