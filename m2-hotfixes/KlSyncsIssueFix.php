diff --git a/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php b/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
index cefd5642..cb231d37 100644
--- a/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
+++ b/vendor/klaviyo/magento2-extension/Cron/KlSyncs.php
@@ -125,9 +125,13 @@ class KlSyncs
             if (in_array($topic, $trackApiTopics) && !empty($rows)) {
                 foreach($rows as $row) {
                     $decodedPayload = json_decode($row['payload'], true);
-
-                    $eventTime = $decodedPayload['time'];
-                    unset($decodedPayload['time']);
+                    if (empty($decodedPayload)) {
+                        continue;
+                    }
+                    if (isset($decodedPayload['time'])) {
+                        $eventTime = $decodedPayload['time'];
+                        unset($decodedPayload['time']);
+                    }
 
                     //TODO: if conditional for backward compatibility, needs to be removed in future versions
                     $storeId = '';
