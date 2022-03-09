<?php

namespace Alfakher\Blog\Block\Post\View;

use Magefan\Blog\Block\Post\View\Opengraph;
use Magento\Store\Model\ScopeInterface;

/**
 * Blog post custom view rich snippets
 */
class Richsnippets extends Opengraph
{
    /**
     * @param  array
     */
    protected $_options;

    /**
     * Retrieve snipet params
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->_options === null) {
            $post = $this->getPost();

            $logoBlock = $this->getLayout()->getBlock('logo');
            if (!$logoBlock) {
                $logoBlock = $this->getLayout()->getBlock('amp.logo');
            }

            $this->_options = [
                '@context' => 'http://schema.org',
                '@type' => 'Article',
                "mainEntityOfPage" => [
                    '@type' => 'WebPage',
                    '@id' => $post->getPostUrl(),
                ],
                'headline' => $this->getTitle(),
                'image' => $this->getImage() ?:
                ($logoBlock ? $logoBlock->getLogoSrc() : ''),
                'author' => [
                    "@type" => "Person",
                    "name" => $this->getAuthor(),
                    "url" => $this->getAuthorUrl(),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $this->getPublisher(),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $logoBlock ? $logoBlock->getLogoSrc() : '',
                    ],
                ],
                'datePublished' => $post->getPublishDate('c'),
            ];
        }

        return $this->_options;
    }

    /**
     * Retrieve author name
     *
     * @return array
     */
    public function getAuthor()
    {
        if ($author = $this->getPost()->getAuthor()) {
            if ($author->getTitle()) {
                return $author->getTitle();
            }
        }

        // if no author name return name of publisher
        return $this->getPublisher();
    }

    /**
     * Retrieve publisher name
     *
     * @return array
     */
    public function getPublisher()
    {
        $publisher = $this->_scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        if (!$publisher) {
            $publisher = 'Magento2 Store';
        }

        return $publisher;
    }

    /**
     * Render html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '<script type="application/ld+json">'
        . json_encode($this->getOptions())
            . '</script>';
    }
}
