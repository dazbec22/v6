TRUNCATE TABLE `CubeCart_404_log`; #EOQ
ALTER TABLE `CubeCart_404_log` DROP INDEX `uri`; #EOQ
ALTER TABLE `CubeCart_404_log` ADD UNIQUE `uri` (`uri`) USING BTREE; #EOQ