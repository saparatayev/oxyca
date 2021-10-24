<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_be_added()
    {
        $this->withoutExceptionHandling();

        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(2000);

        $response = $this->actingAs($user = User::factory()->create())
            ->post(route('customers.store'), array_merge($this->data(), [
                'image' => $file
            ]));

        // check admin_created_id & admin_updated_id fields
        $customer = Customer::first();

        $this->assertCount(1, Customer::all());
        $this->assertTrue($customer->admin_created_id === $user->id);
        $this->assertTrue($customer->admin_updated_id === $user->id);

        $response->assertRedirect(route('customers.index'));
    }

    public function test_neccessary_folders_created_and_images_stored()
    {
        $this->withoutExceptionHandling();

        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(2000);

        $fileSystem = new Filesystem();
        $smImgDirectory = storage_path('app/public') . '/customers/sm';
        $lgImgDirectory = storage_path('app/public') . '/customers/lg';

        $this->actingAs(User::factory()->create())
            ->post(route('customers.store'), array_merge($this->data(), [
                'image' => $file
            ]));
        
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
        $this->withoutExceptionHandling();

        // first add a customer
        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(2000);
        $this->actingAs($user = User::factory()->create())
            ->post(route('customers.store'), array_merge($this->data(), [
                'image' => $file
            ]));
        $this->assertCount(1, Customer::all());
        $customerAfterAdding = Customer::first();

        // then update a customer
        $file = UploadedFile::fake()->image('new_avatar.jpg', 300, 300)->size(2000);
        $response = $this->actingAs($user)
            ->put(route('customers.update', ['customer' => $customerAfterAdding]), [
                'fio' => 'John Does New Name',
                'phone' => '+15555555555',
                'email' => 'johndoe_new_email@mail.com',
                'image' => $file
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

    /**
     * Validation testings
     */
    public function test_required_fields()
    {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('customers.store'), [
                'fio' => '',
                'phone' => '',
                'email' => '',
                'image' => '',
            ]);

        $response->assertSessionHasErrors(['fio', 'phone', 'email', 'image']);
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
            'image' => 'avatar.jpg'
        ];
    }
}
