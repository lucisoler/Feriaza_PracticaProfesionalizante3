<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puesto extends Model
{
   protected $filliable =['numero','estado','feriante-id'];

   public function feriante(): BelongsTo{
    return $this->belongsTo(Feriante::class);

}
}
