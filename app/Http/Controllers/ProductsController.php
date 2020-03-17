<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Category;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Auth;
use Session;
use DB;
use Image;

class ProductsController extends Controller
{
    public function viewProducts(Request $request)
    {
        $products = Product::get();
        foreach ($products as $key => $val) {
            $category_name = Category::where(['id' => $val->category_id])->first();
            $products[$key]->category_name = $category_name->name;
        }
        $products = json_decode(json_encode($products));
        // echo "<pre>"; print_r($products); die;
        return view('admin.products.view_products')->with(compact('products'));
    }

    public function addProduct(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;

            $product = new Product;
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->product_code = $data['product_code'];
            $product->product_color = $data['product_color'];
            $product->weight =  !empty($data['weight']) ? $data['weight'] : 0;
            $product->description = !empty($data['description']) ? $data['description'] : '';
            $product->sleeve = !empty($data['sleeve']) ? $data['sleeve'] : '';
            $product->pattern = !empty($data['pattern']) ? $data['pattern'] : '';
            $product->care = !empty($data['care']) ? $data['care'] : '';
            $status =  empty($data['status']) ? '0' : '1';
            $feature_item =  empty($data['feature_item']) ? '0' : '1';
            $product->price = $data['price'];

            // Upload Image
            if ($request->hasFile('image')) {
                $image_tmp = Input::file('image');
                if ($image_tmp->isValid()) {
                    // Upload Images after Resize
                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111, 99999) . '.' . $extension;
                    $large_image_path = 'images/backend_images/product/large' . '/' . $fileName;
                    $medium_image_path = 'images/backend_images/product/medium' . '/' . $fileName;
                    $small_image_path = 'images/backend_images/product/small' . '/' . $fileName;

                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600, 600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300, 300)->save($small_image_path);

                    $product->image = $fileName;
                }
            }

            // Upload Video
            if ($request->hasFile('video')) {
                $video_tmp = Input::file('video');
                $video_name = $video_tmp->getClientOriginalName();
                $video_path = 'videos/';
                $video_tmp->move($video_path, $video_name);
                $product->video = $video_name;
            }

            $product->feature_item = $feature_item;
            $product->status = $status;
            $product->save();
            return redirect()->back()->with('flash_message_success', 'Product has been added successfully');
        }

        $categories = Category::where(['parent_id' => 0])->get();

        $categories_drop_down = "<option value='' selected disabled>Select</option>";
        foreach ($categories as $cat) {
            $categories_drop_down .= "<option value='" . $cat->id . "'>" . $cat->name . "</option>";
            $sub_categories = Category::where(['parent_id' => $cat->id])->get();
            foreach ($sub_categories as $sub_cat) {
                $categories_drop_down .= "<option value='" . $sub_cat->id . "'>&nbsp;&nbsp;--&nbsp;" . $sub_cat->name . "</option>";
            }
        }

        // echo "<pre>"; print_r($categories_drop_down); die;

        $sleeveArray = array('Full Sleeve', 'Half Sleeve', 'Short Sleeve', 'Sleeveless');

        $patternArray = array('Checked', 'Plain', 'Printed', 'Self', 'Solid');

        return view('admin.products.add_product')->with(compact('categories_drop_down', 'sleeveArray', 'patternArray'));
    }

    public function editProduct(Request $request, $id = null)
    {

        if ($request->isMethod('post')) {
            $data = $request->all();
            /*echo "<pre>"; print_r($data); die;*/

            if (empty($data['status'])) {
                $status = '0';
            } else {
                $status = '1';
            }

            if (empty($data['feature_item'])) {
                $feature_item = '0';
            } else {
                $feature_item = '1';
            }

            if (!empty($data['sleeve'])) {
                $sleeve = $data['sleeve'];
            } else {
                $sleeve = '';
            }

            if (!empty($data['pattern'])) {
                $pattern = $data['pattern'];
            } else {
                $pattern = '';
            }

            // Upload Image
            if ($request->hasFile('image')) {
                $image_tmp = Input::file('image');
                if ($image_tmp->isValid()) {
                    // Upload Images after Resize
                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111, 99999) . '.' . $extension;
                    $large_image_path = 'images/backend_images/product/large' . '/' . $fileName;
                    $medium_image_path = 'images/backend_images/product/medium' . '/' . $fileName;
                    $small_image_path = 'images/backend_images/product/small' . '/' . $fileName;

                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600, 600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300, 300)->save($small_image_path);
                }
            } else if (!empty($data['current_image'])) {
                $fileName = $data['current_image'];
            } else {
                $fileName = '';
            }

            // Upload Video
            if ($request->hasFile('video')) {
                $video_tmp = Input::file('video');
                $video_name = $video_tmp->getClientOriginalName();
                $video_path = 'videos/';
                $video_tmp->move($video_path, $video_name);
                $videoName = $video_name;
            } else if (!empty($data['current_video'])) {
                $videoName = $data['current_video'];
            } else {
                $videoName = '';
            }

            if (empty($data['description'])) {
                $data['description'] = '';
            }

            if (empty($data['care'])) {
                $data['care'] = '';
            }

            Product::where(['id' => $id])->update([
                'feature_item' => $feature_item, 'status' => $status, 'category_id' => $data['category_id'], 'product_name' => $data['product_name'],
                'product_code' => $data['product_code'], 'product_color' => $data['product_color'], 'description' => $data['description'], 'care' => $data['care'], 'price' => $data['price'], 'weight' => $data['weight'], 'image' => $fileName, 'video' => $videoName, 'sleeve' => $sleeve, 'pattern' => $pattern
            ]);

            return redirect()->back()->with('flash_message_success', 'Product has been edited successfully');
        }

        // Get Product Details start //
        $productDetails = Product::where(['id' => $id])->first();
        // Get Product Details End //

        // Categories drop down start //
        $categories = Category::where(['parent_id' => 0])->get();

        $categories_drop_down = "<option value='' disabled>Select</option>";
        foreach ($categories as $cat) {
            if ($cat->id == $productDetails->category_id) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $categories_drop_down .= "<option value='" . $cat->id . "' " . $selected . ">" . $cat->name . "</option>";
            $sub_categories = Category::where(['parent_id' => $cat->id])->get();
            foreach ($sub_categories as $sub_cat) {
                if ($sub_cat->id == $productDetails->category_id) {
                    $selected = "selected";
                } else {
                    $selected = "";
                }
                $categories_drop_down .= "<option value='" . $sub_cat->id . "' " . $selected . ">&nbsp;&nbsp;--&nbsp;" . $sub_cat->name . "</option>";
            }
        }
        // Categories drop down end //

        $sleeveArray = array('Full Sleeve', 'Half Sleeve', 'Short Sleeve', 'Sleeveless');

        $patternArray = array('Checked', 'Plain', 'Printed', 'Self', 'Solid');

        return view('admin.products.edit_product')->with(compact('productDetails', 'categories_drop_down', 'sleeveArray', 'patternArray'));
    }

    public function deleteProduct($id = null)
    {
        Product::where(['id' => $id])->delete();
        return redirect()->back()->with('flash_message_success', 'Product has been deleted successfully');
    }

    public function deleteProductImage($id)
    {

        // Get Product Image
        $productImage = Product::where('id', $id)->first();

        // Get Product Image Paths
        $large_image_path = 'images/backend_images/product/large/';
        $medium_image_path = 'images/backend_images/product/medium/';
        $small_image_path = 'images/backend_images/product/small/';

        // Delete Large Image if not exists in Folder
        if (file_exists($large_image_path . $productImage->image)) {
            unlink($large_image_path . $productImage->image);
        }

        // Delete Medium Image if not exists in Folder
        if (file_exists($medium_image_path . $productImage->image)) {
            unlink($medium_image_path . $productImage->image);
        }

        // Delete Small Image if not exists in Folder
        if (file_exists($small_image_path . $productImage->image)) {
            unlink($small_image_path . $productImage->image);
        }

        // Delete Image from Products table
        Product::where(['id' => $id])->update(['image' => '']);

        return redirect()->back()->with('flash_message_success', 'Product image has been deleted successfully');
    }

    public function deleteProductVideo($id)
    {
        // Get Video Name 
        $productVideo = Product::select('video')->where('id', $id)->first();

        // Get Video Path
        $video_path = 'videos/';

        // Delete Video if exists in videos folder
        if (file_exists($video_path . $productVideo->video)) {
            unlink($video_path . $productVideo->video);
        }

        // Delete Video from Products table
        Product::where('id', $id)->update(['video' => '']);

        return redirect()->back()->with('flash_message_success', 'Product Video has been deleted successfully');
    }

    public function addImages(Request $request, $id = null)
    {
        $productDetails = Product::where(['id' => $id])->first();

        $categoryDetails = Category::where(['id' => $productDetails->category_id])->first();
        $category_name = $categoryDetails->name;

        if ($request->isMethod('post')) {
            $data = $request->all();
            if ($request->hasFile('image')) {
                $files = $request->file('image');
                foreach ($files as $file) {
                    // Upload Images after Resize
                    $image = new ProductsImage;
                    $extension = $file->getClientOriginalExtension();
                    $fileName = rand(111, 99999) . '.' . $extension;
                    $large_image_path = 'images/backend_images/product/large' . '/' . $fileName;
                    $medium_image_path = 'images/backend_images/product/medium' . '/' . $fileName;
                    $small_image_path = 'images/backend_images/product/small' . '/' . $fileName;
                    Image::make($file)->save($large_image_path);
                    Image::make($file)->resize(600, 600)->save($medium_image_path);
                    Image::make($file)->resize(300, 300)->save($small_image_path);
                    $image->image = $fileName;
                    $image->product_id = $data['product_id'];
                    $image->save();
                }
            }

            return redirect('admin/add-images/' . $id)->with('flash_message_success', 'Product Images has been added successfully');
        }

        $productImages = ProductsImage::where(['product_id' => $id])->orderBy('id', 'DESC')->get();

        $title = "Add Images";
        return view('admin.products.add_images')->with(compact('title', 'productDetails', 'category_name', 'productImages'));
    }

    public function deleteProductAltImage($id = null)
    {

        // Get Product Image
        $productImage = ProductsImage::where('id', $id)->first();

        // Get Product Image Paths
        $large_image_path = 'images/backend_images/product/large/';
        $medium_image_path = 'images/backend_images/product/medium/';
        $small_image_path = 'images/backend_images/product/small/';

        // Delete Large Image if not exists in Folder
        if (file_exists($large_image_path . $productImage->image)) {
            unlink($large_image_path . $productImage->image);
        }

        // Delete Medium Image if not exists in Folder
        if (file_exists($medium_image_path . $productImage->image)) {
            unlink($medium_image_path . $productImage->image);
        }

        // Delete Small Image if not exists in Folder
        if (file_exists($small_image_path . $productImage->image)) {
            unlink($small_image_path . $productImage->image);
        }

        // Delete Image from Products Images table
        ProductsImage::where(['id' => $id])->delete();

        return redirect()->back()->with('flash_message_success', 'Product alternate mage has been deleted successfully');
    }
}
