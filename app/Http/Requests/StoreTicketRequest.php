<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|min:10|max:20|regex:/^[0-9+\-\s()]+$/',
            'email' => 'nullable|email|max:255',
            'category' => 'required|string|max:100|in:Layanan Publik,Infrastruktur,Keamanan,Lingkungan,Kesehatan,Pendidikan,Transportasi,Lainnya',
            'message' => 'required|string|min:10|max:1000',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120' // 5MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 20 digit.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'category.required' => 'Kategori harus dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'message.required' => 'Pesan pengaduan harus diisi.',
            'message.min' => 'Pesan pengaduan minimal 10 karakter.',
            'message.max' => 'Pesan pengaduan maksimal 1000 karakter.',
            'evidence_file.mimes' => 'File bukti harus berformat: jpg, jpeg, png, pdf, doc, docx.',
            'evidence_file.max' => 'Ukuran file bukti maksimal 5MB.',
        ];
    }
}