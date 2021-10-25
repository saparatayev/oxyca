<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_be_added()
    {
        $user = User::factory()->create();

        $response = $this->storeCustomer($user, $this->data());

        // check admin_created_id & admin_updated_id fields
        $customer = Customer::first();

        $this->assertCount(1, Customer::all());
        $this->assertTrue($customer->admin_created_id === $user->id);
        $this->assertTrue($customer->admin_updated_id === $user->id);

        $response->assertRedirect(route('customers.index'));
    }

    public function test_neccessary_folders_created_and_images_stored()
    {
        $user = User::factory()->create();

        $fileSystem = new Filesystem();
        $smImgDirectory = storage_path('app/public') . '/customers/sm';
        $lgImgDirectory = storage_path('app/public') . '/customers/lg';

        $this->storeCustomer($user, $this->data());
        
        $customer = Customer::first();
        
        // check if folders have been created
        $this->assertTrue($fileSystem->isDirectory($lgImgDirectory));
        $this->assertTrue($fileSystem->isDirectory($smImgDirectory));

        // check if images have been stored
        $this->assertTrue(Storage::exists('customers/sm/' . $customer->image));
        $this->assertTrue(Storage::exists('customers/lg/' . $customer->image));

        $smImageDimensions = getimagesize($smImgDirectory . '/' . $customer->image);
        $lgImageDimensions = getimagesize($lgImgDirectory . '/' . $customer->image);

        // check stored images' height & width
        $this->assertTrue($lgImageDimensions[0] === Config::get('mysetting.user_image_lg_w'));
        $this->assertTrue($lgImageDimensions[1] === Config::get('mysetting.user_image_lg_h'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.user_image_sm_w'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.user_image_sm_h'));
    }

    public function test_a_customer_can_be_updated()
    {
        $user = User::factory()->create();

        // first add a customer
        $this->storeCustomer($user, $this->data());
        $this->assertCount(1, Customer::all());
        $customerAfterAdding = Customer::first();

        // then update a customer
        $response = $this->actingAs($user)
            ->put(route('customers.update', ['customer' => $customerAfterAdding]), [
                'fio' => 'John Does New Name',
                'phone' => '+15555555555',
                'email' => 'johndoe_new_email@mail.com',
                'image' => $this->fakeUploadFile('new_avatar.jpg')
            ]);
        $this->assertCount(1, Customer::all());
        $customerAfterUpdating = Customer::first();

        $this->assertTrue($customerAfterAdding->fio != $customerAfterUpdating->fio);
        $this->assertTrue($customerAfterAdding->phone != $customerAfterUpdating->phone);
        $this->assertTrue($customerAfterAdding->email != $customerAfterUpdating->email);
        $this->assertTrue($customerAfterAdding->image != $customerAfterUpdating->image);

        $this->assertTrue($customerAfterUpdating->fio === 'John Does New Name');
        $this->assertTrue($customerAfterUpdating->phone === '+15555555555');
        $this->assertTrue($customerAfterUpdating->email === 'johndoe_new_email@mail.com');
        $this->assertTrue($customerAfterUpdating->admin_updated_id === $user->id);

        // check updated image
        $smImgDirectory = storage_path('app/public') . '/customers/sm';
        $lgImgDirectory = storage_path('app/public') . '/customers/lg';
        
        // check if new images have been stored
        $this->assertTrue(Storage::exists('customers/sm/' . $customerAfterUpdating->image));
        $this->assertTrue(Storage::exists('customers/lg/' . $customerAfterUpdating->image));

        // check if old images have been deleted
        $this->assertTrue(!Storage::exists('customers/sm/' . $customerAfterAdding->image));
        $this->assertTrue(!Storage::exists('customers/lg/' . $customerAfterAdding->image));

        $smImageDimensions = getimagesize($smImgDirectory . '/' . $customerAfterUpdating->image);
        $lgImageDimensions = getimagesize($lgImgDirectory . '/' . $customerAfterUpdating->image);

        // check stored new images' height & width
        $this->assertTrue($lgImageDimensions[0] === Config::get('mysetting.user_image_lg_w'));
        $this->assertTrue($lgImageDimensions[1] === Config::get('mysetting.user_image_lg_h'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.user_image_sm_w'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.user_image_sm_h'));
            
        $response->assertRedirect(route('customers.index'));
    }

    public function test_a_customer_can_be_deleted()
    {
        $user = User::factory()->create();

        $this->storeCustomer($user, $this->data());
        $customer = Customer::first();
        
        $response = $this->actingAs($user)
            ->delete(route('customers.destroy', ['customer' => $customer]));
        
        $this->assertCount(0, Customer::all());

        // check if images have been deleted
        $this->assertTrue(!Storage::exists('customers/sm/' . $customer->image));
        $this->assertTrue(!Storage::exists('customers/lg/' . $customer->image));

        $response->assertRedirect(route('customers.index'));
    }

    public function test_a_customer_with_related_orders_can_be_deleted()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->dataOfProduct());
        
        $this->storeCustomer($user, $this->data());
        $customer = Customer::first();

        $this->actingAs($user)
            ->get(route('cart.add', ['id' => 1])); // 1 is $product->id

        $this->actingAs($user)
            ->post(route('checkout'), [
                'customer_id' => 1,
                'cart_count' => 1
            ]);

        $this->actingAs($user)
            ->delete(route('customers.destroy', ['customer' => $customer]));

        $this->assertCount(0, Order::all());
        $this->assertCount(0, DB::table('orders_products')->get()); // check pivot
    }

    /**
     * Validation testings
     */
    public function test_required_fields()
    {
        $user = User::factory()->create();
        $response = $this->storeCustomer($user, [
            'fio' => '',
            'phone' => '',
            'email' => '',
            'image' => '',
        ]);

        $response->assertSessionHasErrors(['fio', 'phone', 'email', 'image']);
    }
    
    public function test_min_max_fields()
    {
        $user = User::factory()->create();
        $this->storeCustomer($user, array_merge($this->data(), [
            'fio' => 'k'
        ]))->assertSessionHasErrors(['fio']);

        $this->storeCustomer($user, array_merge($this->data(), [
            'fio' => \Str::random(257)
        ]))->assertSessionHasErrors(['fio']);
    }

    public function test_unique_fields()
    {
        $user = User::factory()->create();

        $this->storeCustomer($user, $this->data());
        $this->storeCustomer($user, $this->data())
            ->assertSessionHasErrors(['phone', 'email']);
    }

    public function test_phone_regex()
    {
        $user = User::factory()->create();
        $this->storeCustomer($user, array_merge($this->data(), [
            'phone' => '+158'
        ]))->assertSessionHasErrors(['phone']);
        $this->storeCustomer($user, array_merge($this->data(), [
            'phone' => '+dcdsec'
        ]))->assertSessionHasErrors(['phone']);
        $this->storeCustomer($user, array_merge($this->data(), [
            'phone' => '458963214781681815'
        ]))->assertSessionHasErrors(['phone']);
    }

    public function test_uploaded_image()
    {
        $user = User::factory()->create();
        $this->storeCustomer($user, array_merge($this->data(), [
            // width & height
            'image' => UploadedFile::fake()->image('avatar.jpg', 100, 100)->size(2000)
        ]))->assertSessionHasErrors(['image']);
        
        $this->storeCustomer($user, array_merge($this->data(), [
            // size
            'image' => UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(5001)
        ]))->assertSessionHasErrors(['image']);
        
        $this->storeCustomer($user, array_merge($this->data(), [
            // format
            'image' => UploadedFile::fake()->image('avatar.pdf', 1000)
        ]))->assertSessionHasErrors(['image']);
    }

    /**
     * An array of inputed customer's data.
     *
     * @return Array
     */
    private function data()
    {
        return [
            'fio' => 'John Doe',
            'phone' => '+14569631478',
            'email' => 'johndoe@mail.com',
            'image' => $this->fakeUploadFile('avatar.jpg')
        ];
    }

    /**
     * An array of inputed product's data.
     *
     * @return Array
     */
    private function dataOfProduct()
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
     * Helper for storing a customer
     * 
     * @return $response
     */
    private function storeCustomer($user, $params)
    {
        return $this->actingAs($user)
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
