<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SwatchesGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider;

use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media as SwatchesMedia;
use Magento\Swatches\Model\Swatch;
use Magento\SwatchesGraphQl\Model\Resolver\Product\Options\DataProvider\SwatchDataProvider as SourceSwatchDataProvider;

class SwatchDataProvider extends SourceSwatchDataProvider
{
    /**
     * @var SwatchData
     */
    private SwatchData $swatchHelper;

    /**
     * @var SwatchesMedia
     */
    private SwatchesMedia $swatchMediaHelper;

    /**
     * @param SwatchData $swatchHelper
     * @param SwatchesMedia $swatchMediaHelper
     */
    public function __construct(SwatchData $swatchHelper, SwatchesMedia $swatchMediaHelper)
    {
        parent::__construct($swatchHelper, $swatchMediaHelper);

        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
    }

    /**
     * Returns swatch data by option ID.
     *
     * @param string $optionId
     * @return array|null
     */
    public function getData(string $optionId): ?array
    {
        $swatches = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);

        if ((isset($swatches[$optionId]['type']) && $swatches[$optionId]['type'] == Swatch::SWATCH_TYPE_EMPTY)
            || !isset($swatches[$optionId]['type'], $swatches[$optionId]['value'])
        ) {
            // If swatch is empty, return no data to type resolver to not get an error
            return null;
        }

        $value = $swatches[$optionId]['value'];
        $type = (int)$swatches[$optionId]['type'];
        $data = ['value' => $value, 'type' => $type];
        if ($type === Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $data['thumbnail'] = $this->swatchMediaHelper->getSwatchAttributeImage(
                Swatch::SWATCH_THUMBNAIL_NAME,
                $value
            );
        }
        return $data;
    }
}
