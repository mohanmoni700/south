<?php

namespace HookahShisha\SwatchData\Model\Config\Backend;

class SwatchFileType extends \Magento\Config\Model\Config\Backend\File
{
    /** @var \Magento\Framework\App\State */
    protected $state;

    /** @var \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository */
    protected $attributeRepository;

    /** @var \Magento\Framework\Filesystem  */
    protected $filesystem;

    /** @var \Magento\Swatches\Helper\Media */
    protected $swatchHelper;

    /** @var \Magento\Catalog\Model\Product\Media\Config */
    protected $productMediaConfig;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $driverFile;

    protected $csv;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\File\Csv $csv,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->state = $state;
        $this->attributeRepository = $attributeRepository;
        $this->filesystem = $filesystem;
        $this->swatchHelper = $swatchHelper;
        $this->productMediaConfig = $productMediaConfig;
        $this->driverFile = $driverFile;
        $this->csv = $csv;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
    }

    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['csv'];
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        $fileValue = $this->getValue();
        $options = $this->readOptions($fileValue);

        $attributesOptionsData = [
            'color' => [
                \Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_KEY => \Magento\Swatches\Model\Swatch::SWATCH_INPUT_TYPE_VISUAL,
                'optionvisual' => [
                    'value' => $options['option'],
                ],
                'swatchvisual' => [
                    'value' => $options['swatch'],
                ],
            ],
        ];

        $this->addProductAttributes($attributesOptionsData);
        return parent::afterSave();
    }

    /**
     * @return $this
     */
    public function readOptions($fileValue)
    {
        $optionsData = $swatchOptions = [];
        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $file = $mediaDirectory . 'Swatch' . DIRECTORY_SEPARATOR . $fileValue;
        $csvData = $this->csv->getData($file);

        $value = '';
        $key = '';
        foreach ($csvData as $row => $data) {
            if ($row > 0) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', 'color');
                $options = $attribute->getSource()->getAllOptions(false);

                foreach ($options as $option) {
                    if ($data[0] == $option['label']) {
                        $key = $option['value'];
                        break;
                    }
                }
                if ($key) {
                    if ($data[0]) {
                        $value = $data[0];
                    }
                    if ($data[1]) {
                        $valueArr = [];
                        $valueArr[] = $value;
                        $valueArr[] = $data[1];
                        $optionsData[$key] = $valueArr;
                        $value = '';
                    }
                    if ($data[2]) {
                        $swatchOptions[$key] = $data[2];
                    }
                }

            }
        }
        $optionValues['option'] = $optionsData;
        $optionValues['swatch'] = $swatchOptions;
        return $optionValues;
    }

    /**
     * @return $this
     */
    public function addProductAttributes($attributesOptionsData)
    {
        // Add order if it doesn't exist. This is an important step to make sure everything will be created correctly.
        foreach ($attributesOptionsData as &$attributeOptionsData) {
            $order = 0;
            $swatchVisualFiles = isset($attributeOptionsData['optionvisual']['value'])
            ? $attributeOptionsData['optionvisual']['value']
            : [];
            foreach ($swatchVisualFiles as $index => $swatchVisualFile) {
                if (!isset($attributeOptionsData['optionvisual']['order'][$index])) {
                    $attributeOptionsData['optionvisual']['order'][$index] = ++$order;
                }
            }
        }
        unset($attributeOptionsData);

        // Prepare visual swatches files.
        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $tmpMediaPath = $this->productMediaConfig->getBaseTmpMediaPath();
        $fullTmpMediaPath = $mediaDirectory->getAbsolutePath($tmpMediaPath);
        $this->driverFile->createDirectory($fullTmpMediaPath);
        foreach ($attributesOptionsData as &$attributeOptionsData) {
            $swatchVisualFiles = $attributeOptionsData['swatchvisual']['value'] ?? [];
            foreach ($swatchVisualFiles as $index => $swatchVisualFile) {
                $this->driverFile->copy(
                    $mediaDirectory->getAbsolutePath() . 'Swatch' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $swatchVisualFile,
                    $fullTmpMediaPath . DIRECTORY_SEPARATOR . $swatchVisualFile
                );
                $newFile = $this->swatchHelper->moveImageFromTmp($swatchVisualFile);
                if (substr($newFile, 0, 1) == '.') {
                    $newFile = substr($newFile, 1); // Fix generating swatch variations for files beginning with ".".
                }
                $this->swatchHelper->generateSwatchVariations($newFile);
                $attributeOptionsData['swatchvisual']['value'][$index] = $newFile;
            }
        }
        unset($attributeOptionsData);

        // Add attribute options.
        foreach ($attributesOptionsData as $code => $attributeOptionsData) {
            /* @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->attributeRepository->get($code);
            $attribute->addData($attributeOptionsData);
            $attribute->save();
        }
    }
}
