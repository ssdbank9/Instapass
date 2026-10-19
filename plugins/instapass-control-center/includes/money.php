<?php
/** Integer-only currency arithmetic; rates are supplied explicitly to quotes. */
final class Instapass_Control_Money {
 const PKR_PER_USD=280;
 const POLICY='usd-pkr-unit-nearest100-v2';
 public static function usd_cents($value){
  if(!is_string($value)||!preg_match('/^(0|[1-9][0-9]{0,5})(?:\.([0-9]{1,2}))?$/D',$value,$m))throw new InvalidArgumentException('Enter an amount from 0 to 999999.99 with at most two decimal places.');
  return (int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');
 }
 public static function pkr_unit($cents,$rate='280'){
  if(!is_int($cents)||$cents<0||$cents>99999999)throw new InvalidArgumentException('Invalid USD cents.');
  $r=self::usd_cents($rate);if($r<=0)throw new InvalidArgumentException('Rate must be positive.');
  return intdiv($cents*$r+500000,1000000)*100;
 }
 public static function quote($usd,$quantity=1,$rate='280'){
  if(!is_int($quantity)||$quantity<1||$quantity>2500)throw new InvalidArgumentException('Quantity must be a whole number from 1 to 2500.');
  $c=self::usd_cents($usd);$p=self::pkr_unit($c,$rate);
  return ['policy'=>self::POLICY,'rate'=>(float)$rate,'usd_unit_cents'=>$c,'pkr_unit_rupees'=>$p,'quantity'=>$quantity,'usd_total_cents'=>$c*$quantity,'pkr_total_rupees'=>$p*$quantity];
 }
 public static function usd_text($cents){return number_format($cents/100,2,'.',',');}
}
