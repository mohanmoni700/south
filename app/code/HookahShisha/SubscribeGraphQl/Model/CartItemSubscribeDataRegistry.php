<?php

namespace HookahShisha\SubscribeGraphQl\Model;

class CartItemSubscribeDataRegistry
{
    private array $data = [];

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param null $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
