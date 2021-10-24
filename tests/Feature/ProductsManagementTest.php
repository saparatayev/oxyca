<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_added()
    {
        $this->withoutExceptionHandling();

        $file = UploadedFile::fake()->image('product.jpg', 300, 300)->size(2000);

        $response = $this->actingAs($user = User::factory()->create())
            ->post(route('products.store'), array_merge($this->data(), [
                'image' => $file
            ]));

        // check admin_created_id & admin_updated_id fields
        $product = Product::first();

        $this->assertCount(1, Product::all());
        $this->assertTrue($product->admin_created_id === $user->id);
        $this->assertTrue($product->admin_updated_id === $user->id);

        $response->assertRedirect(route('products.index'));
    }

    public function test_neccessary_folders_created_and_images_stored()
    {
        $this->withoutExceptionHandling();

        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(2000);

        $fileSystem = new Filesystem();
        $smImgDirectory = storage_path('app/public') . '/products/sm';
        $lgImgDirectory = storage_path('app/public') . '/products/lg';

        $this->actingAs(User::factory()->create())
            ->post(route('products.store'), array_merge($this->data(), [
                'image' => $file
            ]));
        
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
        $this->withoutExceptionHandling();

        // first add a product
        $file = UploadedFile::fake()->image('product.jpg', 300, 300)->size(2000);
        $this->actingAs($user = User::factory()->create())
            ->post(route('products.store'), array_merge($this->data(), [
                'image' => $file
            ]));
        $this->assertCount(1, Product::all());
        $productAfterAdding = Product::first();

        // then update a product
        $file = UploadedFile::fake()->image('new_product.jpg', 300, 300)->size(2000);
        $response = $this->actingAs($user)
            ->put(route('products.update', ['product' => $productAfterAdding]), [
                'title' => 'Lorem ipsum New Title',
                'sku' => 'BSY88',
                'price' => 259.36,
                'image' => $file
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

    /**
     * Validation testings
     */
    public function test_required_fields()
    {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('products.store'), [
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
            'image' => 'product.jpg'
        ];
    }
}
