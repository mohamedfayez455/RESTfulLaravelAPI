<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductCategoryController extends ApiController
{


    public function __construct(){
        $this->middleware('client.credentials')->only(['index']);
        $this->middleware('auth:api')->except(['index']);
    }

    public function index(Product $product)
    {
        $categories  = $product->categories;
        return $this->showAll($categories);
    }

    public function update(Request $request , Product $product , Category $category){
        $product->categories()->syncWithoutDetaching([$category->id]);
        return $this->showAll($product->categories);
    }

    public function delete(Product $product , Category $category){
        if (! $product->categories()->find($category->id)){
            return $this->errorResponse('the spesified category is not a category of product' , 404);
        }
        $product->categories()->detach($category->id);
        return $this->showAll($product->categories);
    }

}
