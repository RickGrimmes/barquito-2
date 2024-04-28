<?php

namespace App\Http\Controllers\api;

use App\Events\GameCanelEvent;
use App\Events\GamesEvent;
use App\Events\StartGameEvent;
use App\Events\WinEvent;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\Game as GameEvent;
use App\Events\HistoryEvent;
use App\Events\TurnEvent;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function index(int $id){
        if(Auth::user()){
            $game = Game::find($id); 
            if($game != null){
               return response()->json([
                    "msg" => "Datos encontrados!!!",
                    'result' => true,
                    'data' => $game
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'msg' => 'No existe el juego.',
                ], 404);
            }
        }else {
            return response()->json([
                'result' => false,
                'msg' => 'Jugador no autenticado.',
            ], 401);
        }
            
        
    }
    public function games(){
        if(Auth::user()){
            $games = Game::where('start_at', null)->where('user_1', '!=', Auth::user()->id)->get();
            if(count($games) > 0){
                $games->transform(function ($game) {
                    $user1 = User::find($game->user_1);
                    $user2 = User::find($game->user_2);
                    $game->user_1 = $user1 ? $user1->name : 'Usuario no encontrado';
                    $game->user_2 = $user2 ? $user2->name : 'Usuario no encontrado';
                    return $game ;
                });
                return response()->json([
                    "msg" => "Partias encontradas!!!",
                    'result' => true,
                    'data' => $games
                ], 201);
            } else {
                return response()->json([
                    'result' => false,
                    'msg' => 'No existen juegos.',
                ], 404);
            }
        }else {
            return response()->json([
                'result' => false,
                'msg' => 'Jugador no autenticado.',
            ], 401);
        }
            
        
    }
    public function store()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $juego = new Game();
            $juego->user_1 = $user->id;
            $juego->save();
            
            $user1 = User::find($juego->user_1);
            $gameData = new \stdClass();
            $gameData = $juego;
            $gameData->name = $user1->name;
            
            if($juego->start_at == null){
                event(new GamesEvent($gameData));
            }
            
            return response()->json([
                'mesg' => "Juego creado!!!",
                'result' => true,
                'data' => $gameData
            ], 201);
        } else {
            return response()->json([
                'result' => false,
                'msg' => 'Usuario no autenticado.',
            ], 401);
        }
    } 
    public function start(int $id)
    {
        if(Auth::user()){
            $juego = Game::find($id);
            if($juego){
                $juego->user_2 = Auth::user()->id;
                $juego->turn = Auth::user()->id;
                $juego->is_active = true;
                $juego->start_at = now();
                $juego->save(); 
                event(new HistoryEvent($juego));
                event(new StartGameEvent($juego->id));
                return response()->json([
                    "msg" => "Partida iniciada!!!",
                    'result' => true
                ], 201);
            }
            return response()->json([
                "msg" => "Partida no encontrada!!!",
                'result' => false
            ], 404);
        }
        
    } 
    public function cancel(int $id)
    {
        $juego = Game::find($id);
        if($juego){
            $juego->start_at = now();
            $juego->is_active = 0;
            $juego->save();
            event(new GameCanelEvent($juego));
            event(new HistoryEvent($juego));

            return response()->json([
                "msg" => "Partida finalizada!!!",
                'result' => true
            ], 201);
        }
        return response()->json([
            "msg" => "Partida no encontrada!!!",
            'result' => false
        ], 404);
    }  
    public function win(Request $request, int $id)
    {
        $juego = Game::find($id);
        if($juego){
            $juego->is_active = 0;
            $juego->won = $request->win;
            $juego->save();
            event(new WinEvent($juego));
            event(new HistoryEvent($juego));
            return response()->json([
                "msg" => "Partida finalizada!!!",
                'result' => true
            ], 201);
        }else{
            return response()->json([
                "msg" => "Partida no encontrada.",
                'result' => false
            ], 404);
        }
    }    
    public function cancelAll()
    {
        if(Auth::user()){
            $juegos = Game::where('start_at', null)->where('user_1', Auth::user()->id)->get();
            if(count($juegos) > 0){
                foreach($juegos as $juego){
                    $juego->start_at = now();
                    $juego->is_active = 0;
                    $juego->save();
                }
                return response()->json([
                    "msg" => "Partidas finalizadas!!!",
                    'result' => true
                ], 201);
            }
            return response()->json([
                "msg" => "No cuentas con partidas pendientes!!!",
                'result' => true
            ], 202);
        }
        return response()->json([
            "msg" => "Usuario no autorizado!!!",
            'result' => false
        ], 401);
    }
    public function turn(int $id)
    {
        if (Auth::check()) {
            $juego = Game::find($id);
            if($juego){
                if($juego->turn == Auth::user()->id){
                    if($juego->user_2 == $juego->turn){
                        $juego->turn = $juego->user_1;
                    }else{
                        $juego->turn = $juego->user_2   ;
                    }
                $juego->save();
                event(new TurnEvent($juego));
                }
                return response()->json([
                    "msg" => "Movimiento hecho!!!",
                    'result' => true
                ], 201);
            }
            return response()->json([
                "msg" => "Partida no encontrada!!!",
                'result' => false
            ], 404);
        }
        return response()->json([
            "msg" => "Usuario no auntenticado.",
            'result' => false
        ], 401);
    }
    public function board(int $id)
    {
        if (Auth::check()) {
            $juego = Game::find($id);
            if($juego){
                if($juego->turn == Auth::user()->id){
                    if($juego->user_2 == $juego->turn){
                        $juego->boad1 = $juego->boad1 - 1;
                    }else{
                        $juego->boad2 = $juego->boad2 - 1;
                    }
                $juego->save();
                }
                return response()->json([
                    "msg" => "Bien hecho!!!",
                    'result' => true,
                    'data' => $juego
                ], 201);
            }
            return response()->json([
                "msg" => "Partida no encontrada!!!",
                'result' => false
            ], 404);
        }
        return response()->json([
            "msg" => "Usuario no auntenticado.",
            'result' => false
        ], 401);
    }
}