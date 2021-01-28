<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductTransactionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }
    public function index(Product $product)
    {
        $transaction  = $product->transactions;
        return $this->showAll($transaction);
    }


}
