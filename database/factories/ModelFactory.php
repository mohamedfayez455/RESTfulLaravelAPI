<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use \App\Product;
use \App\Transaction;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'admin' => $faker->randomElement([\App\User::ADMIN_USER , \App\User::REGULAR_USER]),
        'verified' => $verified =$faker->randomElement([\App\User::VERIFIED_USER , \App\User::UNVERIFIED_USER]),
        'verification_token' => $verified == \App\User::UNVERIFIED_USER ? null : \App\User::generateVerificationCode(),
    ];
});

$factory->define(\App\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph(1),
    ];
});

$factory->define(Product::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph(1),
        'image' =>$faker->randomElement(['img2.jpg','img3.jpg','img4.jpg']),
        'quantity' =>$faker->numberBetween(1,10),
        'status' =>$faker->randomElement([Product::AVAILABLE_PRODUCT , Product::UNAVAILABLE_PRODUCT]),
        'seller_id' => \App\User::all()->random()->id,
    ];
});

$factory->define(Transaction::class, function (Faker\Generator $faker) {
    $seller = \App\Seller::has('products')->get()->random();
    $buyer = \App\User::all()->except($seller->id)->random();
    return [
        'quantity' =>$faker->numberBetween(1,10),
        'buyer_id' => $buyer->id,
        'product_id' => $seller->products->random()->id,
    ];
});

