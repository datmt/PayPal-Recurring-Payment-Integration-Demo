<?php
include_once 'PayPal.php';
include_once 'db/YOUR_DB_CLASS.php';

if (!isset($_GET))
	return;

$token = $_GET['token'];

$payerID = PayPal::getPayerID($token);

// var_dump($payerID);

if (!$payerID)
{
	//redirect to payment failed page if $payerID == false;
	header("Location: http://YOUR_SITE/payment-failed/");
	return;
}	

$result = PayPal::createPaymentProfile($payerID);


if (!$result)
{
	//redirect to payment failed page if $result == false;
	header("Location: http://YOUR_SITE/payment-failed/");
	return;
}	

//insert buyer data into db



//redirect to success page (download page e.g.)
header("Location: http://YOUR_SITE/download-oheen-traffic/");

// echo '<script> window.location.href = "http://www.oheen.com/download-oheen-traffic/";</script>';



