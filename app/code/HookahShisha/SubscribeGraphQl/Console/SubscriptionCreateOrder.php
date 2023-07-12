<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Console;

use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magedelight\Subscribenow\Model\Service\OrderService;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as SubscriptionCollectionFactory;


class SubscriptionCreateOrder extends Command
{
    const SUBSCRIPTION_ID = 'subscription_id';
    private SubscriptionCollectionFactory $subscriptionCollectionFactory;
    private OrderService $orderService;

    /**
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param OrderService $orderService
     * @param string|null $name
     */
    public function __construct(
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        OrderService $orderService,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->orderService = $orderService;
    }

    protected function configure()
    {
        $this->setName('subscription:create:order')
            ->setDescription(__('Generate an order for an active Subscription'))
            ->setDefinition([
                new InputOption(self::SUBSCRIPTION_ID, 'I', InputOption::VALUE_REQUIRED, 'The Subscription ID')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subscriptionId = $input->getOption(self::SUBSCRIPTION_ID);
        if (!$subscriptionId) {
            throw new NoSuchEntityException(__('Subscription ID is a required field'));
        }
        $model = $this->subscriptionCollectionFactory->create()
            ->addFieldToFilter('subscription_id', $subscriptionId)
            ->getFirstItem();
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Subscription with ID %1 does not exist', $subscriptionId));
        }
        $output->writeln(__('<info>Generating Order....</info>'));
        $this->orderService->createSubscriptionOrder($model, ProductSubscriptionHistory::HISTORY_BY_CRON);
        $output->writeln(__('<info>Order generated Successfully</info>'));
    }
}
