<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'surname' => $this->surname,
            'firstname' => $this->firstname,
            'othername' => $this->othername,
            'email' => $this->email,
            'phone' => $this->phone,
            'title' => $this->title,
            'role' => $this->role,
            'account_status' => $this->account_status,
        ];
    }
}
