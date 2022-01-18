<?php

namespace Blueskytechco\AskQuestion\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class Save extends \Magento\Framework\App\Action\Action
{
    private Context $context;
    private \Blueskytechco\AskQuestion\Model\QuestionFactory $questionfactory;
    private \Magento\Catalog\Model\ProductFactory $_productloader;
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;
    private \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory;
    private \Magento\Framework\App\Filesystem\DirectoryList $directoryList;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \Magento\Framework\UrlInterface $urlInterface;
    private \Magento\Customer\Model\Session $customerSession;
    private \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;
    private \Magento\Framework\App\Request\Http $request;
    private \Blueskytechco\AskQuestion\Helper\Email $emailSender;

    /**
     * Booking action
     *
     * @return void
     */
    public function __construct
    (
        Context $context,
        \Blueskytechco\AskQuestion\Model\QuestionFactory $questionfactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Request\Http $request,
        \Blueskytechco\AskQuestion\Helper\Email $emailSender
    )
    {
        parent::__construct($context);
        $this->questionfactory = $questionfactory;
        $this->_productloader = $_productloader;
        $this->scopeConfig = $scopeConfig;
        $this->resultPageFactory = $resultPageFactory;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->emailSender = $emailSender;
    }
    public function execute()
    {
        $receiveEmail = $this->scopeConfig->getValue('question_email/general/email_received', ScopeInterface::SCOPE_STORE);
        $post = (array) $this->getRequest()->getPost();
        if (!empty($post)) {
            $model = $this->questionfactory->create();
            $model->setData($post)->save();
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $product = $this->_productloader->create()->load($post['product_id']);
        $product_name = $product->getName();
        $emailTemplateData = [
            'customer_name' => $post['customer_name'],
            'email' => $post['email'],
            'phone' => $post['phone'],
            'product' => $product_name,
            'message' => $post['message']
        ];
        $this->emailSender->sendEmail($receiveEmail, $emailTemplateData);
        $message = __(
            'Thank you for send us question!'
        );
        $this->messageManager->addSuccessMessage($message);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}