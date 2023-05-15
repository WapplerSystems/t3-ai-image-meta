<?php
declare(strict_types = 1);

namespace WapplerSystems\AiImageMeta\Controller;


use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Controller\AbstractFormEngineAjaxController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
#[Controller]
class ImageMetaAjaxController extends AbstractFormEngineAjaxController
{

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly RequestFactory $requestFactory,
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
        //$this->checkRequest($request);

        $results = [];

        $queryParameters = $request->getParsedBody() ?? [];
        $fileMetaUid = (int)$queryParameters['fileUid'];


        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

        $fileUid = $this->getFileIdByFileMetaId($fileMetaUid);

        $azureApiKey = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('ai_image_meta', 'azure.apiKey');
        $azureEndPoint = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('ai_image_meta', 'azure.endPoint');


        try {
            $file = $resourceFactory->getFileObject($fileUid);

            $mimeType = $file->getMimeType();
            $content = $file->getContents();


            $additionalOptions = [
                'headers' => ['Cache-Control' => 'no-cache', 'Content-Type' => $mimeType, 'Ocp-Apim-Subscription-Key' => $azureApiKey],
                'allow_redirects' => false,
                'body' => $content,
            ];


            $response = $this->requestFactory->request(
                $azureEndPoint.'/computervision/imageanalysis:analyze?api-version=2023-02-01-preview&features=caption&model-version=latest',
                'POST',
                $additionalOptions
            );

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException(
                    'Returned status code is ' . $response->getStatusCode()
                );
            }

            if (!str_contains($response->getHeaderLine('Content-Type'),'application/json')) {
                throw new \RuntimeException(
                    'The request did not return JSON data'
                );
            }
            // Get the content as a string on a successful request
            $content = $response->getBody()->getContents();

            return new JsonResponse(json_decode($content, true, flags: JSON_THROW_ON_ERROR));

        } catch (FileDoesNotExistException $e) {
        }

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

    private function getFileIdByFileMetaId(int $uid) {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_metadata');

        $databaseRecord = $queryBuilder->select('uid','file')
            ->from('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        return $databaseRecord['file'];

    }
}
