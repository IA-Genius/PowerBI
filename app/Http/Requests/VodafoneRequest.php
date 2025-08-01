<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VodafoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trazabilidad' => ['nullable', 'in:pendiente,asignado,irrelevante,completado,retornado'],
            'orden_trabajo_anterior' => 'nullable|string|max:255',
            'origen_base' => 'nullable|string|max:255',
            'nombre_cliente' => 'required|string|max:255',
            'dni_cliente' => ['required', 'string', 'max:255', 'unique:historial_registros_vodafone,dni_cliente,' . $this->input('id')],
            'telefono_principal' => 'required|string|max:20',
            'telefono_adicional' => 'nullable|string|max:20',
            'correo_referencia' => 'nullable|email|max:255',
            'direccion_historico' => 'nullable|string|max:255',
            'marca_base' => 'nullable|string|max:255',
            'origen_motivo_cancelacion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'asignado_a_id' => 'nullable|exists:users,id',
        ];
    }
}
