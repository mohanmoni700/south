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

    /**
     * Getdata
     *
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Setdata
     *
     * @param null $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
