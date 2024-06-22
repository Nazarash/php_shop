<?php
class Basket{
    private static $basket = [];
    private static $count = 0;
    private static $orderId = null;

    const NAME = 'basket';

    public static function init(){
        if(isset($_COOKIE[self::NAME])){
            self::read($_COOKIE[self::NAME]);
        }else{
            self::create();
        }
    }
    private static function read(string $basket){
        self::$basket = unserialize(base64_decode($basket));
        self::$orderId = self::$basket[0];
        unset(self::$basket[0]);
        self::$count = count(self::$basket);
    }
    private static function create(){
        self::$orderId = bin2hex(random_bytes(16));
        self::save();
    }
    private static function save(){
        self::$basket[0] = self::$orderId;
        $basket = base64_encode(serialize(self::$basket));
        setcookie(self::NAME, $basket, 0x7FFFFFFF);
    }
    public static function get(): iterable{
        return new ArrayIterator(self::$basket);
    }

    public static function size(): int{
        return self::$count;
    }
    public static function getOrderId(): string{
        return self::$orderId;
    }
    public static function quantity(int $id): int{
        return self::$basket[$id];
    }

    public static function add(int $id, int $quantity=1){
        self::$basket[$id] = $quantity;
        self::save();
    }
    public static function remove(int $id){
        unset(self::$basket[$id]);
        self::save();
    }
    public static function clear(){
        self::$basket = [];
        setcookie(self::NAME);
    }
    
}