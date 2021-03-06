<?php
/**
 * Created by PhpStorm.
 * User: yann
 * Date: 05/02/18
 * Time: 11:44
 */
namespace App\Models;
use \Illuminate\Database\Eloquent\Model;
class Serie extends Model
{
    protected $table = 'serie';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $hidden = ["city_id"];

    public function parties(){
        return $this->hasMany(Partie::class, 'serie_id');
    }

    public function photos(){
        return $this->hasMany(Photo::class, 'serie_id');
    }

    public function city(){
        return $this->belongsTo(City::class, 'city_id');
    }
    public function paliers(){
        return $this->hasMany(Palier::class,'serie_id');
    }
       public function times(){
        return $this->hasMany(Time::class,'serie_id');
    }
}