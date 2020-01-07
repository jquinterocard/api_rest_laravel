<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Validator;
class CategoryController extends Controller
{

	public function __construct(){
		$this->middleware('api.auth',['except'=>['index','show']]);
	}

	public function index(){
		$categories = Category::all();
		return response()->json([
			'code'=>200,
			'status'=>'success',
			'categories'=>$categories
		]);
	}

	public function show($id){
		$category = Category::find($id);
		if(is_object($category)){
			$data = [
				'code'=>200,
				'status'=>'success',
				'category'=>$category
			];
		}else{
			$data = [
				'code'=>404,
				'status'=>'error',
				'message'=>'La categoria no existe'
			];
		}
		return response()->json($data,$data['code']);
	}

	public function store(){
		//recoger datos por post
		$json = request()->input('json',null);
		$params_array = json_decode($json,true);
		
		if(!empty($params_array)){
			//validar los datos
			$validate = Validator::make($params_array,[
				'name'=>'required'
			]);


			
			if($validate->fails()){
				$data = array(
					'code'=>400,
					'status'=>'error',
					'message'=>'No se ha guardado la categoria',
					'errors'=>$validate->errors()
				);
			}else{
				//guardar la categoria
				$category = new Category();
				$category->name = $params_array['name'];
				$category->save();
				$data = array(
					'code'=>200,
					'status'=>'success',
					'category'=>$category
				);
			}
		}else{
			$data = array(
				'code'=>400,
				'status'=>'error',
				'message'=>'No has enviado ninguna categoria.'
			);
		}
		
		//devolver el resultado
		return response()->json($data,$data['code']);
	}

	public function update($id){
		//Recoger los datos por post
		$json = request()->input('json',null);
		$params_array = json_decode($json,true);

		if(!empty($params_array)){
			//validar los datos
			$validate = Validator::make($params_array,[
				'name'=>'required|alpha'
			]);

			if($validate->fails()){
				$data = array(
					'code'=>400,
					'status'=>'error',
					'message'=>'No se ha guardado la categoria',
					'errors'=>$validate->errors()
				);
			}else{
				//quitar lo que no quiero actualizar
				unset($params_array['id']);
				unset($params_array['created_at']);
				//actualizar la categoria
				$category = Category::where('id',$id)->update($params_array);
				$data = array(
					'code'=>200,
					'status'=>'success',
					'category'=>$params_array
				);		
			}
		}else{
			$data = array(
				'code'=>400,
				'status'=>'error',
				'message'=>'No has enviado ninguna categoria.'
			);
		}
		//devolver el resultado
		return response()->json($data,$data['code']);	
	}

}
