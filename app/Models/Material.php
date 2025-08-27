<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'name',
    'material_class_id',
    'material_division_id',
    'material_type_id',
    'unique_id',
    'default_unit',
    'description',
    'measurement_unit',
    'height',
    'width',
    'length',
    'prices',
    'waste',
    'production_rate',
    'subbed_out_rate',
    'production_subed_out_cost',
    'cleaning_cost',
    'cleaning_subed_out',
    'associated_products',
    'project_id',
    'unit_measure_value',
    'weight_lf',
    'sq_ft_per_cy',
    'shortTon_w_l_f',

  ];

  public function material_class()
  {
    return  $this->belongsTo(MaterialClass::class);
  }
  public function material_division()
  {
    return $this->belongsTo(MaterialDivision::class);
  }
  public function material_type()
  {
    return $this->belongsTo(MaterialType::class);
  }
  public function user()
  {
    return $this->belongsTo(User::class);
  }
  public function getUnitMeasureValueAttribute()
  {

    $length = 0;
    switch ($this->material_type_id) {

      case 1: //area

        $length = ($this->length * $this->height) / 144;
        break;
      case 2: //lenght

        $length = $this->length / 12;

        break;
      case 3: //quantity
        $length = 1;
        break;
      default:
        $length = 0;
    }
    return $length;
  }

 
  public function getShorttonWlfAttribute()
  {
    $total = 0;
    $short_ton = 2000;
    if ($this->weight_lf > 0) {
      $total=$short_ton/$this->weight_lf;
    }

    return $total;
  }
}
