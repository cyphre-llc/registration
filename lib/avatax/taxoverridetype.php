<?php
/**
 * TaxOverrideType.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright � 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */

//namespace AvaTax;
namespace OCA\Registration\Avatax;

class TaxOverrideType
{
		public static $None = "None"; //No tax override
		public static $TaxAmount = "TaxAmount"; //Override the tax amount to be calculated
		public static $Exemption = "Exemption"; //Override the presence of an exemption certificate to make the transaction taxable.
		public static $TaxDate = "TaxDate";		//Override the tax date used to pull rate and boundaries for calculation.
}

?>
