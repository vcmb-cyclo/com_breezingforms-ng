ALTER TABLE `#__facileforms_forms`
  ADD COLUMN `debug_mode` tinyint(1) NOT NULL DEFAULT '0' AFTER `published`;
