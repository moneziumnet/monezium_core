<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Support\Str;

use Validator;

class BlogController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    //*** JSON Request
    public function datatables(Request $request)
    {
         $datas = Blog::orderBy('id','desc')->get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
            ->editColumn('photo', function(Blog $data) {
                $photo = $data->photo ? url('assets/images/'.explode(',',$data->photo)[0]):url('assets/images/noimage.png');
                return '<img src="' . $photo . '" alt="Image">';
            })
            ->editColumn('date', function(Blog $data) {
                return dateFormat($data->created_at, 'm/d/Y H:i');
            })

            ->editColumn('category', function(Blog $data) {
                return $data->category->name;
            })

            ->editColumn('status', function(Blog $data) {
                return $data->status == 1 ? 'Active' : 'Deactive' ;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('global_search'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        if (Str::contains(Str::lower($row['tags']), Str::lower($request->get('global_search')))) {
                            return true;
                        }
                        else {
                            return false;
                        }
                    });
                }
                if (!empty($request->get('category'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request) {
                        return Str::contains(Str::lower($row['category']), Str::lower($request->get('category'))) ? true : false;
                    });
                }
            })

            ->addColumn('action', function(Blog $data) {
                $status = 1;
                $status_str = 'Active';
                if($data->status == 1) {
                    $status = 0;
                    $status_str = "Deactive";
                }

                return '<div class="btn-group mb-1">
                <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.'Actions' .'
                </button>
                <div class="dropdown-menu" x-placement="bottom-start">
                    <a href="' . route('admin.blog.edit',$data->id) . '"  class="dropdown-item">'.__("Edit").'</a>
                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="'.  route('admin.blog.delete',$data->id).'">'.__("Delete").'</a>
                    <a href="javascript:;" data-toggle="modal" data-target="#statusModal" class="dropdown-item" data-href="'.  route('admin.blog.status',[$data->id, $status]).'">'.__($status_str).'</a>
                </div>
                </div>';

            })
            ->rawColumns(['photo','date', 'category', 'status','action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index()
    {
        $modules = BlogCategory::all();
        return view('admin.blog.index', compact('modules'));
    }

    public function create()
    {
        $data['cats'] = BlogCategory::all();
        return view('admin.blog.create',$data);
    }


    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
               'photo'      => 'required',
               'photo.*'      => 'mimes:jpeg,jpg,png,svg',
               'title'=>'required'
                ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new Blog();
        $input = $request->all();
        $photo_list = [];

        if ($request->hasfile('photo'))
         {
            foreach($request->file('photo') as $file)
           {
            $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images',$name);
            array_push($photo_list, $name);
           }
        }

        $input['photo'] =  implode(",",$photo_list);


         // ------------------------TagFormat--------------------------//
            $input['slug']=Str::random(8).'-'.Str::slug(trim($request->title));
            $common_rep   = ["value", "{", "}", "[","]",":","\""];
            $tag = str_replace($common_rep, '', $request->tags);
            $metatag = str_replace($common_rep, '', $request->meta_tag);



        if (!empty($metatag))
        {
            $input['meta_tag'] = $metatag;
        }
        $input['status'] = $request->status == 'status_checked' ? 1 : 0;


        if (!empty($tag))
         {
            $input['tags'] = $tag;
         }
        if ($request->secheck == "")
         {
            $input['meta_tag'] = null;
            $input['meta_description'] = null;
         }
         $input['views'] = 0;
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section
        $msg = 'New Data Added Successfully.'.'<a href="'.route("admin.blog.index").'">View Post Lists</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }

    //*** GET Request
    public function edit($id)
    {
        $cats = BlogCategory::all();
        $data = Blog::findOrFail($id);
        return view('admin.blog.edit',compact('data','cats'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        $rules = [
                'photo'      => 'required',
                'photo.*'      => 'mimes:jpeg,jpg,png,svg',
               'title'=>'required',
               'slug' => 'required|unique:blogs,slug,'.$id,
                ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = Blog::findOrFail($id);
        $input = $request->all();
        $photo_list = [];

        if ($request->hasfile('photo'))
        {
            foreach (explode(',', $data->photo) as $value) {
                @unlink('assets/images/'.$value);
            }
            foreach($request->file('photo') as $file)
            {
                $name = Str::random(8).time().'.'.$file->getClientOriginalExtension();
                $file->move('assets/images',$name);
                array_push($photo_list, $name);
            }
        }

        $input['photo'] =  implode(",",$photo_list);

            $common_rep   = ["value", "{", "}", "[","]",":","\""];
            $tag = str_replace($common_rep, '', $request->tags);
            $metatag = str_replace($common_rep, '', $request->meta_tag);



        if (!empty($metatag))
        {
            $input['meta_tag'] = $metatag;
        }
        if (!empty($tag))
         {
            $input['tags'] = $tag;
         }
        if ($request->secheck == "")
         {
            $input['meta_tag'] = null;
            $input['meta_description'] = null;
         }
         $input['slug']=Str::slug($request->slug);

        $data->update($input);

        $msg = 'Data Updated Successfully.'.'<a href="'.route("admin.blog.index").'">View Post Lists</a>';
        return response()->json($msg);
    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $data = Blog::findOrFail($id);
        foreach (explode(',', $data->photo) as $value) {
            @unlink('assets/images/'.$value);
        }
        $data->delete();


        $msg = 'Data Deleted Successfully.';
        return response()->json($msg);
    }

    public function status($id, $status)
    {
        $data = Blog::findOrFail($id);
        $data->status = $status;
        $data->update();


        $msg = 'Data Updated Successfully.';
        return response()->json($msg);
    }
}
