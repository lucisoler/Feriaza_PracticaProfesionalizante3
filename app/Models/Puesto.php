<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puesto extends Model
{
   protected $fillable =['numero','estado','feriante_id','habilitado'];

   public function feriante(): BelongsTo{
    return $this->belongsTo(Feriante::class);

}
}
