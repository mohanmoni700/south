<?php

declare(strict_types = 1);

namespace Alfakher\Tabby\Plugin\Model\Resolver;

use Alfakher\Tabby\Model\TabbySession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as Subject;

class TabbyPlaceOrder
{
    /**
     * @var TabbySession
     */
    private $tabbySession;

    /**
     * @param TabbySession $tabbySession
     */
    public function __construct(
        TabbySession $tabbySession
    ) {
        $this->tabbySession = $tabbySession;
    }

    /**
     * After resolve
     *
     * @param Subject          $subject
     * @param mixed            $result
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return mixed
     */
    public function afterResolve(
        Subject     $subject,
                    $result,
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $redirectUrl = $this->tabbySession->getTabbyRedirectUrl();

        if (!empty($redirectUrl)) {
            $result['order']['tabby_redirect_url'] = $redirectUrl;
        }

        return $result;
    }
}
