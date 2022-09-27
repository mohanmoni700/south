<?php
namespace Alfakher\Webhook\Plugin;

class AddNewHookType
{
    public const NEW_DOCUMENT = 'new_document';
    public const UPDATE_DOCUMENT = 'update_document';
    public const DELETE_DOCUMENT = 'delete_document';
    public const UPDATE_ORDER = 'update_order';
    public const CREATE_RMA = 'create_rma';
    public const UPDATE_RMA = 'update_rma';

    /**
     * Retrieve all hook type and add new hook type
     *
     * @param string $subject
     * @param array $result
     * @return array
     */
    public function afterToArray(\Mageplaza\Webhook\Model\Config\Source\HookType $subject, $result)
    {
        $result[self::NEW_DOCUMENT] = "New Document";
        $result[self::UPDATE_DOCUMENT] = "Change Document";
        $result[self::DELETE_DOCUMENT] = "Remove Document";
        $result[self::UPDATE_ORDER] = 'Blue Edit Order';
        $result[self::CREATE_RMA] = 'New RMA';
        $result[self::UPDATE_RMA] = 'Change RMA';
        return $result;
    }
}
