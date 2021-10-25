<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_added()
    {
        $user = User::factory()->create();

        $response = $this->storeProduct($user, $this->data());

        // check admin_created_id & admin_updated_id fields
        $product = Product::first();

        $this->assertCount(1, Product::all());
        $this->assertTrue($product->admin_created_id === $user->id);
        $this->assertTrue($product->admin_updated_id === $user->id);

        $response->assertRedirect(route('products.index'));
    }

    public function test_neccessary_folders_created_and_images_stored()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());

        $fileSystem = new Filesystem();
        $smImgDirectory = storage_path('app/public') . '/products/sm';
        $lgImgDirectory = storage_path('app/public') . '/products/lg';

        $product = Product::first();
        
        // check if folders have been created
        $this->assertTrue($fileSystem->isDirectory($lgImgDirectory));
        $this->assertTrue($fileSystem->isDirectory($smImgDirectory));

        // check if images have been stored
        $this->assertTrue(Storage::exists('products/sm/' . $product->image));
        $this->assertTrue(Storage::exists('products/lg/' . $product->image));

        $smImageDimensions = getimagesize($smImgDirectory . '/' . $product->image);
        $lgImageDimensions = getimagesize($lgImgDirectory . '/' . $product->image);

        // check stored images' height & width
        $this->assertTrue($lgImageDimensions[0] === Config::get('mysetting.product_image_lg_w'));
        $this->assertTrue($lgImageDimensions[1] === Config::get('mysetting.product_image_lg_h'));
        $this->assertTrue($smImageDimensions[0] === Config::get('mysetting.product_image_sm_w'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.product_image_sm_h'));
    }

    public function test_a_product_can_be_updated()
    {
        $user = User::factory()->create();

        // first add a product
        $this->storeProduct($user, $this->data());
        $this->assertCount(1, Product::all());
        $productAfterAdding = Product::first();

        // then update a product
        $response = $this->actingAs($user)
            ->put(route('products.update', ['product' => $productAfterAdding]), [
                'title' => 'Lorem ipsum New Title',
                'sku' => 'BSY88',
                'price' => 259.36,
                'image' => $this->fakeUploadFile('new_product.jpg')
            ]);
        $this->assertCount(1, Product::all());
        $productAfterUpdating = Product::first();

        $this->assertTrue($productAfterAdding->title != $productAfterUpdating->title);
        $this->assertTrue($productAfterAdding->sku != $productAfterUpdating->sku);
        $this->assertTrue($productAfterAdding->price != $productAfterUpdating->price);
        $this->assertTrue($productAfterAdding->image != $productAfterUpdating->image);

        $this->assertTrue($productAfterUpdating->title === 'Lorem ipsum New Title');
        $this->assertTrue($productAfterUpdating->sku === 'BSY88');
        $this->assertTrue($productAfterUpdating->price === 259.36);
        $this->assertTrue($productAfterUpdating->admin_updated_id === $user->id);

        // check updated image
        $smImgDirectory = storage_path('app/public') . '/products/sm';
        $lgImgDirectory = storage_path('app/public') . '/products/lg';
        
        // check if new images have been stored
        $this->assertTrue(Storage::exists('products/sm/' . $productAfterUpdating->image));
        $this->assertTrue(Storage::exists('products/lg/' . $productAfterUpdating->image));

        // check if old images have been deleted
        $this->assertTrue(!Storage::exists('products/sm/' . $productAfterAdding->image));
        $this->assertTrue(!Storage::exists('products/lg/' . $productAfterAdding->image));

        $smImageDimensions = getimagesize($smImgDirectory . '/' . $productAfterUpdating->image);
        $lgImageDimensions = getimagesize($lgImgDirectory . '/' . $productAfterUpdating->image);

        // check stored new images' height & width
        $this->assertTrue($smImageDimensions[0] === Config::get('mysetting.product_image_sm_w'));
        $this->assertTrue($smImageDimensions[1] === Config::get('mysetting.product_image_sm_h'));
        $this->assertTrue($lgImageDimensions[0] === Config::get('mysetting.product_image_lg_w'));
        $this->assertTrue($lgImageDimensions[1] === Config::get('mysetting.product_image_lg_h'));
            
        $response->assertRedirect(route('products.index'));
    }

    public function test_a_product_can_be_deleted()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());
        $product = Product::first();

        $response = $this->actingAs($user)
            ->delete(route('products.destroy', ['product' => $product]));
        
        $this->assertCount(0, Product::all());

        // check if images have been deleted
        $this->assertTrue(!Storage::exists('products/sm/' . $product->image));
        $this->assertTrue(!Storage::exists('products/lg/' . $product->image));

        $response->assertRedirect(route('products.index'));
    }

    public function test_a_product_with_related_orders_can_be_deleted()
    {
        $user = User::factory()->create();

        $this->storeProduct($user, $this->data());
        $product = Product::first();
        
        $this->storeCustomer($user, $this->dataOfCustomer());

        $this->actingAs($user)
            ->get(route('cart.add', ['id' => 1])); // 1 is $product->id

        $this->actingAs($user)
            ->post(route('checkout'), [
                'customer_id' => 1,
                'cart_count' => 1
            ]);

        $this->actingAs($user)
            ->delete(route('products.destroy', ['product' => $product]));

        $this->assertCount(0, Order::all());
        $this->assertCount(0, DB::table('orders_products')->get()); // check pivot
    }

    /**
     * Validation testings
     */
    public function test_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->storeProduct($user, [
            'title' => '',
            'sku' => '',
            'price' => '',
            'image' => ''
        ]);

        $response->assertSessionHasErrors(['title', 'sku', 'price', 'image']);
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
     * @return $response
     */
    private function storeProduct($user, $params)
    {
        return $this->actingAs($user)
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
