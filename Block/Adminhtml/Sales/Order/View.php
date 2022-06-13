<?php

namespace WMZ\DeleteOrder\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View extends OrderView
{
    const DELETE_ORDER_PATH = 'delete/order/index';
    const ORDER_DELETE_BUTTON = 'delete_button';

    /**
     * Adding the Delete Order Button in Order Detail Page
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addButton(
            self::ORDER_DELETE_BUTTON,
            [
                'label' => 'Delete Order',
                'class' => '',
                'onclick' => 'deleteConfirm(\'' .
                    __('Do you want to delete this order?') . '\', \'' .
                    $this->getDeleteOrderUrl() . '\')'
            ]
        );
    }

    /**
     * @return string
     */
    public function getDeleteOrderUrl()
    {
        return $this->getUrl(self::DELETE_ORDER_PATH, ['_current' => true]);
    }
}
