/**
 *  Amasty Scroll widget
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

 define([
    'jquery',
    'Amasty_Base/js/http_build_query',
    'uiRegistry',
    'underscore',
    'mage/cookies',
    'Magento_Ui/js/modal/modal'
], function ($, httpBuildQuery, uiRegistry, _) {

    $.widget('mage.amScrollScript', {
        options: {
            product_container: '.products.products-grid, .products.products-list, .products-grid.grid',
            product_link: '.product-item-link',
            footerSelector: '.page-footer'
        },
        type: 'auto',
        is_loading: 0,
        next_data_url: "",
        prev_data_url: "",
        next_data_cache: "",
        flag_next_cache: 0,
        prev_data_cache: "",
        flag_prev_cache: 0,
        pageFirst: 1,
        pagesCount: 1,
        pagesLoaded: [],
        currentPage: 1,
        last_scroll: 0,
        disabled: 0,
        additionalHeight: null,
        classes: {
            loadButton: 'amscroll-load-button',
            loadingBlock: 'amscroll-loading-block',
            loadingIcon: 'amscroll-loading-icon',
            loading: '-amscroll-loading',
            svgPathToFill: 'amscroll-fill-path',
            backToTopButton: 'amscroll-backtotop-block',
            backToTopEnabled: '-amscroll-backtotop-enabled',
            active: '-amscroll-active',
            text: 'amscroll-text',
            animate: '-amscroll-animate',
            pageNumber: 'amscroll-page-num'
        },
        pageSelector: '[amscroll-page="%1"]',
        lastPageSelector: '.amscroll-page:last, .amscroll-pages:last',
        totalAmountSelector: '.toolbar-amount',
        totalNumberSelector: '.toolbar-number',
        toolbarSelector: '.toolbar.toolbar-products',
        amPageCountSelector: '#am-page-count',
        cahShowAfter: false,
        canShowBefore: false,
        pagesBeforeButton: 0,
        isReinitialized: false,
        afterButton: null,
        beforeButton: null,
        insertedPages: 0,
        afterStepBackData: null,
        lazySelector: 'img.porto-lazyload:not(.porto-lazyload-loaded), img.lazy, img.lazyload',

        _create: function (options) {
            var self = this;

            $(document).on("amscroll_refresh", function () {
                self.initialize(); //run jQuery(document).trigger('amscroll_refresh');
            });

            $(window).bind("pageshow", function (event) {
                if (event.originalEvent.persisted) {
                    window.location.reload()
                }
            });

            this._initNodes();
            this.initialize();
        },

        _initNodes: function () {
            this.loadButtonElement = $('<button/>', {
                class: 'primary ' + this.classes.loadButton,
                style: this.options['loadNextStyle']
            });

            this.loadButtonTextElement = $('<span/>', {
                class: this.classes.text
            });

            this.loaderImage = $('<img/>', {
                class: this.classes.loadingIcon,
                src: this.options['loadingImage']
            });

            this.bodyElement = $('body');
            this.windowElement = $(window);
        },

        initialize: function () {
            var self = this,
                isValidConfiguration;

            this.next_data_cache = "";
            this.pagesLoaded = [];
            this._initPagesCount();
            this.disabled = 1;
            isValidConfiguration = this._validate();

            if (!isValidConfiguration) {
                $(this.classes.backToTopButton).remove(); //remove old nav bar

                return;
            }

            this.disabled = 0;
            this.type = this.options['actionMode'];
            this.pagesBeforeButton = this.options['pages_before_button'];
            this.currentPage = this._getCurrentPage();
            this.pagesLoaded.push(this.currentPage);

            if (this.type === 'button') {
                this._generateButton('before');
                this._generateButton('after');
            }

            this._preloadPages();
            this._hideToolbars();

            setTimeout(function () {
                $(window).scroll(_.debounce(self._initPaginator.bind(self), 50));
                self._initPaginator();
            }, 3000);

            this._initBackToTop();
            this.initPageStepForwardListener(this.currentPage);
            this.intialProductCount();
            this._pagePositionAfterStepBack();
        },

        /**
         * Validate init options
         *
         * @return {boolean}
         */
        _validate: function () {
            switch (true) {
                case !this.options['product_container'] || $(this.options['product_container']).length === 0 :
                    console.warn('Please specify "Products Group" DOM selector in module settings.');
                    return false;
                case !this.options['product_link'] || $(this.options['product_link']).length === 0 :
                    console.warn('Please specify "Product Link" DOM selector in module settings.');
                    return false;
                case !this.options['footerSelector'] || $(this.options['footerSelector']).length === 0 :
                    console.warn('Please specify "Footer Selector" DOM selector in module settings.');
                    return false;
                case this.pagesCount <= 1 :
                    return false;
            }

            return true;
        },

        /**
         * Сallback after inserting a new loaded page
         *
         * @param {jQuery|null} productContainer
         * @private
         */
        _externalAfterAjax: function (productContainer) {
            this.insertedPages++;
            try {
                if ($('head').html().indexOf('Infortis') > -1) {
                    $(document).trigger('last-swatch-found');
                }

                if (productContainer) {
                    var lazyImg = productContainer.find(this.lazySelector);
                    switch (true) {
                        case $.fn.lazyload instanceof Function:
                            lazyImg.lazyload({
                                effect: 'fadeIn'
                            });
                            break;
                        case $.fn.Lazy instanceof Function:
                            lazyImg.Lazy({
                                effect: 'fadeIn'
                            });
                            break;
                        case $.fn.unveil instanceof Function:
                            lazyImg.unveil();
                            break;
                    }
                }

                if (this.insertedPages >= this.pagesBeforeButton) {
                    switch (true) {
                        case this.type === 'auto' && this.options['origActionMode'] === 'combined':
                            this.changeType('button');
                            break;
                        case this.type === 'button' && this.options['origActionMode'] === 'combined_button_auto':
                            this.changeType('auto');
                            break;
                    }
                }
            } catch (e) {
                console.warn(e);
            }
        },

        /**
         * Сallback before inserting a new loaded page
         *
         * @param {jQuery} productBlock
         */
        beforeInsertProductBlock: function (productBlock) {
            //replace form keys for fpc compatibility
            productBlock.find('[name="form_key"]').val($.mage.cookies.get('form_key'));
        },

        /**
         * Endpoint to execute ajax request
         *
         * @param {String} requestUrl
         * @param {Function} successCallback
         */
        doAjax: function (requestUrl, successCallback) {
            return $.ajax({
                url: requestUrl,
                dataType: 'json',
                cache: false,
                success: this.preprocessRawAjaxResponse.bind(this, successCallback),
                error: this._stop.bind(this)
            });
        },

        /**
         * Prepare raw Ajax response
         *
         * @param {Function} successCallback
         * @param {Object|String} response
         */
        preprocessRawAjaxResponse: function (successCallback, response) {
            if (_.isString(response)) {
                try {
                    response = JSON.parse(response)
                } catch (e) {
                    return this.handleUnexpectedResponse(response);
                }
            }

            if (response !== null
                && response.hasOwnProperty('categoryProducts')
                && response.hasOwnProperty('currentPage')
                && response.categoryProducts.trim().length !== 0
            ) {
                successCallback.call(this, response);
            } else {
                this.handleUnexpectedResponse(response);
            }
        },

        /**
         * @param {Object|String|null} response
         */
        handleUnexpectedResponse: function (response) {
            console.warn('Amasty_Scroll: invalid response from server.', {response: response});
            this._stop();
        },

        _initPagesCount: function () {
            var amPager = $(this.amPageCountSelector),
                parent,
                childs,
                limit,
                allProducts,
                result;

            this.pagesLoaded = [];

            if (amPager && amPager.length) {
                this.pagesCount = parseInt(amPager.html());

                return;
            }

            parent = $(this.totalAmountSelector).first();

            if (parent) {
                childs = parent.find(this.totalNumberSelector);

                if (parent && childs.length >= 3) {
                    limit = $('#limiter').val();

                    if ($(childs[2]).text() > 0 && limit) {
                        allProducts = $(childs[2]).text();
                        result = Math.ceil(parseInt(allProducts) / parseInt(limit));

                        if (result > 1) {
                            this.pagesCount = result;

                            return;
                        }
                    }
                }
            }

            this.pagesCount = 1;
        },

        changeType: function (type) {
            if (this.isReinitialized) {
                return;
            }

            switch (type) {
                case 'button':
                    this.type = 'button';
                    this._generateButton('before');
                    this._generateButton('after');
                    break;
                case 'auto':
                    this.type = 'auto';
                    break;
            }

            this.isReinitialized = true;
        },

        _preloadPages: function () {
            var productContainer = $(this.options['product_container']),
                pageNumEl;

            if (productContainer.length > 1 && productContainer.first().parents('.widget').length) {
                productContainer = $(productContainer[1]);
            }

            productContainer.attr('amscroll-page', this.currentPage);
            productContainer.addClass('amscroll-page');

            if (this.options['pageNumbers'] === '1') {
                pageNumEl = this._generatePageTitle(this.currentPage);

                if (pageNumEl) {
                    productContainer.before(pageNumEl);
                }
            }

            this._preloadPageAfter(this.currentPage);
            this._preloadPageBefore(this.currentPage);
        },

        _getCurrentPage: function () {
            var currentPage = parseInt(this.options['current_page']);

            if (currentPage > this.pagesCount) {
                currentPage = this.pagesCount;
            }
            console.log('currentPage: ' + currentPage);
            return currentPage;
        },

        _preloadPageAfter: function (page) {
            var self = this,
                nextPage = page + 1;

            if (nextPage && nextPage <= this.pagesCount) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                self.flag_next_cache = 1;

                this.doAjax(this.next_data_url, function (data) {
                    self.flag_next_cache = 0;
                    self.next_data_cache = data;

                    self.showButton(self.afterButton);
                });

                this.next_data_url = '';
            }
        },

        _preloadPageBefore: function (page) {
            var self = this,
                prevPage = page - 1;

            if (prevPage && prevPage >= 1) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                self.flag_prev_cache = 1;

                this.doAjax(this.prev_data_url, function (data) {
                    self.flag_prev_cache = 0;
                    self.prev_data_cache = data;

                    self.showButton(self.beforeButton);
                });

                this.prev_data_url = '';
            }
        },

        _stop: function () {
            this.disabled = 1;
            this._showToolbars();
            $('.' + this.classes.loadingBlock).hide();
            $('.' + this.classes.backToTopButton).hide();
        },

        _getAdditionalBlockHeight: function () {
            if (this.additionalHeight === null) {
                var height = 0,
                    pageBottom = $('.page-bottom'),
                    blockAfterProducts = $('.main .products ~ .block-static-block');

                if (blockAfterProducts.length) {
                    height += blockAfterProducts.height();
                }

                if ($(this.options.footerSelector).length) {
                    height += $(this.options.footerSelector).height();
                }

                //for custom theme
                if (pageBottom.length > 0) {
                    height += pageBottom.first().height();
                }

                this.additionalHeight = height;
            }

            return this.additionalHeight;
        },

        _initPaginator: function () {
            if (this.disabled) {
                return;
            }

            var self = this,
                scroll_pos = this.windowElement.scrollTop(),
                diff = $(document).height() - this.windowElement.height();

            diff -= this._getAdditionalBlockHeight();
            diff = 0.9 * diff;

            if (scroll_pos < this.lastScrollPos) {
                this.isScrolledBack = true;
            }

            this.lastScrollPos = scroll_pos;

            if (scroll_pos >= diff && this.is_loading === 0) {
                this._loadFollowing();
            }

            if (scroll_pos <= this._getTopContainersHeight() && (this.is_loading === 0 && this._isScrolledBack())) {
                this._loadPrevious();
            }

            /*find current page and change url and scroll-bar*/
            this._calculateCurrentScrollPage(scroll_pos);

            // if we have enough room, load the next batch
            $(document).ready(function () {
                if (self.windowElement.height() > $(self.options['product_container']).height() && '' !== self.next_data_url) {
                    self._loadFollowing();
                }
            });
        },

        _isScrolledBack: function () {
            return this.isScrolledBack;
        },

        _calculateCurrentScrollPage: function (scroll_pos) {
            var self = this;

            if (Math.abs(scroll_pos - self.last_scroll) > self.windowElement.height() * 0.1) {
                self.last_scroll = scroll_pos;
                this._updateUrlAndCurrentPage();
            }
        },

        _updateUrlAndCurrentPage: function () {
            var self = this;

            $(self.options['product_container']).each(function (index) {
                if (self._mostlyVisible(this, index)) {
                    var page = parseInt($(this).attr('amscroll-page'));

                    if (page && page !== self.currentPage) {
                        var newUrl = self._generateUrl(page, 0);

                        if (!window.history.state || newUrl !== window.history.state.url) {
                            window.history.replaceState({url: newUrl}, '', newUrl);
                        }

                        self.currentPage = page;
                    }

                    return false;
                }
            });
        },

        _loadFollowing: function () {
            var self = this;

            if (this.flag_next_cache && this.type !== 'button') {
                this._createLoading('after');
            }

            if (this.next_data_url !== "" || this.next_data_cache) {
                if (this.type !== 'button') {
                    this._createLoading('after');
                }

                if (this.next_data_cache) {
                    this.showFollowing(this.next_data_cache);
                } else {
                    if (!this.flag_next_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond

                        this.doAjax(this.next_data_url, function (data) {
                            self.showFollowing(data);
                        });
                    }
                }
            }
        },

        showFollowing: function (data) {
            if (data.categoryProducts) {
                if (this.type === 'button') {
                    if (this.cahShowAfter) {
                        this.is_loading = 0;
                        this.cahShowAfter = false;
                    } else {
                        return;
                    }
                }

                this.next_data_url = '';
                this.next_data_cache = false;
                this._insertNewProductBlock(data, 'after');
                this._afterShowFollowing();
            }
        },

        _afterShowFollowing: function () {
            var self = this,
                nextPage = $(this.pagesLoaded).get(-1) + 1; //last + 1

            if (nextPage && nextPage <= this.pagesCount && $.inArray(nextPage, this.pagesLoaded) === -1) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                this.flag_next_cache = 1;

                this.doAjax(this.next_data_url, function (preview_data) {
                    self.flag_next_cache = 0;
                    self.next_data_cache = preview_data;
                    self.windowElement.scroll();

                    self.showButton(self.afterButton);
                });
            }

            this.is_loading = 0;
        },

        _loadPrevious: function () {
            var self = this;

            if (this.flag_prev_cache && this.type !== 'button') {
                this._createLoading('before');
            }

            if (this.prev_data_url !== "" || this.prev_data_cache) {
                if (this.type !== 'button') {
                    this._createLoading('before');
                }

                if (this.prev_data_cache) {
                    this.showPrevious(this.prev_data_cache);
                } else {
                    if (!this.flag_prev_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond

                        this.doAjax(this.prev_data_url, function (data) {
                            self.showPrevious(data);
                        });
                    }
                }
            }
        },

        showPrevious: function (data) {
            if (data.categoryProducts) {
                if (this.type === 'button') {
                    if (this.canShowBefore) {
                        this.is_loading = 0;
                        this.canShowBefore = false;
                    } else {
                        return;
                    }
                }

                this.prev_data_cache = false;
                this.prev_data_url = '';
                this._insertNewProductBlock(data, 'before');
                this._afterShowPrevious();
            }
        },

        _afterShowPrevious: function () {
            var self = this,
                prevPage = $(this.pagesLoaded).get(0) - 1;

            if (prevPage && prevPage >= 1 && $.inArray(prevPage, this.pagesLoaded) === -1) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                this.flag_prev_cache = 1;

                this.doAjax(this.prev_data_url, function (preview_data) {
                    self.flag_prev_cache = 0;
                    self.prev_data_cache = preview_data;
                    self.windowElement.scroll();

                    self.showButton(self.beforeButton);
                });
            }
            this.is_loading = 0;
        },

        _createLoading: function (position) {
            var elementSelector = '.' + this.classes.loadingBlock,
                pageNumberSelector = '.' + this.classes.pageNumber,
                productContainer = this.options['product_container'],
                lastSelector = '.amscroll-page:last ~ ' + elementSelector,
                loadingElement = $('<div/>', {
                    class: this.classes.loadingBlock,
                    html: this.loaderImage.clone()
                }),
                element;

            this.imgToSvg(loadingElement.find('.' + this.classes.loadingIcon));

            if ('after' === position && $(lastSelector).length === 0) {
                $(productContainer).last().after(loadingElement);
            } else if ($(elementSelector).not(lastSelector).length === 0) {
                element = $(pageNumberSelector + ', ' + productContainer).first();

                element.before(loadingElement);
            }

            $(elementSelector).next(elementSelector).remove();
        },

        _generateButton: function (position) {
            if (this.type !== 'button') {
                return;
            }

            if ((position === 'before' && this.pagesLoaded.indexOf(1) !== -1
                && ($(this.pageSelector.replace('%1', this.pageFirst)).length || this.currentPage === this.pageFirst))
            ) {
                return;
            }

            if (position === 'after' && this.pagesLoaded.indexOf(this.pagesCount) !== -1
                && ($(this.pageSelector.replace('%1', this.pagesCount)).length || this.currentPage === this.pagesCount)) {
                return;
            }

            var self = this,
                buttonElement = this.loadButtonElement.clone(),
                textElement = this.loadButtonTextElement.clone(),
                color = this.options['buttonColor'];

            textElement.text(this.options['loading' + position + 'TextButton']);

            buttonElement
                .prepend(this.loaderImage.clone())
                .append(textElement)
                .css({'color': color, 'borderColor': color})
                .attr('amscroll_type', position)
                .addClass('-' + position)
                .hide();

            this.imgToSvg(buttonElement.find('.' + this.classes.loadingIcon));

            if (position === 'after') {
                if (this.afterButton) {
                    this.afterButton.remove();
                }
                this.afterButton = buttonElement;
                this._insertBlockInTheEnd(buttonElement);
            } else {
                if (this.beforeButton) {
                    this.beforeButton.remove();
                }
                this.beforeButton = buttonElement;
                this._insertBlockInTheBegin(buttonElement);
            }

            $('.' + this.classes.loadButton + '[amscroll_type="' + position + '"]').on('click', function (item) {
                var totalProductCount = parseInt(jQuery("#totalProductCount").text());
                var productCount = parseInt(jQuery("#productCount").text());
                var InitialproductCount = parseInt(jQuery(".initial-product-count").text());
                var staticIncrement = 12;
                if (totalProductCount - productCount >= InitialproductCount) {
                    jQuery("#productCount").text(productCount+InitialproductCount);
                } else {
                    jQuery("#productCount").text(totalProductCount);
                }
                self.buttonClick(item);
            });
        },

        intialProductCount: function() {
            var initialproductCount = parseInt(jQuery("#productCount").text());
            const initialProductCountdiv = document.createElement('div');
            initialProductCountdiv.classList.add('initial-product-count');
            initialProductCountdiv.textContent = initialproductCount;
            initialProductCountdiv.style.display = "none";
            document.getElementById("html-body").appendChild(initialProductCountdiv);
        },

        showButton: function (buttonElement) {
            if (buttonElement) {
                buttonElement.show();
            }
        },

        imgToSvg: function (element) {
            var image = $(element),
                imgId = image.attr('id'),
                imgClass = image.attr('class'),
                imgUrl = image.attr('src'),
                classList = [imgClass, this.classes.animate],
                svgElement;

            if (imgUrl.match(/.*\.svg$/)) {
                image.hide();

                $.get(imgUrl, function (data) {
                    svgElement = $(data).find('svg');

                    this.setSvgColor(svgElement);

                    if (typeof imgId !== 'undefined') {
                        svgElement = svgElement.attr('id', imgId);
                    }

                    if (typeof imgClass !== 'undefined') {
                        svgElement = svgElement.attr('class', classList.join(' '));
                    }

                    svgElement = svgElement.removeAttr('xmlns:a');
                    image.replaceWith(svgElement);
                }.bind(this), 'xml');
            }
        },

        setSvgColor: function (element) {
            var svgPathElement = element.find('.' + this.classes.svgPathToFill);

            if (svgPathElement) {
                svgPathElement.attr('fill', this.options['buttonColorPressed']);
            }
        },

        buttonClick: function (event) {
            var element = $(event.target),
                type = element.attr('amscroll_type');

            element.addClass(this.classes.loading).css('color', this.options['buttonColorPressed']);

            if (type === 'after') {
                this.cahShowAfter = true;
                this._loadFollowing();
            } else {
                this.canShowBefore = true;
                this._loadPrevious();
            }

            this._updateUrlAndCurrentPage();
        },

        /**
         *
         * @param {Object} data
         * @param {String} position
         * @private
         */
        _insertNewProductBlock: function (data, position) {
            var html = data.categoryProducts,
                tmp = $('<div/>').append(html),
                productContainer = tmp.find(this.options['product_container']);

            tmp.find(this.toolbarSelector).remove();
            tmp.find('.amasty-catalog-topnav').remove();//remove navigation top block
            productContainer.addClass('amscroll-pages').attr('amscroll-page', data.currentPage);
            this.beforeInsertProductBlock(productContainer);

            if (this.options['pageNumbers'] == '1') {
                var pageNumEl = this._generatePageTitle(data.currentPage);
                if (pageNumEl) {
                    productContainer.before(pageNumEl);
                }
            }

            html = tmp.html();

            if ('after' == position) {
                $('.amscroll-page:last ~ .' + this.classes.loadButton).remove();
                $('.amscroll-page:last ~ .' + this.classes.loadingBlock).remove();
                this._insertBlockInTheEnd(html);
            } else {
                var element = this._insertBlockInTheBegin(html),
                    item_height = element.height();

                if (this.type != 'button') {
                    window.scrollTo(0, $(window).scrollTop() + item_height);
                }
            }

            this._addObserverToProductLink($('.amscroll-pages[amscroll-page="' + data.currentPage + '"]'));
            if (this.type == 'button') {
                this._generateButton(position);
            }

            this.updateMultipleWishlist();
            this.initPageStepForwardListener(data.currentPage);
        },

        updateMultipleWishlist: function () {
            var wishLists = uiRegistry.get('multipleWishlist');
            if (wishLists) {
                this.bodyElement.off('click', '[data-post-new-wishlist]');
                uiRegistry.get('multipleWishlist').initialize();
            }
        },

        _addObserverToProductLink: function (productContainer) {
            var self = this;
            this._externalAfterAjax(productContainer);
            productContainer.find('.item a').on("click", function (event) {
                try {
                    var parent = $(this).parents('.amscroll-pages').first();
                    var page = parent ? parent.attr('amscroll-page') : null;
                    if (page) {
                        var newUrl = self._generateUrl(page, 0);
                        if (!window.history.state || newUrl !== window.history.state.url) {
                            window.history.replaceState({url: newUrl}, '', newUrl);
                        }
                    }
                } catch (e) {
                }
            });

            productContainer.first().trigger('contentUpdated');
            if ($.fn.applyBindings != undefined) {
                productContainer.first().applyBindings();
            }
        },

        _generateUrl: function (page, addScroll) {
            var url = window.location.href,
                params = this._getQueryParams(window.location.search);

            url = url.replace(window.location.search, '');
            if (!params || !Object.keys(params).length) {
                if (page) {
                    var paramString = '?p=' + page;
                    if (addScroll) {
                        paramString += '&is_scroll=1';
                    }

                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', paramString + '#')
                    } else {
                        url += paramString;
                    }
                }
            } else {
                if (page && parseInt(page) > 1) {
                    params['p'] = page;
                } else if (params['p']) {
                    delete params['p'];
                }

                if (addScroll) {
                    params['is_scroll'] = 1;
                }

                if (Object.keys(params).length) {
                    params = httpBuildQuery(params);
                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', '?' + params + '#')
                    } else {
                        url += '?' + params;
                    }
                }
            }

            return url;
        },

        _getQueryParams: function (url) {
            url = url.split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(url)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        },

        _hideToolbars: function () {
            $(this.totalAmountSelector).hide();
            $('.products ~ ' + this.toolbarSelector).hide();
        },

        _showToolbars: function () {
            $(this.totalAmountSelector).show();
            $('.products ~ ' + this.toolbarSelector).show();
        },

        _generatePageTitle: function (page) {
            if ($('#amscroll-page-num' + page).length) {
                return false;
            }

            pageNumEl = $('<div/>', {
                class: 'amscroll-page-num -amscroll-' + this.options['pageNumberStyle'],
                id: 'amscroll-page-num' + page,
                text: this.options['pageNumberContent'] + page
            });

            return pageNumEl;
        },

        _mostlyVisible: function (element, index) {
            element = $(element);
            var visible = element.is(":visible"),
                scroll_pos = $(window).scrollTop(),
                window_height = $(window).height(),
                el_top = element.offset().top,
                el_height = element.height(),
                el_bottom = el_top + el_height;

            return ((el_bottom - el_height * 0.25 > scroll_pos)
                && (el_top < (scroll_pos + 0.5 * window_height))
                && visible) || (index == 0 && scroll_pos < el_top);
        },

        _getTopContainersHeight: function () {
            if (!this.topContainersHeight) {
                var result = $(".page-header").height() + $(".nav-sections").height();
                if ($(".main .block-static-block ~ .products,  .main .block-static-block ~ #amasty-shopby-product-list").length) {
                    result += $(".main .block-static-block").height();
                }
                this.topContainersHeight = 0.9 * result;
            }

            return this.topContainersHeight;
        },

        _initBackToTop: function () {
            var self = this,
                data = this.options['backToTop'],
                classes = this.classes,
                classList,
                backButtonElement,
                elementInner;

            if (data && data['enabled'] === '1') {
                classList = [
                    classes.backToTopButton,
                    '-desktop-' + data['style_desktop'],
                    '-mobile-' + data['style_mobile']
                ];
                backButtonElement = $('<button/>', {
                    class: classList.join(' '),
                    id: classes.backToTopButton
                });
                elementInner = $('<span>', {
                    class: classes.text,
                    text: this.options['backToTopText']
                });

                backButtonElement
                    .css('background', data['color'])
                    .append(elementInner)
                    .on('click', this._scrollToTop.bind(this));

                this.bodyElement.addClass(classes.backToTopEnabled).append(backButtonElement);

                this.windowElement.scroll(_.debounce(function () {
                    backButtonElement.toggleClass(classes.active, self.windowElement.scrollTop() > 400);
                }, 100));
            }
        },

        _scrollToTop: function () {
            $('body, html').animate({scrollTop: 0}, 300);
        },

        _insertBlockInTheEnd: function (block) {
            $(this.options['product_container']).last().after(block);
        },

        _insertBlockInTheBegin: function (block) {
            var element = $('.' + this.classes.pageNumber + ', ' + this.options['product_container']).first(),
                loadButtonSelector = '.' + this.classes.loadButton,
                loadingBlockSelector = '.' + this.classes.loadingBlock,
                lastPage = $(this.lastPageSelector),
                lastButton = lastPage.next(loadButtonSelector),
                lastLoadingBLock = lastPage.next(loadingBlockSelector);

            $(loadButtonSelector).not(lastButton).remove();
            $(loadingBlockSelector).not(lastLoadingBLock).remove();
            element.before(block);

            return element;
        },

        getCurrentUrl: function () {
            return location.href.split('?')[0];
        },

        /**
         * Get saved scroll settings after go back
         *
         * @return {Array|null|false}
         */
        getSavedAfterStepBackData: function () {
            if (this.afterStepBackData === null) {
                var savedData = sessionStorage.getItem('am-scroll-go-back-data');

                if (savedData) {
                    savedData = JSON.parse(savedData);
                    this.afterStepBackData = savedData.pageUrl === this.getCurrentUrl() ? savedData : false;
                    sessionStorage.removeItem('am-scroll-go-back-data');
                }
            }

            return this.afterStepBackData;
        },

        initPageStepForwardListener: function (currentPage) {
            var self = this;

            $('[amscroll-page="' + currentPage + '"] .product-item').on('click touchstart', function (e) {
                if (!$(e.target).is('a, button, img')) {
                    //prevent save data after swatch select for example
                    return;
                }

                var scrollPositionAfterStepBackData = {
                    pageUrl: self.getCurrentUrl(),
                    clickedProductLink: $(this).find(self.options['product_link']).first().attr('href')
                };
                sessionStorage.setItem('am-scroll-go-back-data', JSON.stringify(scrollPositionAfterStepBackData));
            });
        },

        // Fix an issue with scroll position after step back to product listing
        _pagePositionAfterStepBack: function () {
            var savedScrollData = this.getSavedAfterStepBackData();

            if (savedScrollData) {
                var productLinkSelector = 'a[href="' + savedScrollData.clickedProductLink + '"]',
                    item = $(productLinkSelector).closest('.product-item').first();
                history.scrollRestoration = 'auto';

                if (item.length) {
                    history.scrollRestoration = 'manual';
                    $('html, body').animate({
                        scrollTop: (item.first().offset().top)
                    }, 500);
                }
            }
        }
    });

    return $.mage.amScrollScript;
});
