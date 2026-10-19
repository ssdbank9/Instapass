<?php
/** Independent GBP amounts: no exchange-rate inputs or price fallback. */
final class Instapass_Control_GBP {
    private static function cents($value) {
        if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,5})(?:\.([0-9]{1,2}))?$/D',$value,$m)) {
            throw new InvalidArgumentException('Enter GBP amounts with at most two decimal places, without commas or currency symbols.');
        }
        return (int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');
    }
    private static function decimal($cents) { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
    public static function values($regular,$sale,$discount='') {
        foreach ([$regular,$sale,$discount] as $v) if(!is_string($v)) throw new InvalidArgumentException('Invalid GBP input.');
        $regular=trim($regular);$sale=trim($sale);$discount=trim($discount);
        if($regular==='') {
            if($sale!==''||$discount!=='')throw new InvalidArgumentException('Enter a GBP regular price before adding a sale or discount.');
            return ['regular'=>'','sale'=>''];
        }
        $r=self::cents($regular);
        if($r<=0)throw new InvalidArgumentException('GBP regular price must be greater than zero. Leave it blank to keep GBP unavailable.');
        if($discount!=='') {
            if(!preg_match('/^(0|[1-9][0-9]?|100)(?:\.([0-9]{1,2}))?$/D',$discount,$m))throw new InvalidArgumentException('GBP discount must be between 0 and 100%.');
            $bps=(int)$m[1]*100+(int)str_pad($m[2]??'',2,'0');
            if($bps>10000)throw new InvalidArgumentException('GBP discount must be between 0 and 100%.');
            $sale=$bps===0?'':self::decimal(intdiv($r*(10000-$bps)+5000,10000));
        }
        if($sale!=='') {
            $s=self::cents($sale);
            if($s>=$r)throw new InvalidArgumentException('GBP sale price must be lower than its GBP regular price.');
            $sale=self::decimal($s);
        }
        return ['regular'=>self::decimal($r),'sale'=>$sale];
    }
    public static function effective($regular,$sale) {
        $v=self::values($regular,$sale);
        return $v['regular']===''?null:($v['sale']!==''?$v['sale']:$v['regular']);
    }
}
