<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function initializeBelongsToUser()
  {
    if (! in_array('user_id', $this->fillable)) {
      $this->fillable[] = 'user_id';
    }
  }
}
