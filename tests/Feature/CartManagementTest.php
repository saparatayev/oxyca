<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    /**
     * RefreshDatabase doesn't work properly.
     * It stores data between tests, causing tests to fail.
     * For example php artisan test --filter test_several_products_with_different_quantities_can_be_checked_out --env=testing PASSES
     * BUT
     * php artisan test --env=testing FAILS
     * That's why use DatabaseMigrations instead of RefreshDatabase
     */
    use DatabaseMigrations;

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

    public function test_products_in_cart_can_be_checked_out()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());
        $this->storeCustomer($user, $this->dataOfCustomer());

        $product = Product::first();

        $this->get(route('cart.add', ['id' => $product->id]));


        $response = $this->post(route('checkout'), [
            'customer_id' => 1,
            'cart_count' => 1
        ]);

        $order = Order::first();
        $this->assertCount(1, Order::all());
        $this->assertCount(1, $order->products()->get());
        $this->assertTrue($order->products()->first()->pivot->quantity === 1); // check pivot column
        $this->assertTrue($order->total === floatval(number_format(1 * $product->price, 2, '.', '')));
        $this->assertTrue($order->customer_id === 1);

        $response->assertSessionHas('products', []);
        $response->assertRedirect(route('orders.index'));
    }

    public function test_several_products_with_different_quantities_can_be_checked_out()
    {
        $this->withExceptionHandling();

        $user = User::factory()->create();

        $this->storeCustomer($user, $this->dataOfCustomer());
        $this->storeProduct($user, $this->data());
        $this->storeProduct($user, array_merge($this->data(), [ // second product
            'title' => 'Lorem ipsum 2',
            'sku' => 'HDV7T',
            'price' => 459.36,
            'image' => $this->fakeUploadFile('product2.jpg')
        ]));

        $product1 = Product::first();
        $product2 = Product::orderBy('id', 'desc')->first();

        // two times was added $product1 & one time $product2
        $this->get(route('cart.add', ['id' => $product1->id]));
        $this->get(route('cart.add', ['id' => $product1->id]));
        $this->get(route('cart.add', ['id' => $product2->id]));

        $response = $this->post(route('checkout'), [
            'customer_id' => 1,
            'cart_count' => 3 // two times was added $product1 & one time $product2
        ]);

        $order = Order::first();
        $firstProductInOrder = $order->products()->first();
        $secondProductInOrder = $order->products()->orderBy('id', 'desc')->first();

        $this->assertCount(1, Order::all());
        $this->assertCount(2, $order->products()->get()); // two different types of products
        $this->assertTrue($firstProductInOrder->pivot->quantity === 2); // check pivot column
        $this->assertTrue($secondProductInOrder->pivot->quantity === 1); // check pivot column
        $this->assertTrue($order->total === floatval(number_format(2 * $product1->price + 1 * $product2->price, 2, '.', '')));
        $this->assertTrue($order->customer_id === 1);

        $response->assertSessionHas('products', []);
        $response->assertRedirect(route('orders.index'));
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
     * An array of inputed customer's data.
     *
     * @return Array
     */
    private function dataOfCustomer()
    {
        return [
            'fio' => 'John Doe',
            'phone' => '+15896321478',
            'email' => 'johndoe@mail.com',
            'image' => $this->fakeUploadFile('avatar.jpg')
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
     * Helper for storing a customer
     * 
     * @return void
     */
    private function storeCustomer($user, $params)
    {
        $this->actingAs($user)
            ->post(route('customers.store'), $params);
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
