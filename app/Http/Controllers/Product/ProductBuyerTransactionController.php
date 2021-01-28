<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{

    public function __construct(){
        Parent::__construct();
        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
    }

    public function store( Request $request, Product $product ,User $buyer)
    {
        $rules =[
         'quantity' => 'required|min:1'
        ];
        $this->validate($request , $rules);
        if ($buyer->id == $product->seller_id){
            return $this->errorResponse('the buyer must different from the seller' , 409);
        }
        if (! $buyer->isVerified()){
            return $this->errorResponse('the buyer must verified user' , 409);
        }
        if (! $product->seller->isVerified()){
            return $this->errorResponse('the seller must verified user' , 409);
        }
        if (! $product->isAvailable()){
            return $this->errorResponse('the product is not available' , 409);
        }
        if (! $product->quantity < $request->quentity){
            return $this->errorResponse('the product does not have enough unit for this transaction' , 409);
        }
        return DB::transaction(function ()use ($request , $product , $buyer){
           $product->quantity -= $request->quantity;
           $product->save();

           $transaction = Transaction::create([
              'quantity' => $request->quantity,
               'buyer_id' => $buyer->id,
               'product_id' => $product->id,
           ]);
           return $this->showOne($transaction , 201);
        });
    }


}
