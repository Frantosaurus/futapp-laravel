<?php

namespace App\Http\Controllers;

use App\Models\Restaurace;
use App\Models\TypyRestauraci;
use App\Models\User;
use App\Models\Pozadavek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    public function hlavni_stranka()
    {
        return view('hlavni_stranka');
    }

    public function zakladni_udaje()
    {
        $nazevRestaurace = Restaurace::orderBy("typ_id")->get();
        $typJidla = TypyRestauraci::all();
    
        return view('zakladni_udaje', ['typJidla' => $typJidla, 'nazevRestaurace' => $nazevRestaurace]);
    }

    public function udaje(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:25',
            'last_name' => 'required|max:25',
            'contact' => 'required|max:25',
        ]);
    
        $uzivatel = new User();
    
        $uzivatel->name = $validatedData["name"];
        $uzivatel->last_name = $validatedData["last_name"];
        $uzivatel->contact = $validatedData["contact"];

        $uzivatel->save();
    
        return redirect("/")->with('mssg', 'Potvrzeno');
    }


    public function cas()
    {
        return view('cas');
    }

    public function vyber_jidla_z_databaze()
    {
        // Načtěte všechny typy restaurací z databáze
        $typyRestauraci = TypyRestauraci::all();
    
        // Předejte data do šablony
        return view('vyber_jidla_z_databaze', ['typyRestauraci' => $typyRestauraci]);
    }

    public function konkretni_restaurace($typId)
{
    // Získání konkrétních restaurací pro daný typ z databáze
    $konkretniRestaurace = DB::table('konkretni_restaurace')
        ->where('typ_id', $typId)
        ->get();

    // Předání dat na view
    return view('konkretni_restaurace', compact('konkretniRestaurace'));
}

public function detailRestaurace($id)
{
    $konkretniRestaurace = Restaurace::find($id);

    return view('detail_restaurace', compact('konkretniRestaurace'));
}


    public function vyber_jidla()
    {
        $restaurace = Restaurace::all();
        return view('vyber_jidla', ['restaurace' => $restaurace]);
    }
    
    // jednotlivé restaurace
    public function showCusine($cuisine, $type)
    {
        $viewName = $cuisine . '.' . $type;

        if (view()->exists($viewName)) {
            return view($viewName);
        } else {
            abort(404); //kdyby za lomítkem nic neexistovalo, hodí to namísto laravel chyby error 404
        }
    }

    public function showType($type)
    {
        return view($type);
    }


    //Obě funkce slouží pro ukládání dat do cache paměti. Jedna ukládá pouze do cache, druhá ukládá do cache a také do databáze.
    public function zakladni_udajeToCache(Request $request)
    {
        // Uložení dat do cache
        $userData = Cache::get('user_data', []);
        $newData = $request->only(['name', 'last_name','contact']);
        $userData = array_merge($userData, $newData);
        Cache::put('user_data', $userData, 600);

        // Přesměrování na další stránku
        return redirect()->route('cas');
    }

    public function casToCache(Request $request)
    {
        // Uložení dat do cache
        $userData = Cache::get('user_data', []);
        $newData = $request->only(['den', 'od_kdy_hours', 'do_kdy_hours']); // Oprava názvů sloupců
        $userData = array_merge($userData, $newData);
        Cache::put('user_data', $userData, 600);
    
        // Přesměrování na další stránku
        return redirect()->route('vyber_jidla_z_databaze');
    }




    public function celamezipametToCacheAndSaveToDatabase(Request $request)
{
    // Uložení dat do cache
    $userData = Cache::get('user_data', []);
    $newData = $request->only(['restaurant_type', 'restaurant_name']);
    $userData = array_merge($userData, $newData);
    Cache::put('user_data', $userData, 600);

    // Uložení dat do databáze
    $pozadavek = new Pozadavek();
    $pozadavek->den = $userData['den'] ?? null;
    $pozadavek->od_kdy = $userData['od_kdy_hours'] ?? null;
    $pozadavek->do_kdy = $userData['do_kdy_hours'] ?? null;
    $pozadavek->typ_restaurace_id = $userData['restaurant_type'];
    $pozadavek->konkretni_restaurace_id = $userData['restaurant_name'];
    $pozadavek->save();

    $uzivatel = new User();
    $uzivatel->name = $userData['name'] ?? null;
    $uzivatel->last_name = $userData['last_name'] ?? null;
    $uzivatel->contact = $userData['contact'] ?? null;
    $uzivatel->pozadavek_id = $pozadavek->id;
    $uzivatel->save();
    // Přesměrování na další stránku
    return redirect()->route('match');
}


public function match()
{
    $latestUser = Pozadavek::latest()->first();

    // Přidání časových podmínek pro překrývající se intervaly
    $peopleWithSameRestaurant = Pozadavek::where('konkretni_restaurace_id', $latestUser->konkretni_restaurace_id)
    ->where('den', $latestUser->den)
    ->where(function($query) use ($latestUser) {
        $query->where(function($q) use ($latestUser) {
            $q->where('od_kdy', '>=', $latestUser->od_kdy)
              ->where('od_kdy', '<=', $latestUser->do_kdy);
        })
        ->orWhere(function($q) use ($latestUser) {
            $q->where('do_kdy', '>=', $latestUser->od_kdy)
              ->where('do_kdy', '<=', $latestUser->do_kdy);
        })
        ->orWhere(function($q) use ($latestUser) {
            $q->where('od_kdy', '<=', $latestUser->od_kdy)
              ->where('do_kdy', '>=', $latestUser->do_kdy);
        });
        })
        ->where('id', '!=', $latestUser->id)
        ->get();

    $peopleWithSameTypeDifferentLocation = Pozadavek::where('typ_restaurace_id', $latestUser->typ_restaurace_id)
        ->where('den', $latestUser->den)
        ->where(function($query) use ($latestUser) {
            $query->where(function($q) use ($latestUser) {
                $q->where('od_kdy', '>=', $latestUser->od_kdy)
                  ->where('od_kdy', '<=', $latestUser->do_kdy);
            })
            ->orWhere(function($q) use ($latestUser) {
                $q->where('do_kdy', '>=', $latestUser->od_kdy)
                  ->where('do_kdy', '<=', $latestUser->do_kdy);
            })
            ->orWhere(function($q) use ($latestUser) {
                $q->where('od_kdy', '<=', $latestUser->od_kdy)
                  ->where('do_kdy', '>=', $latestUser->do_kdy);
            });
        })
        ->where('konkretni_restaurace_id', '!=', $latestUser->konkretni_restaurace_id)
        ->get();

    return view('match', [
        'latestUser' => $latestUser,
        'peopleWithSameRestaurant' => $peopleWithSameRestaurant,
        'peopleWithSameTypeDifferentLocation' => $peopleWithSameTypeDifferentLocation,
    ]);
}
}