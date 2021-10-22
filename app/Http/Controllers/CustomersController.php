<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Config;
use Illuminate\Filesystem\Filesystem;

class CustomersController extends Controller
{
    public $storageUrl;

    public function __construct() {
        $this->storageUrl = Storage::url('customers/');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('customers')->with([
            'customers' => Customer::all(),
            'storageUrl' => $this->storageUrl
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $rules = [
            'fio' => 'required|min:2|max:256',
            /**
             * Telephone numbers in Canada 1 XXX XXX XXXX
             */
            'phone' => 'required|unique:customers,phone|regex:/[+]{1}[1]{1}[0-9]{10}/',
            'email' => 'required|unique:customers,email|email',
            'image' => 'required|max:5000|dimensions:min_width=300,min_height=300|mimes:jpg,png',
        ];

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $customer = new Customer();
            
            // create folders for images if no folders
            $file = new Filesystem();
            $smImgDirectory = storage_path('app/public') . '/customers/sm';
            $lgImgDirectory = storage_path('app/public') . '/customers/lg';
            
            // directory for 300x300 images
            if ( !$file->isDirectory($lgImgDirectory))
            {
                $file->makeDirectory($lgImgDirectory, 755, true, true);
            }
            
            // directory for 70x70 images
            if ( !$file->isDirectory($smImgDirectory))
            {
                $file->makeDirectory($smImgDirectory, 755, true, true);
            }

            // process image
            $file = $request->file('image');

            $hardPath = 'customer_' . \Str::random(100) . '.jpg';

            Image::make($file)
                ->fit(Config::get('mysetting.user_image_lg_w'), Config::get('mysetting.user_image_lg_h'))
                ->save($lgImgDirectory . '/' . $hardPath, 80);

            Image::make($file)
                ->fit(Config::get('mysetting.user_image_sm_w'), Config::get('mysetting.user_image_sm_h'))
                ->save($smImgDirectory . '/' . $hardPath, 80);


            $input['image'] = $hardPath;
            $input['admin_created_id'] = \Auth::user()->id;
            $input['admin_updated_id'] = \Auth::user()->id;

            $customer->fill($input);
            $customer->save();

        } catch (\Throwable $th) {
            return redirect()->back()->with('error','New customer adding error 500');
        }

        return redirect()->route('customers.index')->with('status', 'New Customer Added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            abort(404);
        }
        return view('customers.edit', compact('customer'))->with([
            'storageUrl' => $this->storageUrl
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            abort(404);
        }

        $input = $request->all();
        $rules = [
            'fio' => 'required|min:2|max:256',
            /**
             * Telephone numbers in Canada 1 XXX XXX XXXX
             */
            'phone' => 'required|regex:/[+]{1}[1]{1}[0-9]{10}/|unique:customers,phone,' . $customer->id, // must be unique, but this is for editing
            'email' => 'required|email|unique:customers,email,' . $customer->id, // must be unique, but this is for editing
            'image' => 'max:5000|dimensions:min_width=300,min_height=300|mimes:jpg,png',
        ];

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // try {
            if($request->hasFile('image')) {
                $smImgDirectory = storage_path('app/public') . '/customers/sm';
                $lgImgDirectory = storage_path('app/public') . '/customers/lg';

                if($customer->image) {
                    app(Filesystem::class)->delete($smImgDirectory . '/' . $customer->image);
                    app(Filesystem::class)->delete($lgImgDirectory . '/' . $customer->image);
                }

                // process image
                $file = $request->file('image');

                $hardPath = 'customer_' . \Str::random(100) . '.jpg';

                Image::make($file)
                    ->fit(Config::get('mysetting.user_image_lg_w'), Config::get('mysetting.user_image_lg_h'))
                    ->save($lgImgDirectory . '/' . $hardPath, 80);

                Image::make($file)
                    ->fit(Config::get('mysetting.user_image_sm_w'), Config::get('mysetting.user_image_sm_h'))
                    ->save($smImgDirectory . '/' . $hardPath, 80);


                
            } else {
                $hardPath = $customer->image; // if no image, leave old image name
            }

            $input['image'] = $hardPath;
            $input['admin_created_id'] = $customer->admin_created_id; // fill as it is
            $input['admin_updated_id'] = \Auth::user()->id;

            $customer->fill($input);
            $customer->update();

        // } catch (\Throwable $th) {
        //     return redirect()->back()->with('error','Customer editing error 500');
        // }


        return redirect()->route('customers.index')->with('status', 'Customer updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
