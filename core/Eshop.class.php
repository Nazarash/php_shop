<?php
class Eshop{
    private static $db = null;
    public static function init(array $db)
    {
        self::$db = new PDO("mysql:host={$db['HOST']};dbname={$db['NAME']}", $db['USER'], $db['PASS']);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    }
    public static function addItemToCatalog(Book $item): bool{
        self::cleanItem($item);
        $params = "{$item->title}, {$item->author}, {$item->price}, {$item->pubyear}";
        $sql = "Call spAddItemToCatalog($params)";
        if(self::$db->exec($sql))
            return true;
        return false;
    }

    public static function getItemsFromCatalog(): iterable{
        $sql = "Call spGetCatalog()";
        $result = self::$db->query($sql, PDO::FETCH_CLASS, 'Book');
        if(!$result)
            return new EmptyIterator();
        return new IteratorIterator($result);
    }

    private static function cleanItem(Book $item)
    {
        $item->title = Cleaner::str2db($item->title, self::$db);
        $item->author = Cleaner::str2db($item->author, self::$db);
        $item->price = Cleaner::uint($item->price);
        $item->pubyear = Cleaner::uint($item->pubyear);
    }

    public static function countItemsInBasket(){
        return Basket::size();
    }
    public static function addItemToBasket($id){
       $id = Cleaner::uint($id);
       if(!$id){
            return false;
       }
       Basket::add($id); 
       return true; 
    }
    public static function getItemsFromBasket(): iterable{
       if(!self::countItemsInBasket())
        return new EmptyIterator();
        
       $keys = array_keys(iterator_to_array(Basket::get()));
       $ids = implode(',', $keys);
       $sql = "Call spGetItemsForBasket('$ids')";
       $stmt = self::$db->query($sql);
       $books = $stmt->fetchAll(PDO::FETCH_CLASS, 'Book');
       if(!count($books))
        return new EmptyIterator();
       foreach($books as $book){
        $book->quantity = Basket::quantity($book->id);
       } 
       return new ArrayIterator($books);
    }
    public static function removeItemFromBasket($id){
       $id = Cleaner::uint($id);
       if(!$id){
            return false;
       }
       Basket::remove($id); 
       return true; 
    }
}