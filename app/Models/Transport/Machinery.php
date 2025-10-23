<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Machinery.
 *
 * Represents a machinery item with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property string      $name
 * @property string      $model
 * @property string      $plate
 * @property string      $type
 * @property string      $manufacturer
 * @property null|Carbon $manufacturer_date
 * @property float       $weight
 * @property float       $length
 * @property float       $width
 * @property float       $height
 * @property int         $axles
 * @property string      $tire_config
 * @property null|string $obs
 * @property bool        $active
 * @property null|Carbon $inactivated_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * Relationships:
 * @property User $user
 */
class Machinery extends Model
{
    use HasFactory;

    protected $table = 'transport.machinery';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'model',
        'type',
        'plate',
        'manufacturer',
        'manufacturer_date',
        'weight',
        'length',
        'width',
        'height',
        'axles',
        'tire_config',
        'obs',
        'active',
        'inactivated_at',
    ];

    protected $attributes = [
        'active' => true,
    ];

    protected $casts = [
        'manufacturer_date' => 'date',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
        'axles' => 'integer',
        'inactivated_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    /**
     * Relacionamento com o Usuário (proprietário/criador).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com as fotos do maquinário.
     */
    public function pictures()
    {
        return $this->hasMany(MachineryPicture::class, 'machinery_id');
    }

    public function addPicture(string $id)
    {
        MachineryPicture::create([
            'machinery_id' => $this->id,
            'file_id' => $id,
        ]);
    }

    public function request()
    {   
        return $this->hasOne(Request::class, 'machine_id');
    }
}
