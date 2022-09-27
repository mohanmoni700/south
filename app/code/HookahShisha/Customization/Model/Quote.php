<?php
namespace HookahShisha\Customization\Model;

class Quote
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Around getCreatedAtFormatted.
     *
     * @param \Amasty\RequestQuote\Model\Quote $subject
     * @param \Closure $proceed
     * @param int $format
     * @return date
     */

    public function aroundGetCreatedAtFormatted(
        \Amasty\RequestQuote\Model\Quote $subject,
        $proceed,
        $format
    ) {
        return $this->timezone->formatDateTime(
            new \DateTime($subject->getSubmitedDate()),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $subject->getStore())
        );
    }
}
