<?php

namespace Alfakher\Shopify\Api;

/**
 * Interface for managing customers accounts.
 * @api
 * @since 100.0.2
 */
interface CustomerRepositoryInterface
{
    /**
     * Create customer account.
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @return int $id
     */
    public function createCustomerAccount(
        $first_name,
        $last_name,
        $email
    );
}
