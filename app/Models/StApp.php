<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Redis as Redis;

class StApp extends Model
{

    protected $table = 'st_app';

    public static function getCache( $id )
    {

        if ( !ebsig_is_int( $id ) ) {
            return null;
        }

        $st_app = Redis::get('GLOBAL_APP_' . $id );

        if ( !$st_app ) {

            $st_app = self::find($id);
            if ( !$st_app ) {
                return null;
            }

            Redis::setex( 'GLOBAL_APP_' . $id , 604800 , json_encode(['id'=>$st_app->id,'name'=>$st_app->name,'alias'=>$st_app->alias]) );

        } else {

            $st_app = json_decode( $st_app , true );
        }

        return $st_app;

    }

}
