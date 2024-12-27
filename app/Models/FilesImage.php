<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FilesImage extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'file.image';

    protected $fillable = ['id', 'name', 'path', 'mime_type', 'size', 'uploaded_by'];
}
