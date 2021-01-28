<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Seller;
use App\Transformers\ProductTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\This;

class SellerProductController extends ApiController
{

    public function __construct(){
        Parent::__construct();
        $this->middleware('transform.input:' . ProductTransformer::class)->only(['store' , 'update']);
    }

    public function index(Seller $seller)
    {
        $products = $seller->products;
        return  $this->showAll($products);
    }


    public function store(Request $request , User $seller)
    {
        $rules = [
          'name'    => 'required',
          'description'    => 'required',
          'quantity'    => 'required|integer|min:1',
          'image'    => 'required|image',
        ];
        $this->validate($request , $rules);
        $data = $request->all();
        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);
        return $this->showOne($product);
    }

    public function update(Request $request , Seller $seller , Product $product)
    {
        $rules = [
          'quantity'    => 'integer|min:1',
          'image'    => 'image',
          'status'    => 'in:' . Product::UNAVAILABLE_PRODUCT .','. Product::AVAILABLE_PRODUCT,
        ];
        $this->validate($request , $rules);

        $this->checkSeller($seller , $product);
        $product->fill($request->only([
            'name' , 'description' , 'quantity'
        ]));
        if ($request->has('status')){
            $product->status  =$request->status;
        }
        if ($request->has('image')){
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }
        if ($product->isAvailable() && $product->categories()->count() == 0){
            return $this->errorResponse('an Activation product must have at leat one category' , 409);
        }
        if ($product->isClean()){
            return $this->errorResponse('you need to satisfy different value for update' , 422);
        }
        $product->save();
        return $this->showOne($product);
    }

    protected function checkSeller(Seller $seller, Product $product)
    {
        if ($seller->id != $product->id){
            throw new \HttpException(422 , "the specification seller is not the actual seller of the product");
        }
    }

    public function destroy(Seller $seller , Product $product){
        $this->checkSeller($seller , $product);
        $product->delete();
        Storage::delete($product->image);
        return $this->showOne($product);
    }


}
