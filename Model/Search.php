<?php


namespace Experius\FormSelect2\Model;

class Search extends \Magento\Framework\Model\AbstractModel
{

    protected $searchFields = ['firstname','lastname'];

    protected $modelClass = 'Magento\Customer\Model\Customer';

    protected $modelCollectionClass = 'Magento\Customer\Model\ResourceModel\Customer\Collection';

    protected $modelType = 'eav';

    protected $modelKey = 'entity_id';

    protected $sortByAttribute = 'firstname';

    protected $objectManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $searchData = []
    ) {
        $this->objectManager = $objectManager;

        if(isset($searchData['modelClass'])){
            $this->modelClass = $searchData['modelClass'];
        }

        if(isset($searchData['searchFields'])){
            $this->searchFields = $searchData['searchFields'];
        }

        if(isset($searchData['modelType'])){
            $this->modelType = $searchData['modelType'];
        }

        if(isset($searchData['modelKey'])){
            $this->modelKey = $searchData['modelKey'];
        }

        if(isset($searchData['sortByAttribute'])){
            $this->sortByAttribute = $searchData['sortByAttribute'];
        }

    }

    public function setSearchFields($searchFields){
        $this->searchFields = (array) $searchFields;
    }

    public function setModelClass($class){
        $this->modelClass = (string) $class;
    }

    public function setModelType($searchFields){
        $this->modelType = (string) $searchFields;
    }

    public function setModelKey($modelKey){
        $this->modelKey = (string) $modelKey;
    }

    private function getModel(){
        return $this->objectManager->create($this->modelClass);
    }

    public function getCollectionModel(){
        return $this->objectManager->create($this->modelCollectionClass);
    }

    public function searchCollection($query,$page){

        $collection = $this->getCollectionModel();
        $searchFields = $this->searchFields;

        $conditions = [];
        $eavFilters = [];

        foreach ($searchFields as $searchField) {
            $conditions[] = ['like' => '%' . $query . '%'];
            $eavFilters[] =['attribute'=>$searchField,'like'=>'%' . $query . '%'];
        }

        if($this->modelType=='eav') {
            $collection->addAttributeToFilter($eavFilters,null,'left');
        } else {
            $collection->addFieldToFilter(
                $searchFields,
                [
                    $conditions
                ]
            );
        }

        if($page) {
            $collection->setPageSize(31);
            $collection->setCurPage($page);
        }

        if($this->sortByAttribute){
            $collection->setOrder($this->sortByAttribute,'ASC');
        }

        foreach($collection as $item){
            $items[] = ['id'=>$item->getData($this->modelKey),'text'=>$this->getItemText($item)];
        }

        return $items;

    }

    public function loadInitialValue($key){
        $model = $this->getModel()->load($key,$this->modelKey);
        $items[] = ['id'=>$model->getData($this->modelKey),'text'=>$this->getItemText($model)];
    }

    private function getItemText($item){
        $fields = $this->searchFields;
        $text = '';
        foreach($fields as $field){
            $text .= $item->getData($field) . " ";
        }
        return $text;
    }


}