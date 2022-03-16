<?php

namespace Alfakher\DocumentStatus\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class CustomerDataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * CustomerDataProvider
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param AttributeRepository $attributeRepository
     * @param array $customerCollection
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        AttributeRepository $attributeRepository,
        Customer $customerCollection,
        array $meta = [],
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->customerCollection = $customerCollection;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $attributeRepository,
            $meta,
            $data,
        );
    }
    /**
     * AddFilter
     *
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'docuement_status') {
            $filterOrderValue = $filter->getValue();
        } else {
            parent::addFilter($filter);
        }
    }
    /**
     * Get Data
     *
     * @return array getData
     */
    public function getData()
    {
        $data = parent::getData();
        return $data;
    }
}
