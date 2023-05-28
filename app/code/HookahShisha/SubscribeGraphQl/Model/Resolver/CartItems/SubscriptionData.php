<?php
namespace HookahShisha\SubscribeGraphQl\Model\Resolver\CartItems;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\SerializerInterface;

class SubscriptionData implements ResolverInterface
{
    /**
     * @var $serializer
     */
    protected $serializer;

   public function __construct(SerializerInterface $serializer)
   {
      $this->serializer = $serializer;
   }
   public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
   {
      $item = $value['model'] ?? null;

      $isSubscription = null;
      $billingPeriod = null;
      $subscriptionStartDate = null;
      $endType = null;
      $subscriptionEndDate = null;

      if ($item) {
         $isSubscription = $item->getData('is_subscription') == 1;

         $itemOption = $item->getOptionByCode('additional_options');
          if (!empty($itemOption)) {
           $additionalOptions = $this->serializer->unserialize($itemOption->getValue());
           if(!empty($additionalOptions)){
               foreach($additionalOptions as $additionalOption){
                  if($additionalOption['code'] == 'billing_period_title'){
                     $billingPeriod = $additionalOption['value'];
                  } elseif ($additionalOption['code'] == 'md_sub_start_date') {
                     $subscriptionStartDate = $additionalOption['value'];
                  } elseif ($additionalOption['code'] == 'md_sub_end_date') {
                     $subscriptionEndDate = $additionalOption['value'];
                  } elseif ($additionalOption['code'] == 'billing_cycle_title') {
                      $endType = $additionalOption['value'];
                  }
               }
           }
         }
      }
      return [
         'is_subscription' => $isSubscription,
         'billing_period' => $billingPeriod,
         'subscription_start_date' => $subscriptionStartDate,
         'end_type' => $endType,
         'subscription_end_date' => $subscriptionEndDate
      ];
   }
}
