<?php


namespace Experius\FormSelect2\Controller\Adminhtml\Ajax;

class Search extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    protected $jsonHelper;

    protected $searchFields = ['firstname','lastname'];

    protected $modelClass = 'Magento\Customer\Model\Customer';

    protected $modelType = 'eav';

    protected $modelKey = 'entity_id';

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
    ) {
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
            $page = ($this->getRequest()->getParam('param')) ? $this->getRequest()->getParam('param') : 1;
            $model = $this->_objectManager->create($this->modelClass);
            $type = $this->modelType;

            if($query) {

                $collection = $model->getCollection();
                $searchFields = $this->searchFields;

                $conditions = [];
                $eavFilters = [];

                foreach ($searchFields as $searchField) {
                    $conditions[] = ['like' => '%' . $query . '%'];
                    $eavFilters[] =['attribute'=>$searchField,'like'=>'%' . $query . '%'];
                }

                if($type=='eav') {
                    $collection->addFieldToFilter($eavFilters);
                } else {
                    $collection->addFieldToFilter(
                        $searchFields,
                        [
                            $conditions
                        ]
                    );
                }

                $collection->setPageSize(31);
                $collection->setCurPage($page);

                foreach($collection as $item){
                    $items[] = ['id'=>$item->getData($this->modelKey),'text'=>$this->getItemText($item)];
                }
            }

            if($id){
                $model = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($id,$this->modelKey);
                $items[] = ['id'=>$model->getData($this->modelKey),'text'=>$this->getItemText($model)];
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