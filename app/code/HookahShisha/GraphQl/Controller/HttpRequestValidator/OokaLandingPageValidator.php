<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace HookahShisha\GraphQl\Controller\HttpRequestValidator;

use Exception;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;
use GraphQL\Language\Visitor;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;

/**
 * Processes the "Store" header entry
 */
class OokaLandingPageValidator implements HttpRequestValidatorInterface
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $jsonSerializer;

    /**
     * Allowed queries for 'ooka_store_view' Store header
     */
    private const ALLOWED_QUERIES = [
        'storeConfig',
        'categories',
        'products',
        'blog.*',
        'cms.*',
        'CmsSitemapData'
    ];

    /**
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(SerializerInterface $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Handle the mandatory application/json header
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException|SyntaxError
     * @throws Exception
     */
    public function validate(HttpRequestInterface $request) : void
    {
        $headerName = 'Store';
        $storeToValidate = 'ooka_store_view';
        $headerValue = (string)$request->getHeader($headerName);

        if ($headerValue == $storeToValidate) {
            $query = $this->getQueryDataFromRequest($request);

            if (!empty($query)) {
                $operationType = '';
                $queryAst = Parser::parse(new Source($query, 'GraphQL'));
                Visitor::visit(
                    $queryAst,
                    [
                        'leave' => [
                            NodeKind::OPERATION_DEFINITION => function (Node $node) use (&$operationType) {
                                $operationType = $node->operation;
                            }
                        ]
                    ]
                );

                if (strtolower($operationType) === 'mutation') {
                    throw new GraphQlInputException(
                        __('Mutation requests are not allowed for "%store" store header', ['store' => $headerValue])
                    );
                }
            }

            $matches = [];
            // Remove line breaks from query string
            $query = trim(preg_replace('/\s+/', '', $query));
            $allowedQueriesRegFormat = join('|', self::ALLOWED_QUERIES);
            preg_match('/\{('.$allowedQueriesRegFormat.'?)[({]/s', $query, $matches);

            if (!isset($matches[1])) {
                throw new GraphQlInputException(
                    __(
                        'This request is restricted for "%store" store header. Please check allowed queries: "%q"',
                        ['store' => $headerValue, 'q' => $allowedQueriesRegFormat]
                    )
                );
            }
        }
    }

    /**
     * Get 'query' param
     *
     * @param HttpRequestInterface $request
     * @return string
     */
    private function getQueryDataFromRequest(HttpRequestInterface $request) : string
    {
        /** @var Http $request */
        if ($request->isPost()) {
            $query = $this->jsonSerializer->unserialize($request->getContent())['query'];
        } elseif ($request->isGet()) {
            $query = $request->getParam('query');
        } else {
            return '';
        }

        return $query;
    }
}
