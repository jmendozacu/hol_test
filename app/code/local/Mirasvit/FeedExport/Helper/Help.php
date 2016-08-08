<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced Product Feeds
 * @version   1.1.2
 * @revision  284
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_FeedExport_Helper_Help extends Mirasvit_MstCore_Helper_Help
{
    protected $_help = array(
        'feed_edit_tab_general' => array(
            'name'     => 'The name of the feed',
            'filename' => 'The name of data feed file.
                File will be located at media/feed/[Filename][File Type]',
            'type'     => 'The type of the feed:
                CSV - a comma-separated values, each item placed on a new line. File extension is .csv.
                TXT - same as CSV file, but with .txt extension.
                XML - uses tags to define blocks of content. Information about your items is enclosed within these tags, which are indicated by angle brackets. File extension is .xml.',
        ),
        'feed_edit_tab_ga' => array(
            'ga_source'  => 'Required. referrer: google, citysearch, newsletter4',
            'ga_medium'  => 'Required. marketing medium: cpc, banner, email',
            'ga_term'    => 'Identify the paid keywords',
            'ga_content' => 'Use to differentiate ads',
            'ga_name'    => 'Required. Product, promo code, or slogan',
        ),
        'feed_edit_tab_cron' => array(
            'cron'  => 'If enabled, extensin will generate feed by schedule.
                 To generate feed by shedule, magento cron must be configured.',
        )
    );
}