<?php

namespace Alfakher\MyDocument\Controller\Adminhtml\Document;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     *
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param DirectoryList $directory
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\App\Action\Context $context,
        DirectoryList $directory,
        array $data = []
    ) {
        $this->fileFactory = $fileFactory;
        $this->directory = $directory;
        parent::__construct($context);
    }

    /**
     * Execute MyDocument

     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $post = (array) $this->getRequest()->getParams();
        $filename = ($post['filename']);
        $filePath = 'media/myDocument/' . $filename;
        $downloadName = $filename;
        $content['type'] = 'filename';
        $content['value'] = $filePath;
        $content['rm'] = 0; // If you will set here 1 then, it will remove file from location.
        $test = $this->fileFactory->create($downloadName, $content, DirectoryList::PUB);
        return $test;
    }
}
