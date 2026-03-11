<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'article' => $this->article,
            'barcode' => $this->barcode,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'unit' => $this->unit,
            'purchase_price' => $this->purchase_price,
            'photo_url' => $this->photo_url,
            'status' => $this->status,
            'type' => $this->type,
            'track_serials' => $this->track_serials,
            'length_cm' => $this->length_cm,
            'width_cm' => $this->width_cm,
            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,
            'min_stock' => $this->min_stock,
            'max_stock' => $this->max_stock,
            'description' => $this->description,
            'characteristics' => CharacteristicValueResource::collection($this->whenLoaded('characteristicValues')),
            'device_models' => DeviceModelResource::collection($this->whenLoaded('deviceModels')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
