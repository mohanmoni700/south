<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model;

class Storage
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(
        array $data = []
    )
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }
}
