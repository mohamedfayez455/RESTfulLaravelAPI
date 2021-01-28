<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuyerTransactionsController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Buyer $buyer)
    {
        $transactions = $buyer->transactions;
        return $this->showAll($transactions);
    }


}
