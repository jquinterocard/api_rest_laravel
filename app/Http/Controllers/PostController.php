<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Helpers\JwtAuth;
use Validator;
class PostController extends Controller
{
	public function __construct(){
		$this->middleware('api.auth',['except'=>['index','show','getImage','getPostsByCategory','getPostsByUser']]);
	}

	public function index(){
		$posts =  Post::all()->load('category');
		return response()->json([
			'code'=>200,
			'status'=>'success',
			'posts'=>$posts
		],200);
	}

	public function show($id){
		$post = Post::find($id)->load('category')
							   ->load('user');
		if(is_object($post)){
			$data = [
				'code'=>200,
				'status'=>'success',
				'post'=>$post
			];
		}else{
			$data = [
				'code'=>404,
				'status'=>'error',
				'message'=>'La entrada no existe'
			];
		}
		return response()->json($data,$data['code']);
	}

	public function store(){
		//recoger datos por post
		$json = request()->input('json',null);
		$params_array = json_decode($json,true);
		if(!empty($params_array)){
			//conseguir el usuario identificado
			$jwtAuth = new JwtAuth();
			$token = request()->header('Authorization',null);
			$user = $jwtAuth->checkToken($token,true);
			//validar los datos
			$validate = Validator::make($params_array,[
				'title'=>'required',
				'content'=>'required',
				'category_id'=>'required',
				'image'=>'required'
			]);
			if($validate->fails()){
				$data = [
					'code'=>404,
					'status'=>'error',
					'message'=>'No se ha guardado el post, faltan datos'
				];
			}else{
				//guardar el articulo
				$post = new Post();
				$post->user_id = $user->sub;
				$post->category_id = $params_array['category_id'];
				$post->title = $params_array['title'];
				$post->content = $params_array['content'];
				$post->image = $params_array['image'];
				$post->save();
				$data = [
					'code'=>200,
					'status'=>'success',
					'post'=>$post
				];
			}
			
		}else{
			$data = [
				'code'=>400,
				'status'=>'error',
				'message'=>'Envia los datos correctamente'
			];
		}
		//devolver la respuesta
		return response()->json($data,$data['code']);
	}

	public function update($id){

		//recoger los datos por post
		$json = request()->input('json',null);
		$params_array = json_decode($json,true);
		if(!empty($params_array)){
			//validar los datos
			$validate = Validator::make($params_array,[
				'title'=>'required',
				'content'=>'required',
				'category_id'=>'required'
			]);
			if($validate->fails()){
				$data = [
					'code'=>400,
					'status'=>'error',
					'errors'=>$validate->errors()
				];
			}else{
				//eliminar lo que no queremos actualizar
				unset($params_array['id']);
				unset($params_array['user_id']);
				unset($params_array['created_at']);
				unset($params_array['user']);

				//conseguir usuario identificado para solo actualizar el post 
				//que me pertenece
				$user = $this->getIdentity();


				//buscar el registro a actualizar
				$post = Post::where('id',$id)
				->where('user_id',$user->sub)
				->first();
				if($post){
					//actualizar el registro en concreto
					$post->update($params_array);

					$data = [
						'code'=>200,
						'status'=>'success',
						'post'=>$post,
						'changes'=>$params_array
					];
				}else{
					$data = [
						'code'=>404,
						'status'=>'error',
						'message'=>'El post no existe.'
					];
				}
			}
		}else{
			$data = [
				'code'=>400,
				'status'=>'error',
				'message'=>'Datos enviados incorrectamente'
			];
		}
		//devolver respuesta
		return response()->json($data,$data['code']);
	}

	public function destroy($id){
		//conseguir usuario identificado
		$user = $this->getIdentity();

		//conseguir el post
		$post = Post::where('id',$id)
		->where('user_id',$user->sub)
		->first();
		if($post){
			//eliminar
			$post->delete();
			//devolver respuesta
			$data = [
				'code'=>200,
				'status'=>'success',
				'post'=>$post
			];
		}else{
			$data = [
				'code'=>404,
				'status'=>'error',
				'message'=>'El post no existe.'
			];
		}
		return response()->json($data,$data['code']);
	}

	private function getIdentity(){
		$jwtAuth = new JwtAuth();
		$token = request()->header('Authorization',null);
		$user = $jwtAuth->checkToken($token,true);
		return $user;
	}

	public function upload(){
		//recoger el archivo subido
		$image = request()->file('file0');
		//validar la imagen
		$validate = Validator::make(request()->all(),[
			'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
		]);
		if(!$image || $validate->fails()){
			$data = [
				'code'=>400,
				'status'=>'error',
				'message'=>'Error al subir la imagen'
			];
		}else{
			//guardar imagen en disco
			$image_name = time().$image->getClientOriginalName();
			\Storage::disk('images')->put($image_name,\File::get($image));
			$data = [
				'code'=>200,
				'status'=>'success',
				'image'=>$image_name
			];
		}

		//devolver respuesta
		return response()->json($data,$data['code']);
	}

	public function getImage($filename){
		//comprobar si existe el fichero
		$isset = \Storage::disk('images')->exists($filename);
		if($isset){
			//conseguir la imagen
			$file = \Storage::disk('images')->get($filename);
			//devolver la imagen
			return response($file,200);
		}else{
			//mostrar error
			$data = [
				'code'=>404,
				'status'=>'error',
				'message'=>'La imagen no existe'
			];
		}
		return response()->json($data,$data['code']);
	}

	public function getPostsByCategory($id){
		$posts = Post::where('category_id',$id)->get();
		return response()->json([
			'code'=>200,
			'status'=>'success',
			'posts'=>$posts
		],200);
	}

	public function getPostsByUser($id){
		$posts = Post::where('user_id',$id)->get();
		return response()->json([
			'code'=>200,
			'status'=>'success',
			'posts'=>$posts
		],200);
	}
}
