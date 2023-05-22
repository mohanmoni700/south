<?php
namespace Avalara\Excise\Model;
use Avalara\Excise\Api\Rest\ListEntityUseCodesInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Model\EntityUseCodeFactory;

/**
 * @codeCoverageIgnore
 */
class UpdateEntityUseCodes
{
	/**
     * UpdateEntityUseCodes constructor.
     *
     * @param  ListEntityUseCodesInterface $entityUseCodesInterface
     */
	public function __construct(
        ListEntityUseCodesInterface $entityUseCodesInterface,
        EntityUseCodeFactory $_entityUseCodeFactory
    ){
        $this->entityUseCodesInterface = $entityUseCodesInterface;
        $this->_entityUseCodeFactory = $_entityUseCodeFactory;
    }

    /**
     * insert entity use codes records from API response using Cron
     */
    public function updateEntityUseCodes()
    {
    	// Get response from api
        $type = Constants::AVALARA_API;
        $entityUseCodesResponse = $this->entityUseCodesInterface->getEntityUseCodes(null, $type);
        
        //First truncate the table and insert new records from response
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        if($entityUseCodesResponse){
	        $result = $this->_entityUseCodeFactory->create();
	        $collection = $result->getCollection();
	        $tableName = $collection->getResource()->getMainTable();
	        $conn = $collection->getConnection();
	        $conn->truncateTable($tableName);
	        // Create data to insert into teh table
	        $insertArray = $this->getInsertData($entityUseCodesResponse);
			$conn->insertMultiple($tableName, $insertArray);
	   	} 
    }

    private function getInsertData($entityUseCodesResponse)
    {
    	foreach ($entityUseCodesResponse as $value) {
            $optionArr[] = [
            	'code' => $value['code'],
                'name' => $value['code'] .' - '. $value['name']                 
            ];
        }
        return $optionArr;
    }
}
