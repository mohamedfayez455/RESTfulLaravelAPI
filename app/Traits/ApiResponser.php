<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait ApiResponser{

    private function successResponse($data , $code){
        return response()->json($data  , 200);
    }

    protected function errorResponse($message , $code){
        return response()->json(['message' => $message , 'code' => $code] , $code);
    }

    protected function showAll(Collection $collection , $code = 200){
        if ($collection->isEmpty()){
            return $this->successResponse(['data' => $collection] , $code);
        }
        $transformer = $collection->first()->transformer;
        $collection = $this->filterData($collection , $transformer);
        $collection = $this->sortData($collection , $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection , $transformer);
        return $this->successResponse($collection , $code);
    }

    protected function showOne(Model $instance , $code = 200){
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance , $transformer);
        return $this->successResponse($instance , $code);
    }

    protected function showMessage($message , $code = 200){
        return $this->successResponse(['data' => $message ] , $code);
    }

    protected function sortData(Collection $collection , $transformer){
        if (request('sort_by')){
            $attribute = $transformer::originalAttribute(request('sort_by'));
            $collection = $collection->sortBy->{$attribute};
        }
        return $collection;
    }
    protected function paginate(Collection $collection ){
        $rules = [
            'pre_page'  => 'integer|min:2|max:50',
        ];
        Validator::validate(request()->all() , $rules);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $prePage = 15;
        if (request('pre_page')){
            $prePage = (int) request('pre_page');
        }
        $results = $collection->slice(($page-1)*$prePage , $prePage)->values();
        $paginated = new LengthAwarePaginator($results , $collection->count() , $prePage , $page , [
            'path' => LengthAwarePaginator::resolveCurrentPage(),
        ]);
        $paginated->appends(request()->all());
        return $paginated;
    }

    protected function filterData(Collection $collection , $transformer){
        foreach (request()->query() as $query=>$value){
            $attribute = $transformer::originalAttribute($query);
            if (isset($attribute , $value)){
                $collection = $collection->where($attribute , $value);
            }
        }
        return $collection;
    }

    protected function transformData($data , $transformer){
        $transformation = fractal($data , new $transformer);
        return $transformation->toArray();
    }

    protected function cacheResponse($data){
        $url = request()->url();
        $queryParams = request()->query();
        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        $fullUrl = "{$url}?{$queryString}";
        return Cache::remeber($fullUrl , 30/60 , function () use ($data){
          return $data;
        });
    }


}