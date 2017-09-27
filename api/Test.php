<?php
namespace Api;


class Test
{
    public function test1($str1 = 'empty1', $str2 = 'empty2')
    {
        //var_dump(SOAServer::getClientEnv());
        //var_dump(SOAServer::getRequestHeader());

        $app = app('app');
        $cache = app('cache');
        
        $cache->put('test', 'demo', 4);

        //$products = $app->db->select("SELECT * FROM products");
        //$cache->put('product', $products, 4);
        //$c = $cache->get('product');

        $test = $cache->get('test');
        var_dump($test);

        /*
        app('log')->info('test', ['aaa' => $product->id]);
        */
        
        
        return "hello-soa-finish: $str1" . " $str2 + " . $app->version();
    }

    public function hello()
    {
        return array('key1' => 'A', 'key2' => 'B');
    }
}