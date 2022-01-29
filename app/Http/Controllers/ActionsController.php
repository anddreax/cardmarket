<?php

namespace App\Http\Controllers;

use App\Models\assigned_cards;
use App\Models\User;
use App\Models\cards;
use App\Models\collections;
use App\Models\sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ActionsController extends Controller
{
    public function newUser(Request $request){
        //Array Asociativo que genera la respuesta
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent(); //recogemos datos
        $data = json_decode($data); //descodificamos los datos
        $user = new User();
        $user->name  = $data->name;
        $user->email  = $data->email;
        $user->password  = Hash::make($data->password);

        try{
            if(!User::where('name', $data->name)->first()){
                if(!User::where('email', $data->email)->first()){
                    if($data->role == 'personal' || $data->role == 'professional' || $data->role == 'admin'){
                        $user->role  = $data->role;
                        $user->save();
                        $response['msg'] = "User Save";
                    }else{
                        $response['msg'] = 'Role not found';
                    }
                }else{
                    $response['msg'] = 'The mail is already assigned to another account';
                }
            }else{
                $response['msg'] = 'The name is taken';
            }
        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }

        return response()->json($response);
    }

    public function newCard(Request $request){
        //Array Asociativo que genera la respuesta
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent(); //recogemos datos
        $data = json_decode($data); //descodificamos los datos
        $card = new cards();
        $card->name  = $data->name;
        $card->description  = $data->description;

        try{
            if(isset($data->id_collection) && collections::where('id', $data->id_collection)->first()){
                $card->save();
                $response['msg'] = "Card Save";
                $id = DB::table('cards')->select('id')->where('name', $data->name)->orderBy('id', 'desc')->first();
                $assigned = new assigned_cards();
                $assigned->id_card = $id->id;
                $assigned->id_collection = $data->id_collection;
                $assigned->save();
                $response['assigned'] = "Assigned Save";
            }else if (!collections::where('id', $data->id_collection)->first()){
                $response['msg'] = "This collection doens't exist, create the collection first";
            }
            else{
                $response['msg'] = "You need you assign a collection for this card";
            }
        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }

        return response()->json($response);
    }

    public function newCollection(Request $request){
        //Array Asociativo que genera la respuesta
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent(); //recogemos datos
        $data = json_decode($data); //descodificamos los datos
        $collection = new collections();
        $collection->name  = $data->name;
        $collection->edition_date  = date('Y-m-d');
        if(isset($collection->symbol)){
            $collection->symbol  = $data->symbol;
        }else{
            $collection->symbol  = 'default.png';
        }

        try{
            if(isset($data->id_card)){
                $search = cards::where('id', $data->id_card)->first();
                if($search){
                    // añadir a tabla intermedia
                    $collection->save();
                    $response['msg'] = "Card Save";
                }else{
                    $card = new cards();
                    $card->name  = "card".$data->id_card;
                    $card->description  = "Introduce la descripcion de la carta";
                    $collection->save();
                    $card->save();
                    $response['card'] = "Card Save";
                    $response['collection'] = "Collection Save";
                }
                $id = DB::table('collections')->select('id')->where('name', $data->name)->orderBy('id', 'desc')->first();
                $assigned = new assigned_cards();
                $assigned->id_collection = $id->id;
                $assigned->id_card = $data->id_card;
                $assigned->save();
            }else{
                $response['collection'] = "You need to assign a card to this collection";
            }

        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }

        return response()->json($response);
    }

    public function newSale(Request $request){
        //Array Asociativo que genera la respuesta
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent(); //recogemos datos
        $data = json_decode($data); //descodificamos los datos
        $sale = new sales();
        $sale->id_card  = $data->id_card;
        $sale->id_user  = $request->user->id;
        $sale->amount  = $data->amount;
        $sale->total_price  = $data->total_price;

        try{
            $sale->save();
            $response['msg'] = "Sale Save";
        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }

        return response()->json($response);
    }

    public function login(Request $request){
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent();
        $data = json_decode($data);


        try{
            $user = User::where('email', $data->email)->first();

            if(isset($data->email) && isset($data->password)){
                if($user){
                    if(Hash::check($data->password, $user->password)){
                        $apitoken =  Hash::make(now().$user->id);
                        $user->api_token = $apitoken;
                        $user->save();
                        $response['msg'] = "Your token is: " . $apitoken;

                    }else{
                        $response['msg']='Password is not correct';
                    }
                }else{
                    $response['msg']='User is not correct';
                }
            }else{
                $response['msg']='Data missing';
            }

        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }
        return response()->json($response);
    }

    public function recoverypass(Request $request){
        $response = ['status'=>1, 'msg'=>''];
        $data = $request->getContent();
        $data = json_decode($data);
        try{
            if($data->email){
                $email = $data->email;
                $user = User::where('email', $email)->first();
                if($user){
                    $password = Str::random(8);
                    $user->password = Hash::make($password);
                    $user->save();
                    $response ['msg'] = $password;
                }else{
                    $response ['msg'] = 'User not found';
                }
            }else{
                $response ['msg'] = 'Enter an email';
            }
        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }
        return response()->json($response);
    }

    public function listSales(Request $request){
        $response = ['status'=>1, 'msg'=>''];
        $logedUser = $request->user;
        try{
            if ($logedUser){
                $sales = DB::table('cards')->join('sales','sales.id_card', '=','cards.id')
                                           ->join('users','sales.id_user', '=','users.id')
                                           ->select('cards.id', 'cards.name', 'sales.amount', 'sales.total_price', 'users.name as user')
                                           ->get();
                $response['msg'] = $sales;
            }else{
                $response['msg'] = 'No te has logeado';
            }
        }catch(\Exception $e){
            $response['msg'] = $e->getMessage();
            $response['satus'] = 0;
        }
        return response()->json($response);
    }

    public function listCards(Request $request){
        $answer = ['status'=>1, 'msg'=>''];
        $cardname = $request->input('name', '0');
        try{

            $cards = cards::select('id', 'name');
            if($cardname!= 0){
                $cards = $cards->where('cards.name', 'like','%'.$cardname.'%');

            }

            $answer = $cards->get();
        }catch(\Exception $e){
            $answer['status'] = 0;
            $answer['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($answer);
    }

    public function asignCards(Request $request){
        $answer = ['status'=>1, 'msg'=>''];
        $data = $request->getContent();
        $data = json_decode($data);
        $assigned = new assigned_cards();
        $assigned->id_collection = $data->id_collection;
        $assigned->id_card = $data->id_card;
        try{
            $assigned->save();
            $answer['msg'] = "Asignation save";
        }catch(\Exception $e){
            $answer['status'] = 0;
            $answer['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($answer);
    }

}
