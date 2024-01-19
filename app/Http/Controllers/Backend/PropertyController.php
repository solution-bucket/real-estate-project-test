<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\Amenities;
use App\Models\PropertyType;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class PropertyController extends Controller
{
    public function AllProperty(){

        $property = Property::latest()->get();
        return view('backend.property.all_property',compact('property'));

    } // End Method

    public function AddProperty(){

         $propertytype = PropertyType::latest()->get();
        // $pstate = State::latest()->get();
         $amenities = Amenities::latest()->get();
         $activeAgent = User::where('status','active')->where('role','agent')->latest()->get();
        return view('backend.property.add_property',compact('propertytype','amenities','activeAgent'));

    }// End Method

    public function StoreProperty(Request $request){
        $amen = $request->amenities_id;
        $amenites = implode(",", $amen);

        $pcode = IdGenerator::generate(['table' => 'properties','field' => 'property_code','length' => 5, 'prefix' => 'PC' ]);

            if($request->file('property_thambnail')){
                $manager = new ImageManager(new Driver());
                $name_gen = hexdec(uniqid()).'.'.$request->file('property_thambnail')->getClientOriginalExtension();
              $img = $manager->read($request->file('property_thambnail'));
              $img = $img->resize(370,246);
          $img->toJpeg(80)->save(base_path('public/upload/property/thambnail/'. $name_gen));
          $save_url = 'upload/property/thambnail/'.$name_gen;

          $property_id = Property::insertGetId([

            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenites,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
            'property_code' => $pcode,
            'property_status' => $request->property_status,

            'lowest_price' => $request->lowest_price,
            'max_price' => $request->max_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'garage' => $request->garage,
            'garage_size' => $request->garage_size,

            'property_size' => $request->property_size,
            'property_video' => $request->property_video,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,

            'neighborhood' => $request->neighborhood,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => $request->agent_id,
            'status' => 1,
            'property_thambnail' => $save_url,
            'created_at' => Carbon::now(),
        ]);
             if($request->file('multi_img')!= NULL) {
            $data = $request->file('multi_img');
                foreach($data as $images)
                {
                    foreach($data as $images)
                    {
                      $manager = new ImageManager(new Driver());
                      $img_group = hexdec(uniqid()).'.'.$images->getClientOriginalExtension();
                      $manager->read($img);
                      $img = $img->resize(770,520);
                      $img->toJpeg(80)->save(base_path('public/upload/property/multi-image/'. $img_group));
                      $save_img_group = 'upload/property/multi-image/'.$img_group;

                      MultiImage::insert([

                        'property_id' => $property_id,
                        'photo_name' => $save_img_group,
                       'created_at' => Carbon::now(),

                   ]);
                    }

                  MultiImage::insert([

                    'property_id' => $property_id,
                    'photo_name' => $save_img_group,
                   'created_at' => Carbon::now(),

               ]);
                }

          }

    }

     $facilities = Count($request->facility_name);

        if ($facilities != NULL) {
           for ($i=0; $i < $facilities; $i++) {
               $fcount = new Facility();
               $fcount->property_id = $property_id;
               $fcount->facility_name = $request->facility_name[$i];
               $fcount->distance = $request->distance[$i];
               $fcount->save();
           }
        }
        $notification = array(
            'message' => 'Property Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification);


    }
    public function EditProperty($id){

        $facilities = Facility::where('property_id',$id)->get();
        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_ami = explode(',', $type);

        $multiImage = MultiImage::where('property_id',$id)->get();

        $propertytype = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();
        $activeAgent = User::where('status','active')->where('role','agent')->latest()->get();

        return view('backend.property.edit_property',compact('property','propertytype','amenities','activeAgent','property_ami','multiImage','facilities'));

    }// End Method

    public function UpdateProperty(Request $request){

        $amen = $request->amenities_id;
        $amenites = implode(",", $amen);

        $property_id = $request->id;

        Property::findOrFail($property_id)->update([

            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenites,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
            'property_status' => $request->property_status,

            'lowest_price' => $request->lowest_price,
            'max_price' => $request->max_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'garage' => $request->garage,
            'garage_size' => $request->garage_size,

            'property_size' => $request->property_size,
            'property_video' => $request->property_video,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,

            'neighborhood' => $request->neighborhood,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => $request->agent_id,
            'updated_at' => Carbon::now(),

        ]);

         $notification = array(
            'message' => 'Property Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification);

    }// End Method

    public function UpdatePropertyThambnail(Request $request){

        $pro_id = $request->id;
        $oldImage = $request->old_img;
      $manager = new ImageManager(new Driver());
      $name_gen = hexdec(uniqid()).'.'.$request->file('property_thambnail')->getClientOriginalExtension();
      $img = $manager->read($request->file('property_thambnail'));
      $img = $img->resize(370,246);
      $img->toJpeg(80)->save(base_path('public/upload/property/thambnail/'. $name_gen));
      $save_url = 'upload/property/thambnail/'.$name_gen;

      if (file_exists($oldImage)) {
        unlink($oldImage);
    }

    Property::findOrFail($pro_id)->update([

        'property_thambnail' => $save_url,
        'updated_at' => Carbon::now(),
    ]);

     $notification = array(
        'message' => 'Property Image Thambnail Updated Successfully',
        'alert-type' => 'success'
    );

    return redirect()->back()->with($notification);

    }// End Method

    public function UpdatePropertyMultiimage(Request $request){

        $imgs = $request->multi_img;
        foreach($imgs as $id => $img){
            $imgDel = MultiImage::findOrFail($id);
            unlink($imgDel->photo_name);
            $manager = new ImageManager(new Driver());
            $img = $img->resize(770,520);
                      $img_group = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
                      $img->toJpeg(80)->save(base_path('public/upload/property/multi-image/'. $img_group));
                      $uploadPath = 'public/upload/property/multi-image/'.$img_group;

                MultiImage::where('id',$id)->update([
               'photo_name' => $uploadPath,
               'updated_at' => Carbon::now(),

           ]);
           $notification = array(
            'message' => 'Property Multi Image Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

        } // End Foreach





    }

}
