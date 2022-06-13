<?php

namespace WMZ\DeleteOrder\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use WMZ\DeleteOrder\Controller\Adminhtml\AbstractDelete;

class Index extends AbstractDelete
{
    const ORDERS_GRID_PATH = 'sales/order/index';

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!isset($orderId)) {
            $this->messageManager->addErrorMessage(__('Please Select an Order'));
            return $this->resultRedirectFactory->create()->setPath(
                self::ORDERS_GRID_PATH
            );
        }
        try {
            $orderData = $this->orderRepository->get($orderId);
            $this->deleteOrder($orderData);
            $this->messageManager->addSuccessMessage(
                __('The Order Against Id ' . $orderId . ' has been Removed')
            );
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                $exception->getMessage()
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            self::ORDERS_GRID_PATH
        );
    }
}
