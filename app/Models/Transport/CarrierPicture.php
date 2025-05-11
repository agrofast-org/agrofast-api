<?php

namespace App\Models\Transport;

use App\Models\FileAttachment;
use Carbon\Carbon;

/**
 * Class File.
 *
 * @property int    $id
 * @property int    $machinery_id
 * @property int    $file_id
 * @property bool   $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $inactivated_at
 */
class CarrierPicture extends FileAttachment
{
    protected $table = 'transport.carrier_picture';

    protected $fillable = [
        'machinery_id',
        'file_id',
        'active',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];
}
