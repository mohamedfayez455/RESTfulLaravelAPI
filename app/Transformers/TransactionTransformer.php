<?php

namespace App\Transformers;

use App\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'identifier' => (int) $transaction->id,
            'quantity'       => (string) $transaction->quantity,
            'buyer'       => (string) $transaction->buyer_id,
            'Product'       => (string) $transaction->product_id,
            'creationDate'=> (string) $transaction->created_at,
            'lastChange'=> (string) $transaction->updated_at,
            'deletedDate' => isset($transaction->deleted_at) ? (string) $transaction->deleted_at : null,
            'links' => [
                [
                    'rel'   => 'Self',
                    'href' => route('transactions.show ' , $transaction->id),
                ],
                [
                    'rel'   => 'transaction.products',
                    'href' => route('products.show' , $transaction->product_id),
                ],
                [
                    'rel'   => 'transaction.categories',
                    'href' => route('transactions.categories.index' , $transaction->id),
                ],
                [
                    'rel'   => 'transaction.sellers',
                    'href' => route('transactions.sellers.index' , $transaction->id),
                ],
                [
                    'rel'   => 'transaction.buyers',
                    'href' => route('buyers.show' , $transaction->buyer_id),
                ],
            ]
        ];
    }


    public static function originalAttribute($index){
        $attributes = [
            'identifier' => 'id',
            'quantity'   => 'quantity',
            'buyer'  => 'buyer_id',
            'Product'  => 'product_id',
            'creationDate'=> 'created_at',
            'lastChange'=> 'updated_at',
            'deletedDate' => 'deleted_at',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null ;
    }


    public static function transformedAttribute($index){
        $attributes = [
            'id' => 'identifier',
            'quantity'   => 'quantity',
            'buyer_id'  => 'buyer',
            'product_id'  => 'Product',
            'created_at'=> 'creationDate',
            'updated_at'=> 'lastChange',
            'deleted_at' => 'deletedDate',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null ;
    }

}
