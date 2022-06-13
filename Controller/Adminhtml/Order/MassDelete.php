<?php

namespace WMZ\DeleteOrder\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use WMZ\DeleteOrder\Controller\Adminhtml\AbstractDelete;

class MassDelete extends AbstractDelete
{
    /**
     * @var string
     */
    const REDIRECT_URL = '*/*/';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var object
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        TransactionSearchResultInterfaceFactory $transactionSearchResultInterfaceFactory,
        TransactionRepositoryInterface $transactionRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct(
            $context,
            $invoiceRepository,
            $shipmentRepository,
            $creditmemoRepository,
            $transactionSearchResultInterfaceFactory,
            $transactionRepository,
            $orderRepository
        );
        $this->filter=$filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param AbstractCollection $collection
     * @return RedirectController
     */
    protected function massAction(AbstractCollection $collection)
    {
        $count=0;
        foreach ($collection->getItems() as $order) {
            $this->deleteOrder($order);
            $count++;
        }
        $this->messageManager->addSuccessMessage(__(
            '%1 order(s) successfully deleted',
            $count
        ));
        return $this->resultRedirectFactory->create()->setPath(
            Index::ORDERS_GRID_PATH
        );
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(self::REDIRECT_URL);
        }
    }
}
