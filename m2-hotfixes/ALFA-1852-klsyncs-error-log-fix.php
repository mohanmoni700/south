diff --git a/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php b/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
index cefd5642..d046d2f2 100644
--- a/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
+++ b/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
@@ -126,8 +126,11 @@ class KlSyncs
                 foreach($rows as $row) {
                     $decodedPayload = json_decode($row['payload'], true);
 
-                    $eventTime = $decodedPayload['time'];
-                    unset($decodedPayload['time']);
+                    $eventTime = '';
+                    if (isset($decodedPayload['time'])) {
+                        $eventTime = $decodedPayload['time'];
+                        unset($decodedPayload['time']);
+                    }
 
                     //TODO: if conditional for backward compatibility, needs to be removed in future versions
                     $storeId = '';
