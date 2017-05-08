<?php


namespace Experius\FormSelect2\Controller\Adminhtml\Ajax;

class Search extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    protected $jsonHelper;

    protected $search;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ){

        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {

            $id = $this->getRequest()->getParam('id');
            $query = $this->getRequest()->getParam('q');
            $page = ($this->getRequest()->getParam('page')) ? $this->getRequest()->getParam('page') : 1;

            if($this->getRequest()->getParam('search')){
                $this->search = $this->_objectManager->create('Experius\\FormSelect2\\Model\\Virtual\\' . $this->getRequest()->getParam('search'));
            } else {
                $this->search = $this->_objectManager->create('Experius\\FormSelect2\\Model\\Search',['searchData'=>'']);
            }

            if($query) {
                $items = $this->search->searchCollection($query,$page);
            }

            if($id){
                $items = $this->search->loadInitialValue($id);
            }

            if($query || $id){
                $response = [
                    'query'=> $query,
                    'total_count' => count($items),
                    'page' => $page,
                    'items'=> $items
                ];
                return $this->jsonResponse($response);
            };

            return $this->jsonResponse([]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    private function getItemText($item){
        $fields = ['firstname','lastname'];
        $text = '';
        foreach($fields as $field){
            $text .= $item->getData($field) . " ";
        }
        return $text;
    }
}