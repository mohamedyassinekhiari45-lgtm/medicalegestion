<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $description, $entityType = null, $entityId = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
        ]);
    }
}
