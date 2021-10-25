<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrdersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_can_be_deleted()
    {
        $this->withExceptionHandling();
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());
        
        $this->storeCustomer($user, $this->dataOfCustomer());

        $this->actingAs($user)
            ->get(route('cart.add', ['id' => Product::first()->id])); // 1 is $product->id

        $this->actingAs($user)
            ->post(route('checkout'), [
                'customer_id' => Customer::first()->id,
                'cart_count' => 1
            ]);

        $this->actingAs($user)
            ->delete(route('orders.destroy', ['order' => Order::first()]));

        $this->assertCount(0, Order::all());
        $this->assertCount(0, DB::table('orders_products')->get()); // check pivots
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
