<?php
class Brandedcrate_Payjunction_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        echo Mage::getStoreConfig('payment/payjunction/active');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/order_status');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/title');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/username');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/password');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/appkey');
        echo "<br>";
        echo Mage::getStoreConfig('payment/payjunction/endpoint');
        echo "<br>";



        $pj = new BrandedCrate\PayJunction\Client(
            array(
                'username' => Mage::getStoreConfig('payment/payjunction/username'),
                'password' => Mage::getStoreConfig('payment/payjunction/password'),
                'appkey'   => Mage::getStoreConfig('payment/payjunction/appkey'),
                'endpoint' => Mage::getStoreConfig('payment/payjunction/endpoint') // 'test' or 'live'
            )
        );





    }



}