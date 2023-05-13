<?php
declare(strict_types = 1);

namespace WapplerSystems\AiImageMeta\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\AbstractFormEngineAjaxController;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class ImageMetaAjaxController extends AbstractFormEngineAjaxController
{

    /**
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \RuntimeException
     */
    public function suggestAction(ServerRequestInterface $request): ResponseInterface
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
