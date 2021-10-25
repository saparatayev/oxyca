<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_added_to_cart()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $product = Product::first();

        $response = $this->get(route('cart.add', ['id' => $product->id]));

        $response->assertSessionHas('products', [
            $product->id => 1
        ]);

        $response->assertJson([
            'cartCount' => 1,
            'prodByIdCount' => 1,
            'oneProdTotalPrice' => 1 * $product->price,
            'totalPrice' => 1 * $product->price
        ], $strict = false);
    }

    public function test_one_product_can_be_added_to_cart_several_times()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $product = Product::first();

        // several times
        $response = $this->get(route('cart.add', ['id' => $product->id]));
        $response = $this->get(route('cart.add', ['id' => $product->id]));
        $response = $this->get(route('cart.add', ['id' => $product->id]));

        $response->assertSessionHas('products', [
            $product->id => 3
        ]);

        $response->assertJson([
            'cartCount' => 3, // total quantity of items in cart
            'prodByIdCount' => 3, // quantity of added $product->id
            'oneProdTotalPrice' => 3 * $product->price,
            'totalPrice' => 3 * $product->price
        ], $strict = false);
    }

    public function test_different_products_can_be_added_to_cart()
    {
        $this->withoutExceptionHandling();

        $file1 = $this->fakeUploadFile('product1.jpg');
        $file2 = $this->fakeUploadFile('product2.jpg');

        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $this->storeProduct($user, array_merge($this->data(), [
                'title' => 'Lorem ipsum 2',
                'sku' => 'HDV7T',
                'price' => 459.36,
                'image' => $this->fakeUploadFile('product2.jpg')
            ]));

        $this->assertCount(2, Product::all());

        $product1 = Product::first();
        $product2 = Product::orderBy('id', 'desc')->first();

        // add product1 to cart one time
        $response = $this->get(route('cart.add', ['id' => $product1->id]));
        $response->assertSessionHas('products', [
            $product1->id => 1,
        ]);

        $response->assertJson([
            'cartCount' => 1, // total quantity of items in cart
            'prodByIdCount' => 1, // quantity of added $product1->id
            'oneProdTotalPrice' => 1 * $product1->price,
            'totalPrice' => 1 * $product1->price
        ], $strict = false);

        // add product2 to cart one time
        $response = $this->get(route('cart.add', ['id' => $product2->id]));

        $response->assertSessionHas('products', [
            $product1->id => 1,
            $product2->id => 1,
        ]);

        $response->assertJson([
            'cartCount' => 2, // total quantity of items in cart
            'prodByIdCount' => 1, // quantity of added $product2->id
            'oneProdTotalPrice' => 1 * $product2->price,
            'totalPrice' => 1 * $product2->price + 1 * $product1->price
        ], $strict = false);
    }

    public function test_a_product_can_be_added_to_cart_by_not_ajax()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $product = Product::first();

        $response = $this->get(route('cart.not_ajax.add', ['id' => $product->id]));

        $response->assertSessionHas('products', [
            $product->id => 1
        ]);

        $response->assertRedirect(route('cart.index'));
    }

    public function test_product_quantity_can_be_subtracted()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $product = Product::first();

        // several times
        $this->get(route('cart.add', ['id' => $product->id]));
        $this->get(route('cart.add', ['id' => $product->id]));
        $this->get(route('cart.add', ['id' => $product->id]));

        $response = $this->get(route('cart.subtract', ['id' => $product->id]));

        $response->assertSessionHas('products', [
            $product->id => 2,
        ]);

        $response->assertJson([
            'cartCount' => 2, // total quantity of items in cart
            'prodByIdCount' => 2, // quantity of added $product->id
            'oneProdTotalPrice' => 2 * $product->price,
            'totalPrice' => 2 * $product->price
        ], $strict = false);
    }

    public function test_product_can_be_deleted_from_cart()
    {
        $file2 = $this->fakeUploadFile('product2.jpg');

        $user = User::factory()->create();

        $this->storeProduct($user, $this->data()); // first product

        $this->storeProduct($user, array_merge($this->data(), [ // second product
                'title' => 'Lorem ipsum 2',
                'sku' => 'HDV7T',
                'price' => 459.36,
                'image' => $this->fakeUploadFile('product2.jpg')
            ]));

        $product1 = Product::first();
        $product2 = Product::orderBy('id', 'desc')->first();

        // add product1 to cart one time
        $this->get(route('cart.add', ['id' => $product1->id]));
        
        // add product2 to cart one time
        $this->get(route('cart.add', ['id' => $product2->id]));

        $response = $this->get(route('cart.delete', ['id' => $product2->id]));

        $response->assertJson([
            'cartCount' => 1, // total quantity of items in cart
            'totalPrice' => 1 * $product1->price
        ], $strict = false);
    }

    /**
     * An array of inputed product's data.
     *
     * @return Array
     */
    private function data()
    {
        return [
            'title' => 'Lorem ipsum',
            'sku' => 'JS6SY',
            'price' => 126.23,
            'image' => $this->fakeUploadFile('product.jpg')
        ];
    }

    /**
     * Helper for storing a product
     * 
     * @return void
     */
    private function storeProduct($user, $params)
    {
        $this->actingAs($user)
            ->post(route('products.store'), $params);
    }

    /**
     * Fake file upload
     * 
     * @return UploadedFile
     */
    private function fakeUploadFile($filename)
    {
        return UploadedFile::fake()->image($filename, 300, 300)->size(2000);
    }
}
