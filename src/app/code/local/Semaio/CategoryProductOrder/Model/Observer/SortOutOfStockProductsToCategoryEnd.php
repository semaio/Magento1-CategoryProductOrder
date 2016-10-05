<?php
/**
 * This file is part of the Semaio_CategoryProductOrder module.
 *
 * See LICENSE.md bundled with this module for license details.
 *
 * @category  Semaio
 * @package   Semaio_CategoryProductOrder
 * @author    semaio GmbH <hello@semaio.com>
 * @copyright 2016 semaio GmbH (http://www.semaio.com)
 */

/**
 * Class Semaio_OrderCategoryProducts_Model_Observer_SortOutOfStockProductsToCategoryEnd
 */
class Semaio_CategoryProductOrder_Model_Observer_SortOutOfStockProductsToCategoryEnd
{
    /**
     * Sort out of stock products to end of category
     *
     * @param Varien_Event_Observer $observer Observer
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Block_Product_List_Toolbar $toolbar */
        $toolbar = Mage::getBlockSingleton('catalog/product_list_toolbar');
        if ($toolbar) {
            /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
            $collection = $observer->getEvent()->getCollection();

            $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
            $websiteId = Mage::app()->getStore($collection->getStoreId())->getWebsiteId();

            $collection->getSelect()->joinLeft(
                array('ciss' => $collection->getResource()->getTable('cataloginventory/stock_status')),
                "ciss.product_id = e.entity_id and ciss.website_id=$websiteId and ciss.stock_id=$stockId",
                array('stock_status')
            );
            $collection->addExpressionAttributeToSelect('in_stock', 'IFNULL(ciss.stock_status,0)', array());

            $collection->getSelect()->reset('order');
            $collection->getSelect()->order('in_stock DESC');

            if ($toolbar->getCurrentOrder()) {
                $collection->addAttributeToSort($toolbar->getCurrentOrder(), $toolbar->getCurrentDirection());
            }
        }
    }
}
