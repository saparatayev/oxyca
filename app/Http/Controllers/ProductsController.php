<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class ProductsController extends AdminController
{
    public $storageUrl;

    public function __construct() {
        $this->storageUrl = Storage::url('products/');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('products')->with([
            'products' => Product::all(),
            'storageUrl' => $this->storageUrl,
            'cartCount'=>$this->countItems($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create');
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
            'title' => 'required|min:2|max:256',
            'sku' => 'required|unique:products,sku|regex:/^[0-9A-Z]{5}$/',
            'price' => 'required|gt:0',
            'image' => 'required|max:5000|dimensions:min_width=300,min_height=300|mimes:jpg,png',
        ];

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $product = new Product();

            // create folders for images if no folders
            $file = new Filesystem();
            $smImgDirectory = storage_path('app/public') . '/products/sm';
            $lgImgDirectory = storage_path('app/public') . '/products/lg';
            
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

            $hardPath = 'product_' . \Str::random(100) . '.jpg';

            Image::make($file)
                ->fit(Config::get('mysetting.product_image_lg_w'), Config::get('mysetting.product_image_lg_h'))
                ->save($lgImgDirectory . '/' . $hardPath, 80);

            Image::make($file)
                ->fit(Config::get('mysetting.product_image_sm_w'), Config::get('mysetting.product_image_sm_h'))
                ->save($smImgDirectory . '/' . $hardPath, 80);


            $input['image'] = $hardPath;
            $input['admin_created_id'] = \Auth::user()->id;
            $input['admin_updated_id'] = \Auth::user()->id;

            $product->fill($input);
            $product->save();

        } catch (\Throwable $th) {
            return redirect()->back()->with('error','New product adding error 500');
        }

        return redirect()->route('products.index')->with('status', 'New Product Added');
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
        $product = Product::find($id);
        if (!$product) {
            abort(404);
        }
        return view('products.edit', compact('product'))->with([
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
        $product = Product::find($id);
        if (!$product) {
            abort(404);
        }

        $input = $request->all();
        $rules = [
            'title' => 'required|min:2|max:256',
            'sku' => 'required|regex:/[0-9A-Z]{5}/|unique:products,sku,' . $product->id,
            'price' => 'required|gt:0',
            'image' => 'max:5000|dimensions:min_width=300,min_height=300|mimes:jpg,png',
        ];

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            //code...
            if($request->hasFile('image')) {
                $smImgDirectory = storage_path('app/public') . '/products/sm';
                $lgImgDirectory = storage_path('app/public') . '/products/lg';

                if($product->image) {
                    app(Filesystem::class)->delete($smImgDirectory . '/' . $product->image);
                    app(Filesystem::class)->delete($lgImgDirectory . '/' . $product->image);
                }

                // process image
                $file = $request->file('image');

                $hardPath = 'product_' . \Str::random(100) . '.jpg';

                Image::make($file)
                    ->fit(Config::get('mysetting.product_image_lg_w'), Config::get('mysetting.product_image_lg_h'))
                    ->save($lgImgDirectory . '/' . $hardPath, 80);

                Image::make($file)
                    ->fit(Config::get('mysetting.product_image_sm_w'), Config::get('mysetting.product_image_sm_h'))
                    ->save($smImgDirectory . '/' . $hardPath, 80);


                
            } else {
                $hardPath = $product->image; // if no image, leave old image name
            }

            $input['image'] = $hardPath;
            $input['admin_created_id'] = $product->admin_created_id; // fill as it is
            $input['admin_updated_id'] = \Auth::user()->id;

            $product->fill($input);
            $product->update();

        } catch (\Throwable $th) {
            return redirect()->back()->with('error','Product editing error 500');
        }


        return redirect()->route('products.index')->with('status', 'Product updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::with(['orders'])->find($id);

        $this->authorize('delete', $product);

        // if(auth()->user()->can('delete', $product)) {
            
            if(!$product) {
                abort(404);
            }

            DB::beginTransaction();

            try {
                // deleting images
                if ($product->image) {
                    $smImgDirectory = storage_path('app/public') . '/products/sm';
                    $lgImgDirectory = storage_path('app/public') . '/products/lg';

                    app(Filesystem::class)->delete($smImgDirectory . '/' . $product->image);
                    app(Filesystem::class)->delete($lgImgDirectory . '/' . $product->image);
                }

                // $product->orders()->detach();
                // $product->orders()->delete();
                foreach($product->orders as $ord) {
                    $ord->products()->detach();
                    $ord->delete();
                }
            
                $product->delete();
                
            } catch (\Throwable $th) {
                DB::rollback();
                return redirect()->back()->with('error','Product deleting error 500');
            }

            DB::commit();    

            return redirect()->route('products.index')->with('status', 'Deleted Product succesfully');
            
        // } else {
        //     return redirect()->route('products.index')->with('error', 'Forbidden 403');
        // }
    
    }
}
