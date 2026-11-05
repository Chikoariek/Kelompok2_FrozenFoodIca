<?php

return [
    'accepted' => ':Attribute harus diterima.',
    'accepted_if' => ':Attribute harus diterima ketika :other berisi :value.',
    'active_url' => ':Attribute bukan URL yang valid.',
    'after' => ':Attribute harus berisi tanggal setelah :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'array' => ':Attribute harus berupa sebuah array.',
    'before' => ':Attribute harus berisi tanggal sebelum :date.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi saat ini tidak cocok atau salah.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'filled' => 'Kolom :attribute harus memiliki nilai.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':Attribute tidak boleh lebih besar dari :max.',
        'file' => ':Attribute maksimal berukuran :max kilobita.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
        'array' => ':Attribute tidak boleh lebih dari :max item.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal bernilai :min.',
        'file' => ':Attribute minimal berukuran :min kilobita.',
        'string' => ':Attribute minimal berisi :min karakter.',
        'array' => ':Attribute minimal memiliki :min item.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':Attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':Attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':Attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':Attribute yang diberikan telah terkompromi dalam kebocoran data. Silakan pilih :attribute yang lain.',
    ],
    'required' => 'Kolom :attribute wajib diisi.',
    'same' => ':Attribute dan :other harus sama.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',

    'attributes' => [
        'current_password' => 'kata sandi saat ini',
        'password' => 'kata sandi baru',
        'password_confirmation' => 'konfirmasi kata sandi baru',
        'name' => 'nama lengkap',
        'email' => 'alamat email',
        'phone' => 'nomor telepon',
        'address' => 'alamat',
    ],
];

