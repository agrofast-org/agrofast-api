<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilesImage extends Model
{
    protected $fillable = ['id', 'name', 'path', 'mime_type', 'size', 'uploaded_by'];
}
