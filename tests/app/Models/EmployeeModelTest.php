<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class EmployeeModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true; // Membersihkan DB testing setiap kali tes jalan
    protected $namespace = 'App'; // Lokasi file migration jika ada

    public function testCekKoneksiUserModel()
    {
        // Memanggil UserModel sesuai kodingan kamu
        $model = new \App\Models\UserModel();
        
        // Memastikan model berhasil di-instansiasi
        $this->assertInstanceOf(\App\Models\UserModel::class, $model);
    }

    public function testCekTableYangDigunakan()
    {
        $model = new \App\Models\UserModel();
        
        // Memastikan tabel yang dipakai benar-benar 'user' sesuai file UserModel.php
        $this->assertEquals('user', $model->table);
    }

    public function testSimpanUserBaru()
    {
        $model = new \App\Models\UserModel();

        // Data palsu untuk ngetes
        $data = [
            'username'     => 'testqa',
            'email'        => 'test@bitespace.com',
            'password'     => password_hash('123456', PASSWORD_DEFAULT),
            'role'         => 'kasir',
            'nama_lengkap' => 'QA Tester Bitespace'
        ];

        // 1. Coba simpan
        $model->insert($data);

        // 2. Cek apakah datanya beneran masuk ke database bitespace_qa
        $this->seeInDatabase('user', [
            'username' => 'testqa'
        ]);
    }
}