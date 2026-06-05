<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
 * BreezingForms NG - A Joomla Forms Application
 * 
 * @version 6.0.0
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2008-2020 by Markus Bopp
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 **/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Uri\Uri;

?>
<?php echo BFText::_('COM_BREEZINGFORMSNG_THANK_YOU_FOR_BUYING'); ?>
<br />
<br />
<?php echo BFText::_('COM_BREEZINGFORMSNG_YOUR_TRANSACTION_ID') ?>:
<?php echo htmlentities($tx_token); ?>
<br />
<?php echo BFText::_('COM_BREEZINGFORMSNG_PAYMENT_METHOD_PAYPAL') ?>
<br />
<br />
<a
    href="<?php echo Uri::root() ?>index.php?raw=true&option=com_breezingformsng&amp;paypalDownload=true&amp;tx=<?php echo urlencode($tx_token) ?>&amp;form=<?php echo intval($form_id) ?>&amp;record_id=<?php echo intval($record_id) ?>">
    <?php echo BFText::_('COM_BREEZINGFORMSNG_DOWNLOAD'); ?> (
    <?php echo BFText::_('COM_BREEZINGFORMSNG_ALLOWED_TRIES'); ?>:
    <?php echo $tries ?>)
</a>