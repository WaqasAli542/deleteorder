<?php

namespace WMZ\DeleteOrder\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use WMZ\DeleteOrder\Controller\Adminhtml\Order\Index;

abstract class AbstractDelete extends Action
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditMemoRepository;

    /**
     * @var TransactionSearchResultInterfaceFactory
     */
    protected $transactionSearchResultInterfaceFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * AbstractDelete constructor.
     * @param Context $context
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditMemoRepository
     * @param TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditMemoRepository,
        TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory,
        TransactionRepositoryInterface $transactionRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->transactionSearchResultInterfaceFactory = $transactionSearchResultInterfaceFactory;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('WMZ_DeleteOrder::delete_order');
    }

    /**
     * @param $order
     * @return bool|\Magento\Framework\Controller\Result\Redirect
     */
    protected function deleteOrder($order)
    {
        /** @var OrderInterface $order */
        $orderId = $order->getEntityId();
        /** @var InvoiceInterface $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceId = $invoice->getEntityId();
            if (isset($invoiceId)) {
                $this->removeInvoices($invoiceId);
            }
        }
        /** @var ShipmentInterface $shipment */
        foreach ($order->getShipmentsCollection() as $shipment) {
            $shipmentId = $shipment->getEntityId();
            if (isset($shipmentId)) {
                $this->removeShipments($shipment->getEntityId());
            }
        }
        /** @var CreditmemoInterface $creditMemo */
        if ($order->hasCreditmemos()) {
            foreach ($order->getCreditmemosCollection() as $creditMemo) {
                $creditMemoId = $creditMemo->getEntityId();
                if (isset($creditMemoId)) {
                    $this->removeCreditMemo($creditMemoId);
                }
            }
        }
        /** @var TransactionSearchResultInterface $transactions */
        $transactions = $this->transactionSearchResultInterfaceFactory->create()
            ->addOrderIdFilter(
                $order->getEntityId()
            );
        foreach ($transactions->getItems() as $transaction) {
            $transactionId = $transaction->getTransactionId();
            if (isset($transactionId)) {
                $this->removeTransaction($transactionId);
            }
        }
        if (empty($orderId)) {
            $this->messageManager->addErrorMessage('There is no order to process');
            return $this->resultRedirectFactory->create()->setPath(
                Index::ORDERS_GRID_PATH
            );
        }
        try {
            $this->orderRepository->deleteById($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return true;
    }

    /**
     * @param $invoiceId
     */
    public function removeInvoices($invoiceId)
    {
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $this->invoiceRepository->delete($invoice);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $shipmentId
     */
    public function removeShipments($shipmentId)
    {
        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $this->shipmentRepository->delete($shipment);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $creditMemoId
     */
    public function removeCreditMemo($creditMemoId)
    {
        try {
            $creditMemo = $this->creditMemoRepository->get($creditMemoId);
            $this->creditMemoRepository->delete($creditMemo);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $transactionId
     */
    public function removeTransaction($transactionId)
    {
        try {
            $transaction = $this->transactionRepository->get($transactionId);
            $this->transactionRepository->delete($transaction);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
