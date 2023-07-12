<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model;

/**
 * CartItemSubscribeDataRegistry
 */
class CartItemSubscribeDataRegistry
{
    /**
     * @var array
     */
    private array $data = [];

    private bool $isDataSet = false;

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
        $this->isDataSet = true;
    }

    /**
     * @return bool
     */
    public function isDataSet(): bool
    {
        return $this->isDataSet;
    }
}
