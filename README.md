Targetpay iDEAL API class for PHP
===============

Targetpay iDEAL API class for PHP

#Setup
Setting up this Targetpay iDEAL API is fairly easy. You will need to define your Targetpay Layoutcode (found on the Dashboard).
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code'));
?>
```

##Test mode
Targetpay supports basis test support. You will receive a successfull result value even if the transaction in internet banking was cancelled. Instead of an actual payment in internet banking, you cancel it and are able to see what will happen as if someone had really paid.

To enable test mode you need to set the test option to true as shown below.
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code','test'=>true));
?>
```

#Getting a list of banks
To get the list of iDEAL banks supported by Targetpay you can use the getBanks() method. This method will return an array with as key the bank ID and as value the bank name. In the example below we will load the banks into an select list.
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code'));
?>
<select name="bank">
  <?php
    foreach($api->getBanks() as $bank_id=>$bank_name) {
	    echo '<option value="'.$bank_id.'">'.$bank_name.'</option>';
    }
  ?>
</select>
```

##Check if an bank ID exists
To validate the choosen bank you can use the bankExists($bank_id) method. This will return true when the bank ID exists and will return false when not.
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code'));
  if(isset($_POST['bank']) && $api->bankExists($_POST['bank'])) {
    //Bank exists
  } else {
    //Bank doesn't exists
  }
?>
```

#Preparing the payment
When the user choosed the bank you can prepare the payment. You will need to use the preparePayment() method for this.
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code'));
  if(isset($_POST['bank']) && $api->bankExists($_POST['bank'])) {
    $bank = $_POST['bank']; //The choosen bank by the user, required
    $description = 'Test payment'; //The description of the payment, required
    $amount = 1000; //The payment amount in EUROCENTS, required
    $returnurl = 'http://example.com/return.php'; //The url where the user will be redirect to after the payment, required
    $reporturl = 'http://example.com/report.php'; //The report url, this will be called on the background, optional
    if($api->preparePayment($bank,$description,$amount,$returnurl,$reporturl)) {
      //Payment prepared
      //To get the transaction ID you can use $api->getTransactionID()
      //To get the redirect(bank) URL you can use $api->getTransactionURL()
      header('Location: '.$api->getTransactionURL());
      exit;
    } else {
      //Error while preparing payment
    }
  } else {
    //Bank doesn't exists
  }
?>
```
#Checking a payment
After the user is redirected back to your site, you can check the payment on your returnurl and reporturl by using the checkPayment() method. This will return true when the payment is succesfull and false when the payment failed or has been cancelled.
```php
<?php
  //Include the targetpay iDEAL API here
  $api = new TargetpayIdeal(array('layoutcode'=>'your-layout-code'));
  if(isset($_REQUEST['trxid']) && $api->checkPayment($_REQUEST['trxid'])) {
    //Payment successfull
  } else {
    //Payment failed
  }
?>
```
