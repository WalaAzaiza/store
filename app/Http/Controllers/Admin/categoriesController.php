<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class categoriesController extends Controller
{
    public function index(Request $request)
    {
        $categories=Category::when($request->name,function($query,$value){
            $query->where('name','LIKE',"%$value%")
                  ->orWhere('description','LIKE',"%$value%");
        })
        ->when($request->parent_id,function($query,$value){
            $query->where('parent_id','=',$value);
        })
        ->leftJoin('categories as parents','parents.id','=','categories.parent_id')
        ->select([
            'categories.*',
            'parents.name as parent_name'
        ])
        ->get();

        $parents=Category::orderBy('name','asc')->get();
         return view('admin.categories.index',[
             'categories'=>$categories,
             'parents'=>$parents,
             ]);
    }
public function create()
{
    $parents=Category::orderBy('name','asc')->get();
    return view('admin.categories.create',['parents'=>$parents]);
}

public function store(Request $request)
{
      $validator=Validator::make(

        $request->all(),[
            'name'=>'required|max:255|min:3|unique:categories,name',
            'description'=>'nullable|min:5',
            'parent_id'=>[
                'nullable',
                'exists:categories,id'
            ],
            'image'=>[
                'nullable',
                'image',
                'max:1048576',
                'dimensions:min_width=200,min_height=300'
            ],
            'status'=>'required|in:active,inactive'

        ]

      );
      $a=$validator->fails();
      $b=$validator->failed();
      $errors=$validator->errors();

      $b=$validator->validate();




    $category =new Category();
    $category->name=$request->name;
    $category->description=$request->description;
    $category->status=$request->status;
    $category->slug=Str::slug($request->name);
    $category->parent_id=$request->post('parent_id');

    $category->save();

    return redirect(route('admin.categories.index'))->with('success','category add successfuly');


}
public function edit($id){
    // ???????????? where ?????? ???? ???????? ???????????????????? ????????????(???????? ???????? ?????? ?????????? ????????) ?????? ?????????? ??????????????
    $parents=Category::where('id','<>',$id)
    ->orderBy('name','asc')
    ->get();
    $category=Category::findOrFail($id);
   // if( $category==null){
    //    abort(404);

   // }
    return view('admin.categories.edit',[
        'id'=>$id,
        'parents'=>$parents,
        'category'=>$category,
    ]);

}
public function update(Request $request,$id){
    $category=Category::findOrFail($id);


    $category->name=$request->name;
    $category->description=$request->description;
    $category->status=$request->status;
    $category->slug=Str::slug($request->name);
    $category->parent_id=$request->post('parent_id');

    $category->save();

    return redirect()
    ->route('admin.categories.index')
    ->with('success','category updated successfuly');

}

public function destroy($id)
{
    Category::destroy($id);
    return redirect()
    ->route('admin.categories.index')
    ->with('success','category deleted');
}

}
