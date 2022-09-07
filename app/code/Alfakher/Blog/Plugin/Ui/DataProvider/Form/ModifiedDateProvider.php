<?php
namespace Alfakher\Blog\Plugin\Ui\DataProvider\Form;

use Magento\Framework\App\RequestInterface;

/**
 * Class Data provider
 */
class ModifiedDateProvider
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get Meta data
     *
     * @param \Magefan\Blog\Ui\DataProvider\Post\Form\PostDataProvider $subject
     * @param array $result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetMeta(
        \Magefan\Blog\Ui\DataProvider\Post\Form\PostDataProvider $subject,
        $result
    ) {
        $id = $this->request->getParam('id');
        if (!empty($id)) {
            $result['additional_options']['children']['publish_time']['arguments']['data']['config']['disabled'] = 1;
        }
        return $result;
    }
}
